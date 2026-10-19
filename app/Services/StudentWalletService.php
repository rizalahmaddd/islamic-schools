<?php

namespace App\Services;

use App\Models\CanteenOrder;
use App\Models\CanteenOrderItem;
use App\Models\RfidCard;
use App\Models\Student;
use App\Models\StudentWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentWalletService
{
    public function __construct(
        protected DocumentNumberGenerator $docGenerator,
    ) {}

    /**
     * Dapatkan atau inisialisasi dompet digital santri.
     */
    public function getOrCreateWallet(Student $student): StudentWallet
    {
        /** @var StudentWallet $wallet */
        $wallet = StudentWallet::query()->firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0, 'daily_limit' => 50000, 'is_active' => true]
        );

        return $wallet;
    }

    /**
     * Top-up saldo dompet santri (bebas race-condition berkat DB row lock).
     *
     * @throws ValidationException
     */
    public function topUp(
        StudentWallet $wallet,
        float $amount,
        ?string $notes = null,
        ?int $cashierUserId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pengisian saldo harus lebih dari Rp 0.',
            ]);
        }

        return DB::transaction(function () use ($wallet, $amount, $notes, $cashierUserId) {
            /** @var StudentWallet $lockedWallet */
            $lockedWallet = StudentWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $lockedWallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $lockedWallet->update(['balance' => $balanceAfter]);

            $ref = $this->docGenerator->next(prefix: 'TOP', pad: 5);

            return WalletTransaction::create([
                'reference_number' => $ref,
                'student_wallet_id' => $lockedWallet->id,
                'rfid_card_id' => null,
                'transaction_type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'canteen_merchant_id' => null,
                'description' => $notes ?? 'Isi ulang saldo tunai',
                'cashier_user_id' => $cashierUserId,
            ]);
        });
    }

    /**
     * Memproses transaksi belanja kantin non-tunai melalui tap kartu RFID.
     *
     * @param  list<array{product_id?: ?int, name: string, quantity: int, price: float, subtotal: float}>  $items
     *
     * @throws ValidationException
     */
    public function processCanteenPurchase(
        string $cardUid,
        float $totalAmount,
        array $items = [],
        ?int $merchantId = null,
        ?int $cashierUserId = null
    ): WalletTransaction {
        $cardUid = trim($cardUid);

        if ($totalAmount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Total belanja harus lebih dari Rp 0.',
            ]);
        }

        $card = RfidCard::with('student')->where('card_uid', $cardUid)->first();

        if (! $card) {
            throw ValidationException::withMessages([
                'card_uid' => "Kartu RFID [{$cardUid}] tidak terdaftar dalam sistem.",
            ]);
        }

        if ($card->isBlocked()) {
            $reason = $card->blocked_reason ?: 'Kartu dinonaktifkan';
            throw ValidationException::withMessages([
                'card_uid' => "Kartu RFID santri ini sedang diblokir ({$reason}).",
            ]);
        }

        /** @var Student $student */
        $student = $card->student;
        $wallet = $this->getOrCreateWallet($student);

        if (! $wallet->is_active) {
            throw ValidationException::withMessages([
                'card_uid' => 'Dompet digital santri sedang dinonaktifkan.',
            ]);
        }

        return DB::transaction(function () use ($wallet, $card, $totalAmount, $items, $merchantId, $cashierUserId, $student) {
            /** @var StudentWallet $lockedWallet */
            $lockedWallet = StudentWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $lockedWallet->balance;

            // 1. Cek kecukupan saldo
            if ($balanceBefore < $totalAmount) {
                $deficit = $totalAmount - $balanceBefore;
                throw ValidationException::withMessages([
                    'card_uid' => 'Saldo kartu santri tidak mencukupi. (Sisa Saldo: Rp '.number_format($balanceBefore, 0, ',', '.').', Kurang: Rp '.number_format($deficit, 0, ',', '.').')',
                ]);
            }

            // 2. Cek batas limit jajan harian (Parental Daily Limit)
            $spentToday = $lockedWallet->spentToday();
            $dailyLimit = (float) $lockedWallet->daily_limit;

            if (($spentToday + $totalAmount) > $dailyLimit) {
                $allowed = max(0, $dailyLimit - $spentToday);
                throw ValidationException::withMessages([
                    'card_uid' => 'Transaksi ditolak: Melampaui batas limit jajan harian orang tua! (Limit: Rp '.number_format($dailyLimit, 0, ',', '.').', Sisa kuota jajan hari ini: Rp '.number_format($allowed, 0, ',', '.').')',
                ]);
            }

            $balanceAfter = $balanceBefore - $totalAmount;
            $lockedWallet->update(['balance' => $balanceAfter]);

            $ref = $this->docGenerator->next(prefix: 'BLJ', pad: 5);

            $transaction = WalletTransaction::create([
                'reference_number' => $ref,
                'student_wallet_id' => $lockedWallet->id,
                'rfid_card_id' => $card->id,
                'transaction_type' => 'purchase',
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'canteen_merchant_id' => $merchantId,
                'description' => 'Belanja di Kantin Sekolah',
                'cashier_user_id' => $cashierUserId,
            ]);

            // Catat order belanja rincian
            if (! empty($items)) {
                $orderNumber = $this->docGenerator->next(prefix: 'ORD-KTN', pad: 4);
                $order = CanteenOrder::create([
                    'order_number' => $orderNumber,
                    'wallet_transaction_id' => $transaction->id,
                    'canteen_merchant_id' => $merchantId,
                    'student_id' => $student->id,
                    'total_amount' => $totalAmount,
                ]);

                foreach ($items as $item) {
                    CanteenOrderItem::create([
                        'canteen_order_id' => $order->id,
                        'canteen_product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            }

            return $transaction;
        });
    }

    /**
     * Memperbarui batas limit belanja harian santri yang ditentukan wali murid / admin.
     */
    public function setDailyLimit(StudentWallet $wallet, float $limit): StudentWallet
    {
        $wallet->update([
            'daily_limit' => max(0, $limit),
        ]);

        return $wallet->fresh();
    }

    /**
     * Memblokir kartu RFID (misal jika kartu santri hilang/rusak).
     */
    public function blockCard(RfidCard $card, string $reason): RfidCard
    {
        $card->update([
            'is_active' => false,
            'blocked_at' => now(),
            'blocked_reason' => $reason,
        ]);

        return $card->fresh();
    }

    /**
     * Mengaktifkan kembali kartu RFID santri.
     */
    public function activateCard(RfidCard $card): RfidCard
    {
        $card->update([
            'is_active' => true,
            'blocked_at' => null,
            'blocked_reason' => null,
        ]);

        return $card->fresh();
    }

    /**
     * Mendaftarkan kartu RFID baru untuk santri.
     */
    public function registerCard(Student $student, string $cardUid, string $label = 'Kartu Utama'): RfidCard
    {
        return RfidCard::create([
            'card_uid' => trim($cardUid),
            'student_id' => $student->id,
            'label' => $label,
            'is_active' => true,
        ]);
    }
}
