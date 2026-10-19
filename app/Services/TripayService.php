<?php

namespace App\Services;

use App\Models\OnlinePaymentTransaction;
use App\Models\PaymentTransaction;
use App\Models\PaymentTransactionItem;
use App\Models\StudentBill;
use App\Models\User;
use App\Support\Branding;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TripayService
{
    protected ?string $apiKey;

    protected ?string $privateKey;

    protected ?string $merchantCode;

    protected string $apiUrl;

    protected string $mode;

    public function __construct(
        protected DocumentNumberGenerator $docGenerator,
        protected FonnteService $fonnteService
    ) {
        $this->apiKey = (string) config('services.tripay.api_key');
        $this->privateKey = (string) config('services.tripay.private_key');
        $this->merchantCode = (string) config('services.tripay.merchant_code');
        $this->mode = (string) config('services.tripay.mode', 'sandbox');
        $this->apiUrl = (string) config('services.tripay.api_url', 'https://tripay.co.id/api-sandbox/');
    }

    /**
     * Daftar saluran pembayaran yang didukung sekolah via TriPay.
     *
     * @return array<int, array{code: string, name: string, group: string, fee_flat: int, fee_percent: float, icon: string}>
     */
    public function getPaymentChannels(): array
    {
        return [
            [
                'code' => 'QRIS',
                'name' => 'QRIS (Gopay, OVO, Dana, ShopeePay, BCA, dll)',
                'group' => 'QRIS Realtime',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
                'icon' => 'qr-code',
            ],
            [
                'code' => 'BSIVA',
                'name' => 'BSI Virtual Account (Bank Syariah Indonesia)',
                'group' => 'Virtual Account',
                'fee_flat' => 3500,
                'fee_percent' => 0.0,
                'icon' => 'building-2',
            ],
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0.0,
                'icon' => 'credit-card',
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0.0,
                'icon' => 'credit-card',
            ],
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0.0,
                'icon' => 'credit-card',
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0.0,
                'icon' => 'credit-card',
            ],
            [
                'code' => 'ALFAMART',
                'name' => 'Gerai Alfamart / Alfamidi',
                'group' => 'Gerai Retail',
                'fee_flat' => 3500,
                'fee_percent' => 0.0,
                'icon' => 'store',
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Gerai Indomaret',
                'group' => 'Gerai Retail',
                'fee_flat' => 3500,
                'fee_percent' => 0.0,
                'icon' => 'store',
            ],
        ];
    }

    /**
     * Hitung perkiraan biaya admin gateway TriPay.
     */
    public function calculateFee(string $channelCode, float $amount): float
    {
        $channels = collect($this->getPaymentChannels())->keyBy('code');
        $channel = $channels->get($channelCode);

        if (! $channel) {
            return 4000;
        }

        $flat = $channel['fee_flat'];
        $percent = ($amount * ($channel['fee_percent'] / 100));

        return round($flat + $percent);
    }

    /**
     * Buat transaksi tertutup (Closed Transaction) ke TriPay.
     *
     * @throws ValidationException
     */
    public function createClosedTransaction(StudentBill $bill, string $paymentChannel, ?User $payer = null): OnlinePaymentTransaction
    {
        $remaining = (float) $bill->remaining_amount;

        if ($remaining <= 0 || $bill->isPaid()) {
            throw ValidationException::withMessages([
                'paymentChannel' => 'Tagihan ini sudah lunas.',
            ]);
        }

        // Cek apakah sudah ada transaksi pending yang masih berlaku untuk tagihan ini dengan channel yang sama
        $existing = OnlinePaymentTransaction::query()
            ->where('student_bill_id', $bill->id)
            ->where('payment_channel', $paymentChannel)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return $existing;
        }

        $channels = collect($this->getPaymentChannels())->keyBy('code');
        $channelMeta = $channels->get($paymentChannel);
        $channelName = $channelMeta['name'] ?? $paymentChannel;

        $amount = $remaining;
        $adminFee = $this->calculateFee($paymentChannel, $amount);
        $grossAmount = $amount + $adminFee;

        $orderId = $this->docGenerator->next('ORD');

        $bill->loadMissing(['student.guardian', 'feeCategory']);
        $student = $bill->student;
        $guardian = $student->guardian;

        $customerName = $student->name;
        $customerEmail = ! empty($guardian?->email) ? $guardian->email : ('santri.'.$student->id.'@sekolah.sch.id');
        $customerPhone = ! empty($guardian?->phone) ? $guardian->phone : '081234567890';

        $title = ($bill->feeCategory?->name ?? 'Biaya Pendidikan').($bill->month ? ' ('.$bill->month_name.' '.$bill->year.')' : '');

        $expiresAt = now()->addHours(24);

        // Jika testing atau API Key belum diisi, gunakan simulasi sandbox aman
        if (app()->environment('testing') || blank($this->apiKey) || blank($this->privateKey) || blank($this->merchantCode)) {
            $isQris = $paymentChannel === 'QRIS';
            $ref = 'DEV-T'.date('YmdHis').rand(100, 999);

            return OnlinePaymentTransaction::create([
                'order_id' => $orderId,
                'student_bill_id' => $bill->id,
                'student_id' => $student->id,
                'payer_user_id' => $payer?->id,
                'payment_channel' => $paymentChannel,
                'payment_channel_name' => $channelName,
                'amount' => $amount,
                'admin_fee' => $adminFee,
                'gross_amount' => $grossAmount,
                'provider' => 'tripay',
                'reference' => $ref,
                'checkout_url' => 'https://tripay.co.id/checkout/'.$ref,
                'qr_url' => $isQris ? 'https://tripay.co.id/qr/'.$ref : null,
                'qr_string' => $isQris ? '00020101021226590014ID.LINKAJA.WWW011893600011011122334402151234567890123455204581253033605802ID5914Sekolah Islam6007Jakarta6304' : null,
                'va_number' => ! $isQris ? ('98877'.rand(10000000, 99999999)) : null,
                'status' => 'pending',
                'paid_at' => null,
                'expires_at' => $expiresAt,
                'raw_payload' => ['simulation' => true],
            ]);
        }

        // Live / Real Sandbox API call ke TriPay
        $signature = hash_hmac('sha256', $this->merchantCode.$orderId.(int) $grossAmount, $this->privateKey);

        $payload = [
            'method' => $paymentChannel,
            'merchant_ref' => $orderId,
            'amount' => (int) $grossAmount,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => [
                [
                    'sku' => (string) $bill->id,
                    'name' => substr($title, 0, 50),
                    'price' => (int) $amount,
                    'quantity' => 1,
                ],
                [
                    'sku' => 'FEE',
                    'name' => 'Biaya Layanan Payment Gateway',
                    'price' => (int) $adminFee,
                    'quantity' => 1,
                ],
            ],
            'callback_url' => route('api.v1.payment.webhook'),
            'return_url' => route('portal.guardian'),
            'expired_time' => $expiresAt->timestamp,
            'signature' => $signature,
        ];

        try {
            $response = Http::timeout(20)
                ->withToken($this->apiKey)
                ->post(rtrim($this->apiUrl, '/').'/transaction/create', $payload);

            $result = $response->json();

            if (! $response->successful() || ! ($result['success'] ?? false)) {
                $err = $result['message'] ?? 'Gagal membuat tagihan online di TriPay.';
                Log::error('TriPay createClosedTransaction failed: '.$err, ['payload' => $payload, 'response' => $result]);
                throw ValidationException::withMessages(['paymentChannel' => $err]);
            }

            $data = $result['data'] ?? [];

            return OnlinePaymentTransaction::create([
                'order_id' => $orderId,
                'student_bill_id' => $bill->id,
                'student_id' => $student->id,
                'payer_user_id' => $payer?->id,
                'payment_channel' => $paymentChannel,
                'payment_channel_name' => $channelName,
                'amount' => $amount,
                'admin_fee' => $adminFee,
                'gross_amount' => $grossAmount,
                'provider' => 'tripay',
                'reference' => $data['reference'] ?? null,
                'checkout_url' => $data['checkout_url'] ?? null,
                'qr_url' => $data['qr_url'] ?? null,
                'qr_string' => $data['qr_string'] ?? null,
                'va_number' => $data['pay_code'] ?? null,
                'status' => 'pending',
                'paid_at' => null,
                'expires_at' => isset($data['expired_time']) ? Carbon::createFromTimestamp((int) $data['expired_time']) : $expiresAt,
                'raw_payload' => $data,
            ]);
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            Log::error('TriPay exception: '.$e->getMessage(), ['order_id' => $orderId]);
            throw ValidationException::withMessages([
                'paymentChannel' => 'Terjadi kendala saat menghubungi server pembayaran TriPay: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Verifikasi signature callback dari header X-Callback-Signature TriPay.
     */
    public function verifyCallbackSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (blank($signatureHeader)) {
            return false;
        }

        $privateKey = $this->privateKey ?: (app()->environment('testing') ? 'testing-private-key' : '');
        $expected = hash_hmac('sha256', $rawBody, $privateKey);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * Proses pelunasan pembayaran online dari webhook dengan proteksi anti double-credit.
     *
     * @param  array<string, mixed>  $payload
     * @return array{status: string, receipt_number?: string, message: string}
     */
    public function processSuccessfulPayment(OnlinePaymentTransaction $tx, array $payload = []): array
    {
        return DB::transaction(function () use ($tx, $payload) {
            /** @var OnlinePaymentTransaction $lockedTx */
            $lockedTx = OnlinePaymentTransaction::query()
                ->whereKey($tx->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTx->status === 'settlement') {
                return [
                    'status' => 'already_settled',
                    'receipt_number' => $lockedTx->paymentTransaction?->receipt_number,
                    'message' => 'Transaksi ini sudah diproses dan dinyatakan lunas sebelumnya.',
                ];
            }

            $bill = $lockedTx->studentBill()->lockForUpdate()->firstOrFail();
            $student = $lockedTx->student()->with('guardian')->firstOrFail();

            // 1. Update status tagihan siswa
            $newPaid = (float) $bill->amount_paid + (float) $lockedTx->amount;
            $bill->amount_paid = $newPaid;
            $bill->status = ($newPaid >= (float) $bill->amount_due) ? 'paid' : 'partially_paid';
            $bill->save();

            // 2. Buat kuitansi kasir resmi (PaymentTransaction)
            $receiptNumber = $this->docGenerator->next('KW', 5);
            $receivedByUser = $lockedTx->payerUser ?: User::whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))->first();

            $paymentTransaction = PaymentTransaction::create([
                'receipt_number' => $receiptNumber,
                'student_id' => $student->id,
                'total_amount' => $lockedTx->amount,
                'payment_method' => strtolower($lockedTx->payment_channel) === 'qris' ? 'qris' : 'transfer',
                'received_by' => $receivedByUser?->id,
                'paid_at' => now(),
                'notes' => 'Pembayaran Online TriPay ('.$lockedTx->order_id.' - '.$lockedTx->payment_channel.')',
            ]);

            PaymentTransactionItem::create([
                'payment_transaction_id' => $paymentTransaction->id,
                'student_bill_id' => $bill->id,
                'amount_allocated' => $lockedTx->amount,
            ]);

            // 3. Update OnlinePaymentTransaction
            $lockedTx->status = 'settlement';
            $lockedTx->paid_at = now();
            $lockedTx->payment_transaction_id = $paymentTransaction->id;
            $lockedTx->raw_payload = array_merge($lockedTx->raw_payload ?? [], ['webhook' => $payload]);
            $lockedTx->save();

            // 4. Kirim notifikasi kuitansi lunas ke WhatsApp wali murid
            $phone = $student->guardian?->phone;
            if (! empty($phone)) {
                $appName = Branding::appName();
                $amountRupiah = 'Rp '.number_format((float) $lockedTx->amount, 0, ',', '.');
                $receiptUrl = route('portal.receipts.print', ['paymentTransaction' => $paymentTransaction->id]);

                $message = "*{$appName} - Bukti Pembayaran Lunas*\n\n".
                    "Alhamdulillah, pembayaran tagihan santri telah berhasil diverifikasi oleh sistem.\n\n".
                    "• Nama Santri: *{$student->name}*\n".
                    "• No. Kuitansi: *{$receiptNumber}*\n".
                    "• Order ID: *{$lockedTx->order_id}*\n".
                    "• Metode: *{$lockedTx->payment_channel_name}*\n".
                    "• Jumlah: *{$amountRupiah}*\n".
                    '• Waktu: *'.now()->translatedFormat('d F Y, H:i')." WIB*\n\n".
                    "Unduh / Cetak Kuitansi Resmi:\n{$receiptUrl}\n\n".
                    'Jazakumullahu khairan atas pembayaran tepat waktu.';

                $this->fonnteService->send($phone, $message);
            }

            return [
                'status' => 'settled',
                'receipt_number' => $receiptNumber,
                'message' => 'Pembayaran berhasil diverifikasi dan kuitansi resmi telah dibuat.',
            ];
        });
    }
}
