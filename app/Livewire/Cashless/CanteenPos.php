<?php

namespace App\Livewire\Cashless;

use App\Models\CanteenMerchant;
use App\Models\CanteenProduct;
use App\Models\WalletTransaction;
use App\Services\StudentWalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Kasir Kantin Cashless RFID'])]
#[Title('Kasir Kantin Cashless')]
class CanteenPos extends Component
{
    public ?int $selectedMerchantId = null;

    public string $categoryFilter = '';

    public string $search = '';

    /**
     * Keranjang belanja kasir.
     *
     * @var array<int, array{id: int, code: string, name: string, price: float, quantity: int, subtotal: float}>
     */
    public array $cart = [];

    // Scan input kartu RFID
    public string $cardUid = '';

    // Modal Transaksi Sukses
    public bool $showSuccessModal = false;

    public ?WalletTransaction $lastTransaction = null;

    public function mount(): void
    {
        $this->selectedMerchantId = CanteenMerchant::where('is_active', true)->value('id');
    }

    public function canAccess(): bool
    {
        return auth()->user()?->can('manage-cashless') || auth()->user()?->can('cashier-cashless') || auth()->user()?->hasRole(['admin', 'superadmin', 'tata_usaha']);
    }

    public function addToCart(int $productId): void
    {
        $product = CanteenProduct::findOrFail($productId);

        if (! $product->is_available) {
            $this->dispatch('notify', message: "{$product->name} sedang tidak tersedia.", type: 'error');

            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
        } else {
            $price = (float) $product->price;
            $this->cart[$productId] = [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ];
        }
    }

    public function updateQuantity(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeFromCart($productId);

            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] = $qty;
            $this->cart[$productId]['subtotal'] = $qty * $this->cart[$productId]['price'];
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->cardUid = '';
        $this->resetValidation();
    }

    public function getTotalProperty(): float
    {
        return (float) collect($this->cart)->sum('subtotal');
    }

    public function processPayment(StudentWalletService $walletService): void
    {
        abort_unless($this->canAccess(), 403);

        $this->validate([
            'cardUid' => ['required', 'string'],
        ]);

        if (empty($this->cart)) {
            $this->addError('cardUid', 'Keranjang belanja masih kosong.');

            return;
        }

        // Harga selalu dibaca ulang dari database; isi keranjang berasal dari browser dan bisa diubah.
        $products = CanteenProduct::query()->whereIn('id', array_keys($this->cart))->get()->keyBy('id');
        $items = [];
        $total = 0.0;

        foreach ($this->cart as $productId => $line) {
            $product = $products->get($productId);

            if (! $product || ! $product->is_available) {
                $this->addError('cardUid', ($product?->name ?? $line['name']).' sudah tidak tersedia. Hapus dari keranjang lalu coba lagi.');

                return;
            }

            $quantity = max(1, (int) $line['quantity']);
            $price = (float) $product->price;
            $total += $price * $quantity;
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $price * $quantity,
            ];
        }

        try {
            $tx = $walletService->processCanteenPurchase(
                cardUid: $this->cardUid,
                totalAmount: $total,
                items: $items,
                merchantId: $this->selectedMerchantId,
                cashierUserId: auth()->id()
            );

            $this->lastTransaction = $tx->load(['wallet.student', 'merchant', 'order.items']);
            $this->showSuccessModal = true;
            $this->cart = [];
            $this->cardUid = '';
            $this->dispatch('open-modal', 'success-modal');
            $this->dispatch('notify', message: 'Transaksi belanja non-tunai berhasil!');
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        }
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->lastTransaction = null;
        $this->dispatch('close-modal', 'success-modal');
    }

    public function render(): View
    {
        $merchants = CanteenMerchant::where('is_active', true)->orderBy('name')->get();

        $products = CanteenProduct::query()
            ->where('is_available', true)
            ->when($this->selectedMerchantId, fn ($q) => $q->where('canteen_merchant_id', $this->selectedMerchantId))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.cashless.canteen-pos', [
            'merchants' => $merchants,
            'products' => $products,
        ]);
    }
}
