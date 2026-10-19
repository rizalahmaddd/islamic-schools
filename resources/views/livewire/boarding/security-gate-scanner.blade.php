<div class="mx-auto max-w-2xl space-y-6">
    {{-- Top Header / Mobile Friendly --}}
    <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 p-5 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                <x-icon name="shield-check" class="h-6 w-6 text-white" />
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-tight">Pos Keamanan Gerbang</h1>
                <p class="text-xs text-emerald-100">Validasi Tiket Izin Santri Keluar / Kembali</p>
            </div>
        </div>
        <div class="text-right">
            <div class="font-mono text-sm font-bold">{{ now()->format('H:i') }} WIB</div>
            <div class="text-[11px] text-emerald-200">{{ now()->translatedFormat('l, d M Y') }}</div>
        </div>
    </div>

    {{-- Scanner / Input Box --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="flex items-center justify-between">
            <label for="qr_token_input" class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Pindai QR / Masukkan Nomor Izin
            </label>
            @if($scannedLeave)
                <button wire:click="resetScanner" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400">
                    Reset Pindaian
                </button>
            @endif
        </div>

        <div class="flex gap-2">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-icon name="qr-code" class="h-5 w-5 text-slate-400" />
                </div>
                <input
                    id="qr_token_input"
                    wire:model="qr_token_input"
                    wire:keydown.enter="checkToken($event.target.value)"
                    type="text"
                    autofocus
                    placeholder="Scan QR code atau ketik IZN-..."
                    class="block w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-10 pr-4 font-mono text-sm shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
            </div>
            <x-primary-button wire:click="checkToken($wire.qr_token_input)" class="px-5 py-3 rounded-xl">
                Cek
            </x-primary-button>
        </div>

        {{-- Pemindai kamera memakai BarcodeDetector bawaan browser (Chrome/Edge Android). Tanpa dukungan itu,
             satpam tetap bisa memakai scanner USB/Bluetooth atau mengetik nomor izin. --}}
        <div
            x-data="{
                supported: 'BarcodeDetector' in window && !!navigator.mediaDevices?.getUserMedia,
                scanning: false,
                error: '',
                stream: null,
                timer: null,
                async start() {
                    this.error = '';
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                    } catch (e) {
                        this.error = 'Kamera tidak bisa dibuka. Izinkan akses kamera di browser (butuh koneksi HTTPS), atau ketik nomor izin.';
                        return;
                    }
                    this.scanning = true;
                    await this.$nextTick();
                    this.$refs.video.srcObject = this.stream;
                    await this.$refs.video.play().catch(() => {});
                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    this.timer = setInterval(async () => {
                        try {
                            const codes = await detector.detect(this.$refs.video);
                            if (codes.length) {
                                const value = codes[0].rawValue;
                                this.stop();
                                $wire.set('qr_token_input', value);
                                $wire.checkToken(value);
                            }
                        } catch (e) {}
                    }, 350);
                },
                stop() {
                    clearInterval(this.timer);
                    this.stream?.getTracks().forEach((t) => t.stop());
                    this.stream = null;
                    this.scanning = false;
                },
            }"
            x-on:livewire:navigating.window="stop()"
            class="space-y-3"
        >
            <template x-if="supported">
                <div class="space-y-3">
                    <x-secondary-button type="button" x-show="!scanning" x-on:click="start()" class="w-full justify-center">
                        <x-icon name="camera" class="h-4 w-4 text-emerald-500" />
                        Pindai dengan Kamera
                    </x-secondary-button>
                    <div x-show="scanning" x-cloak class="space-y-2">
                        <video x-ref="video" playsinline muted class="aspect-square w-full rounded-xl bg-slate-950 object-cover"></video>
                        <x-secondary-button type="button" x-on:click="stop()" class="w-full justify-center">Tutup Kamera</x-secondary-button>
                    </div>
                </div>
            </template>
            <p x-show="error" x-text="error" x-cloak class="text-xs text-rose-500" role="alert"></p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                <span x-show="supported">Arahkan kamera ke QR tiket, atau gunakan scanner USB/Bluetooth.</span>
                <span x-show="!supported" x-cloak>Browser ini belum mendukung pemindaian kamera. Gunakan scanner USB/Bluetooth atau ketik nomor izin.</span>
            </p>
        </div>
    </div>

    {{-- Alert / Feedback Message --}}
    @if($scanFeedback)
        <div class="rounded-xl p-4 text-sm font-medium flex items-start gap-3 {{ match($feedbackType) {
            'success' => 'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'warning' => 'bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            default => 'bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
        } }}">
            <div class="shrink-0 mt-0.5">
                @if($feedbackType === 'success')
                    <x-icon name="check-circle" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                @elseif($feedbackType === 'warning')
                    <x-icon name="alert-triangle" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                @else
                    <x-icon name="x-circle" class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                @endif
            </div>
            <div>{{ $scanFeedback }}</div>
        </div>
    @endif

    {{-- Detail Data Santri Terpindai --}}
    @if($scannedLeave)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5 animate-in fade-in duration-200">
            {{-- Header Profil Santri --}}
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                    {{ strtoupper(substr($scannedLeave->student->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white truncate">
                            {{ $scannedLeave->student->name }}
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500">
                        NIS: {{ $scannedLeave->student->nis ?: '-' }} · {{ $scannedLeave->student->gender === 'male' ? 'Santri Putra' : 'Santri Putri' }}
                    </p>
                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                        <x-badge :color="$scannedLeave->statusBadgeColor()">
                            {{ $scannedLeave->statusLabel() }}
                        </x-badge>
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ $scannedLeave->leaveTypeLabel() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Jadwal & Penjemput --}}
            <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4 text-xs dark:bg-slate-800/60">
                <div>
                    <span class="text-slate-500">Rencana Berangkat:</span>
                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $scannedLeave->departure_plan->format('d/m/Y H:i') }} WIB</div>
                </div>
                <div>
                    <span class="text-slate-500">Batas Waktu Kembali:</span>
                    <div class="font-bold text-rose-600 dark:text-rose-400">{{ $scannedLeave->return_plan->format('d/m/Y H:i') }} WIB</div>
                </div>
                <div class="col-span-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500">Penjemput / Pendamping:</span>
                    <div class="font-semibold text-slate-900 dark:text-white">{{ $scannedLeave->pickup_person ?: 'Mandiri / Wali Murid' }}</div>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-500">Keperluan / Alasan:</span>
                    <div class="text-slate-700 dark:text-slate-300">{{ $scannedLeave->reason }}</div>
                </div>
            </div>

            {{-- Status Waktu Riil --}}
            @if($scannedLeave->actual_departure_at || $scannedLeave->actual_returned_at)
                <div class="rounded-xl border border-dashed border-slate-300 p-3 text-xs space-y-1.5 dark:border-slate-700">
                    @if($scannedLeave->actual_departure_at)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Waktu Keluar Gerbang:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $scannedLeave->actual_departure_at->format('d/m/Y H:i') }} WIB</span>
                        </div>
                    @endif
                    @if($scannedLeave->actual_returned_at)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Waktu Kembali:</span>
                            <span class="font-semibold {{ $scannedLeave->status === 'late' ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $scannedLeave->actual_returned_at->format('d/m/Y H:i') }} WIB
                                @if($scannedLeave->status === 'late')
                                    (Terlambat)
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Action Buttons Khusus Pos Satpam --}}
            <div class="space-y-3 pt-2">
                @if($scannedLeave->isApproved())
                    {{-- Santri mau keluar gerbang --}}
                    <button
                        wire:click="confirmExit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-4 text-base font-bold text-white shadow-md hover:bg-emerald-700 active:scale-[0.98] transition"
                    >
                        <x-icon name="log-out" class="h-5 w-5" />
                        Konfirmasi Santri KELUAR Gerbang
                    </button>
                    <p class="text-center text-[11px] text-slate-500">Otomatis mengirimkan WhatsApp notifikasi santri telah keluar ke nomor orang tua.</p>
                @elseif($scannedLeave->isActive())
                    {{-- Santri kembali ke pondok --}}
                    @php
                        $isLate = now()->greaterThan($scannedLeave->return_plan);
                    @endphp

                    @if($isLate)
                        <div class="rounded-xl bg-rose-50 p-3 text-xs text-rose-800 border border-rose-200 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-800">
                            <strong>PERHATIAN KETERLAMBATAN:</strong> Waktu izin telah lewat dari {{ $scannedLeave->return_plan->format('d/m/Y H:i') }}. Mengonfirmasi kembali akan otomatis mencatat poin pelanggaran disiplin ke riwayat santri.
                        </div>
                    @endif

                    <div>
                        <x-input-label for="notes" value="Catatan Satpam (Opsional)" />
                        <x-text-input id="notes" wire:model="notes" type="text" class="mt-1 block w-full" placeholder="Kondisi santri saat kembali..." />
                    </div>

                    <button
                        wire:click="confirmReturn"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl {{ $isLate ? 'bg-amber-600 hover:bg-amber-700' : 'bg-sky-600 hover:bg-sky-700' }} px-6 py-4 text-base font-bold text-white shadow-md active:scale-[0.98] transition"
                    >
                        <x-icon name="log-in" class="h-5 w-5" />
                        Konfirmasi Santri KEMBALI ke Pondok {{ $isLate ? '(Terlambat)' : '' }}
                    </button>
                    <p class="text-center text-[11px] text-slate-500">Status izin akan ditutup dan laporan kepulangan terkirim ke wali santri.</p>
                @else
                    <div class="text-center py-2 text-xs text-slate-400">
                        Tidak ada tindakan satpam yang diperlukan untuk status tiket ini.
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Log Aktivitas Gerbang Hari Ini --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Aktivitas Gerbang Hari Ini</h3>
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                {{ $todayGateActivity->count() }} Pergerakan
            </span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($todayGateActivity as $activity)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $activity->student->name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $activity->leave_number }} · {{ $activity->leaveTypeLabel() }}</div>
                    </div>
                    <div class="text-right">
                        @if($activity->actual_returned_at && $activity->actual_returned_at->isToday())
                            <span class="inline-flex items-center gap-1 font-semibold {{ $activity->status === 'late' ? 'text-rose-600' : 'text-sky-600' }}">
                                <x-icon name="arrow-down-left" class="h-3.5 w-3.5" />
                                Kembali {{ $activity->actual_returned_at->format('H:i') }}
                            </span>
                        @elseif($activity->actual_departure_at && $activity->actual_departure_at->isToday())
                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                                <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                                Keluar {{ $activity->actual_departure_at->format('H:i') }}
                            </span>
                        @endif
                        <div class="mt-0.5">
                            <x-badge :color="$activity->statusBadgeColor()">{{ $activity->statusLabel() }}</x-badge>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    Belum ada santri keluar atau kembali melalui gerbang hari ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
