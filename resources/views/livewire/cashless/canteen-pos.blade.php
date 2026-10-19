<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Kasir Kantin Cashless RFID</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Layanan transaksi jajan santri non-tunai bebas uang tunai dengan verifikasi limit harian orang tua.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('cashless.cards')" wire:navigate>
                <x-icon name="credit-card" class="mr-1.5 h-4 w-4" />
                Kartu & Saldo Santri
            </x-secondary-button>
        </div>
    </div>

    {{-- Main POS Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Product Catalog (Col 7/12) --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="w-full sm:w-64">
                    <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari menu, snack, minuman..." />
                </div>
                <div class="w-full sm:w-60">
                    <x-select wire:model.live="selectedMerchantId" class="w-full">
                        @foreach ($merchants as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </x-select>
                </div>
            </div>

            {{-- Category Filter Tabs --}}
            <x-segmented>
                <x-tab-button :active="$categoryFilter === ''" wire:click="$set('categoryFilter', '')">
                    Semua
                </x-tab-button>
                <x-tab-button :active="$categoryFilter === 'makanan'" wire:click="$set('categoryFilter', 'makanan')">
                    Makanan
                </x-tab-button>
                <x-tab-button :active="$categoryFilter === 'minuman'" wire:click="$set('categoryFilter', 'minuman')">
                    Minuman
                </x-tab-button>
                <x-tab-button :active="$categoryFilter === 'snack'" wire:click="$set('categoryFilter', 'snack')">
                    Snack
                </x-tab-button>
                <x-tab-button :active="$categoryFilter === 'alat_tulis'" wire:click="$set('categoryFilter', 'alat_tulis')">
                    Alat Tulis
                </x-tab-button>
            </x-segmented>

            {{-- Products Grid Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse ($products as $product)
                    <button
                        type="button"
                        wire:click="addToCart({{ $product->id }})"
                        class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-emerald-500 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 group"
                    >
                        <div>
                            <span class="inline-block rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 uppercase dark:bg-slate-800 dark:text-slate-400">
                                {{ $product->category }}
                            </span>
                            <h3 class="mt-1.5 font-bold text-xs sm:text-sm text-slate-900 group-hover:text-emerald-600 dark:text-white line-clamp-2 leading-snug">
                                {{ $product->name }}
                            </h3>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800">
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                            </span>
                            <span class="rounded-full bg-emerald-50 p-1 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-950/50">
                                <x-icon name="plus" class="h-3.5 w-3.5" />
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                        Tidak ada menu atau produk di kategori ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Shopping Cart & RFID Tap Checkout (Col 5/12) --}}
        <div class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <x-icon name="shopping-cart" class="h-5 w-5 text-emerald-600" />
                    <h2 class="font-bold text-sm text-slate-900 dark:text-white">Pesanan Saat Ini</h2>
                </div>
                @if (!empty($cart))
                    <button type="button" wire:click="clearCart" class="text-xs text-rose-500 hover:text-rose-700">
                        Kosongkan
                    </button>
                @endif
            </div>

            {{-- Cart Items List --}}
            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 custom-scrollbar pr-1">
                @forelse ($cart as $item)
                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $item['name'] }}</p>
                            <p class="font-mono text-slate-500">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                class="h-6 w-6 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 flex items-center justify-center font-bold transition cursor-pointer"
                            >-</button>
                            <span class="w-6 text-center font-bold font-mono">{{ $item['quantity'] }}</span>
                            <button
                                type="button"
                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                class="h-6 w-6 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 flex items-center justify-center font-bold transition cursor-pointer"
                            >+</button>
                        </div>
                        <div class="w-20 text-right font-mono font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs text-slate-400">
                        <x-icon name="shopping-bag" class="h-8 w-8 mx-auto mb-2 opacity-40" />
                        Pilih menu makanan/minuman di samping untuk menambahkan ke pesanan.
                    </div>
                @endforelse
            </div>

            {{-- Total Amount Summary --}}
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4 dark:border-emerald-950 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Total Pembayaran</span>
                    <span class="font-mono text-xl font-extrabold text-emerald-700 dark:text-emerald-400">
                        Rp {{ number_format($this->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- RFID Card Tap Input --}}
            <form wire:submit="processPayment" class="space-y-3">
                <div class="relative">
                    <x-input-label for="cardUid" value="Tap Kartu Smart Card RFID Santri" />
                    <div class="relative mt-1">
                        <x-text-input
                            id="cardUid"
                            type="text"
                            class="block w-full pl-10 font-mono text-sm tracking-wider uppercase"
                            wire:model="cardUid"
                            placeholder="Tempelkan kartu RFID pada scanner..."
                            autofocus
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icon name="qr-code" class="h-4 w-4 text-emerald-600" />
                        </div>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">Scanner RFID USB akan otomatis mengetik UID kartu dan menekan Enter.</p>
                    <x-input-error :messages="$errors->get('cardUid')" class="mt-1" />
                </div>

                <x-primary-button type="submit" class="w-full justify-center py-2.5 text-sm" :disabled="empty($cart)">
                    <x-loading-label target="processPayment" label="Selesaikan Pembayaran Non-Tunai" />
                </x-primary-button>
            </form>
        </div>
    </div>

    {{-- Modal Transaksi Berhasil --}}
    <x-record-form-modal
        name="success-modal"
        title="Pembayaran Berhasil!"
        subtitle="Saldo santri telah terpotong dan tercatat di portal wali santri."
        icon="check"
        max-width="md"
    >
        @if ($lastTransaction)
            <div class="space-y-4 text-xs">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center dark:border-emerald-900 dark:bg-emerald-950/30">
                    <div class="text-[11px] text-emerald-800 dark:text-emerald-300 font-semibold uppercase">Nomor Referensi</div>
                    <div class="font-mono text-sm font-bold text-emerald-900 dark:text-emerald-100">{{ $lastTransaction->reference_number }}</div>
                    <div class="mt-2 font-mono text-2xl font-black text-emerald-700 dark:text-emerald-400">
                        Rp {{ number_format((float) $lastTransaction->amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-1.5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Santri:</span>
                        <strong class="text-slate-900 dark:text-white">{{ $lastTransaction->wallet?->student?->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Sisa Saldo Kartu:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $lastTransaction->balance_after, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Sisa Kuota Jajan Hari Ini:</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">Rp {{ number_format($lastTransaction->wallet?->remainingDailyLimit() ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Waktu Transaksi:</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $lastTransaction->created_at->format('d/m/Y H:i:s') }} WIB</span>
                    </div>
                </div>

                @if ($lastTransaction->order?->items)
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-700 dark:text-slate-300">Rincian Pembelian:</p>
                        @foreach ($lastTransaction->order->items as $it)
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>{{ $it->quantity }}x {{ $it->product_name }}</span>
                                <span class="font-mono">Rp {{ number_format((float) $it->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-primary-button wire:click="closeSuccessModal" class="w-full justify-center">
                Transaksi Baru (OK)
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>
</div>
