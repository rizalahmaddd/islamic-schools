<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Kartu RFID & Dompet Digital Santri</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pendaftaran Smart Card santri, saldo kantin cashless, top-up tunai, dan pengaturan limit belanja harian.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('cashless.pos')" wire:navigate>
                <x-icon name="shopping-cart" class="mr-1.5 h-4 w-4" />
                POS Kasir Kantin
            </x-secondary-button>
            @can('manage-cashless')
                <x-primary-button wire:click="openCardModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Daftarkan Kartu RFID
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari santri, NIS, atau UID kartu..." />
        </div>
        <div>
            <x-select wire:model.live="statusFilter" class="w-full">
                <option value="">Semua Status Kartu</option>
                <option value="has_card">Sudah Memiliki Kartu</option>
                <option value="no_card">Belum Memiliki Kartu</option>
                <option value="blocked">Kartu Diblokir / Nonaktif</option>
            </x-select>
        </div>
    </div>

    {{-- Data Table --}}
    <x-table :pagination="$students">
        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
            <tr>
                <x-table.th>Nama Santri & NIS</x-table.th>
                <x-table.th>Kartu Smart Card RFID</x-table.th>
                <x-table.th class="text-right">Saldo Dompet</x-table.th>
                <x-table.th class="text-right">Limit Harian</x-table.th>
                <x-table.th class="text-center">Status Kartu</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($students as $student)
                @php
                    /** @var \App\Models\Student $student */
                    $card = $student->rfidCards->first();
                    $wallet = $student->wallet;
                @endphp
                <x-table.tr wire:key="student-{{ $student->id }}">
                    <x-table.td class="font-medium text-slate-900 dark:text-white">
                        <div>{{ $student->name }}</div>
                        <div class="font-mono text-xs text-slate-500">NIS: {{ $student->nis ?: '-' }}</div>
                    </x-table.td>
                    <x-table.td>
                        @if ($card)
                            <div class="font-mono text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <x-icon name="qr-code" class="h-3.5 w-3.5 text-emerald-600" />
                                {{ $card->card_uid }}
                            </div>
                            <div class="text-[11px] text-slate-500">{{ $card->label }}</div>
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ada kartu</span>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-right font-mono text-xs font-bold text-emerald-700 dark:text-emerald-400">
                        Rp {{ number_format((float) ($wallet?->balance ?? 0), 0, ',', '.') }}
                    </x-table.td>
                    <x-table.td class="text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                        Rp {{ number_format((float) ($wallet?->daily_limit ?? 50000), 0, ',', '.') }} / hari
                    </x-table.td>
                    <x-table.td class="text-center">
                        @if (! $card)
                            <x-badge color="slate">Belum Terbit</x-badge>
                        @elseif ($card->is_active)
                            <x-badge color="emerald">Aktif</x-badge>
                        @else
                            <x-badge color="rose">Diblokir</x-badge>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('manage-cashless')
                                <x-primary-button size="xs" wire:click="openTopupModal({{ $student->id }})">
                                    <x-icon name="wallet" class="mr-1 h-3.5 w-3.5" />
                                    Isi Saldo
                                </x-primary-button>
                                <x-secondary-button size="xs" wire:click="openLimitModal({{ $student->id }})" title="Ubah Limit Harian">
                                    Limit
                                </x-secondary-button>
                                @if (! $card)
                                    <x-secondary-button size="xs" wire:click="openCardModal({{ $student->id }})" title="Daftarkan Kartu RFID">
                                        + Kartu
                                    </x-secondary-button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="toggleBlockCard({{ $card->id }})"
                                        class="inline-flex h-7 px-2 items-center justify-center rounded-lg text-xs font-semibold {{ $card->is_active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/50 dark:text-rose-400' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400' }}"
                                    >
                                        {{ $card->is_active ? 'Blokir' : 'Aktifkan' }}
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="6" class="p-0">
                        <x-empty-state
                            icon="credit-card"
                            title="Belum ada data santri"
                            description="Tambahkan santri pada Master Data untuk mulai mengelola kartu Smart Card RFID & saldo kantin."
                        />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Registrasi Kartu Baru --}}
    <x-record-form-modal
        name="card-modal"
        title="Daftarkan Kartu Smart Card RFID Santri"
        subtitle="Tempelkan kartu RFID pada scanner USB untuk membaca Card UID."
        icon="qr-code"
        max-width="md"
    >
        <form wire:submit="saveCard" id="card-form" class="space-y-4">
            <div>
                <x-input-label for="selectedStudentId" value="Pilih Santri / Siswa *" />
                <x-select id="selectedStudentId" class="mt-1 block w-full" wire:model="selectedStudentId" required>
                    <option value="">Pilih Santri</option>
                    @foreach ($allStudents as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} (NIS: {{ $st->nis ?: '-' }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('selectedStudentId')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="cardUid" value="Card UID (Hasil Scan RFID) *" />
                <x-text-input id="cardUid" type="text" class="mt-1 block w-full font-mono uppercase" wire:model="cardUid" placeholder="Tempel kartu pada alat scanner..." required />
                <p class="mt-1 text-xs text-slate-500">Contoh UID: 10 digit desimal atau hex dari kartu Mifare/RFID.</p>
                <x-input-error :messages="$errors->get('cardUid')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="cardLabel" value="Label / Nama Kartu *" />
                <x-text-input id="cardLabel" type="text" class="mt-1 block w-full" wire:model="cardLabel" placeholder="Mis. Kartu Utama / Gelang RFID" required />
                <x-input-error :messages="$errors->get('cardLabel')" class="mt-1" />
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeCardModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="card-form">
                <x-loading-label target="saveCard" label="Simpan Kartu" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>

    {{-- Modal Top-Up Saldo Dompet --}}
    <x-record-form-modal
        name="topup-modal"
        title="Isi Ulang Saldo Dompet Santri"
        subtitle="Penerimaan setoran tunai dari santri atau wali murid di loket kasir."
        icon="wallet"
        max-width="md"
    >
        <form wire:submit="saveTopup" id="topup-form" class="space-y-4">
            <div>
                <x-input-label for="topupAmount" value="Nominal Top-Up (Rp) *" />
                <x-text-input id="topupAmount" type="number" min="1000" step="5000" class="mt-1 block w-full font-mono text-base font-bold" wire:model="topupAmount" required />
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <button type="button" wire:click="$set('topupAmount', 20000)" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700/60 px-2 py-1 text-xs font-semibold transition cursor-pointer">20.000</button>
                    <button type="button" wire:click="$set('topupAmount', 50000)" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700/60 px-2 py-1 text-xs font-semibold transition cursor-pointer">50.000</button>
                    <button type="button" wire:click="$set('topupAmount', 100000)" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700/60 px-2 py-1 text-xs font-semibold transition cursor-pointer">100.000</button>
                    <button type="button" wire:click="$set('topupAmount', 200000)" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:border-slate-700/60 px-2 py-1 text-xs font-semibold transition cursor-pointer">200.000</button>
                </div>
                <x-input-error :messages="$errors->get('topupAmount')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="topupNotes" value="Catatan / Keterangan" />
                <x-text-input id="topupNotes" type="text" class="mt-1 block w-full" wire:model="topupNotes" placeholder="Mis. Setoran tunai orang tua" />
                <x-input-error :messages="$errors->get('topupNotes')" class="mt-1" />
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeTopupModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="topup-form">
                <x-loading-label target="saveTopup" label="Proses Top-Up" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>

    {{-- Modal Atur Limit Belanja Harian --}}
    <x-record-form-modal
        name="limit-modal"
        title="Atur Batas Limit Jajan Harian"
        subtitle="Batas belanja santri maksimal per hari untuk mengontrol pengeluaran."
        icon="settings"
        max-width="md"
    >
        <form wire:submit="saveLimit" id="limit-form" class="space-y-4">
            <div>
                <x-input-label for="newDailyLimit" value="Batas Maksimal Belanja Harian (Rp) *" />
                <x-text-input id="newDailyLimit" type="number" min="0" step="5000" class="mt-1 block w-full font-mono font-bold" wire:model="newDailyLimit" required />
                <p class="mt-1 text-xs text-slate-500">Jika santri berbelanja melebihi batas ini dalam 1 hari, mesin kasir akan otomatis menolak transaksi.</p>
                <x-input-error :messages="$errors->get('newDailyLimit')" class="mt-1" />
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeLimitModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="limit-form">
                <x-loading-label target="saveLimit" label="Simpan Limit" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>
</div>
