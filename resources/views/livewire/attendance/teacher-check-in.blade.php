<div class="max-w-xl mx-auto space-y-5" x-data="teacherCheckIn()">
    {{-- Header & Profile Guru --}}
    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold text-base">
                    {{ $teacher ? substr($teacher->name, 0, 2) : 'GR' }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-100">{{ $teacher?->name ?? 'Pendidik / Guru' }}</h2>
                    <p class="text-xs text-slate-400 font-mono">NIP: {{ $teacher?->nip ?: '-' }} • {{ $teacher?->specialization ?: 'Pendidik' }}</p>
                </div>
            </div>

            <a href="{{ route('attendance.teachers') }}" wire:navigate class="text-xs text-emerald-400 hover:underline flex items-center gap-1">
                <span>Daftar Presensi</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Live Clock & Tanggal --}}
        <div class="bg-slate-950/60 rounded-xl p-4 border border-slate-800/60 text-center space-y-1">
            <div class="text-xs text-slate-400 uppercase tracking-wider font-semibold">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <div class="text-3xl sm:text-4xl font-extrabold text-emerald-400 font-mono tracking-tight" x-text="currentTime">
                {{ now()->format('H:i:s') }}
            </div>
            <div class="text-[11px] text-slate-500">Batas Tepat Waktu: Sebelum pk. {{ $checkInLateTime }} WIB</div>
        </div>

        {{-- Status Presensi Hari Ini --}}
        <div class="grid grid-cols-2 gap-3 pt-1">
            <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-center">
                <span class="text-[11px] text-slate-400 block mb-1">Check-In Masuk</span>
                @if ($todayAttendance && $todayAttendance->check_in_time)
                    <span class="font-mono text-base font-bold text-slate-100">{{ substr((string) $todayAttendance->check_in_time, 0, 5) }}</span>
                    <div class="mt-1">
                        <x-badge :color="$todayAttendance->statusColor()">
                            {{ $todayAttendance->statusLabel() }}
                        </x-badge>
                    </div>
                @else
                    <span class="text-xs text-slate-500 italic">Belum Check-In</span>
                @endif
            </div>

            <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-center">
                <span class="text-[11px] text-slate-400 block mb-1">Check-Out Pulang</span>
                @if ($todayAttendance && $todayAttendance->check_out_time)
                    <span class="font-mono text-base font-bold text-slate-100">{{ substr((string) $todayAttendance->check_out_time, 0, 5) }}</span>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Selesai Mengajar
                        </span>
                    </div>
                @else
                    <span class="text-xs text-slate-500 italic">Belum Check-Out</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Error Alert --}}
    @if ($errorMessage)
        <div class="bg-rose-950/40 border border-rose-500/30 rounded-xl p-4 text-xs text-rose-300 flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
            <div>
                <span class="font-semibold block">Gagal Melakukan Presensi:</span>
                <span>{{ $errorMessage }}</span>
            </div>
        </div>
    @endif

    {{-- Kartu Aksi Geolocation Check-In / Check-Out --}}
    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-300">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400"></i>
                <span>Deteksi Geofencing GPS</span>
            </div>
            <span class="text-[11px] text-slate-400">Radius maks: {{ $radiusMeters }}m</span>
        </div>

        {{-- Info Koordinat & Jarak --}}
        <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-800/60 text-xs space-y-1.5 text-slate-400">
            <div class="flex justify-between">
                <span>Lokasi Sekolah:</span>
                <span class="font-mono text-slate-300">{{ number_format($schoolLat, 4) }}, {{ number_format($schoolLng, 4) }}</span>
            </div>
            <template x-if="userLat && userLng">
                <div class="space-y-1.5 pt-1 border-t border-slate-800/60">
                    <div class="flex justify-between">
                        <span>Koordinat Anda:</span>
                        <span class="font-mono text-slate-200" x-text="userLat.toFixed(5) + ', ' + userLng.toFixed(5)"></span>
                    </div>
                    @if ($calculatedDistance !== null)
                        <div class="flex justify-between font-semibold">
                            <span>Jarak dari Sekolah:</span>
                            <span class="font-mono {{ $calculatedDistance <= $radiusMeters ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $calculatedDistance }} meter ({{ $calculatedDistance <= $radiusMeters ? 'Dalam Radius' : 'Di Luar Radius' }})
                            </span>
                        </div>
                    @endif
                </div>
            </template>
        </div>

        {{-- Action Buttons --}}
        <div class="space-y-3 pt-2">
            @if (! $todayAttendance || ! $todayAttendance->check_in_time)
                <button
                    type="button"
                    @click="submitCheckIn()"
                    :disabled="isLocating"
                    class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] transition shadow-lg shadow-emerald-950/50 flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <i data-lucide="log-in" class="w-5 h-5" x-show="!isLocating"></i>
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="isLocating" style="display: none;"></i>
                    <span x-text="isLocating ? 'Mendeteksi Posisi GPS...' : 'Check-In Masuk Sekarang'"></span>
                </button>
            @elseif (! $todayAttendance->check_out_time)
                <button
                    type="button"
                    @click="submitCheckOut()"
                    :disabled="isLocating"
                    class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-sky-600 hover:bg-sky-500 active:scale-[0.99] transition shadow-lg shadow-sky-950/50 flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <i data-lucide="log-out" class="w-5 h-5" x-show="!isLocating"></i>
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="isLocating" style="display: none;"></i>
                    <span x-text="isLocating ? 'Mendeteksi Posisi GPS...' : 'Check-Out Kepulangan'"></span>
                </button>
            @else
                <div class="bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-4 text-center space-y-1">
                    <div class="text-sm font-bold text-emerald-400">Alhamdulillah, Presensi Hari Ini Selesai</div>
                    <p class="text-xs text-slate-400">Terima kasih atas dedikasi dan pengabdian Anda dalam membina santri hari ini.</p>
                </div>
            @endif

            <p class="text-[11px] text-slate-500 text-center leading-relaxed">
                Pastikan izin akses lokasi (GPS) browser diaktifkan agar sistem dapat memvalidasi posisi Anda secara akurat.
            </p>
        </div>
    </div>
</div>

<script>
    function teacherCheckIn() {
        return {
            currentTime: '{{ now()->format("H:i:s") }}',
            userLat: null,
            userLng: null,
            isLocating: false,

            init() {
                setInterval(() => {
                    const now = new Date();
                    this.currentTime = now.toTimeString().split(' ')[0];
                }, 1000);
            },

            locate() {
                return new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung Geolocation GPS.');
                        reject('Geolocation not supported');
                        return;
                    }

                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.userLat = position.coords.latitude;
                            this.userLng = position.coords.longitude;
                            this.isLocating = false;
                            resolve({ lat: this.userLat, lng: this.userLng });
                        },
                        (error) => {
                            this.isLocating = false;
                            let msg = 'Gagal mengakses GPS: ';
                            if (error.code === 1) msg += 'Izin lokasi ditolak oleh pengguna.';
                            else if (error.code === 2) msg += 'Posisi GPS tidak ditemukan.';
                            else msg += error.message;
                            alert(msg);
                            reject(error);
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                });
            },

            async submitCheckIn() {
                try {
                    const coords = await this.locate();
                    @this.processCheckIn(coords.lat, coords.lng);
                } catch (e) {
                    console.error('Check-in failed', e);
                }
            },

            async submitCheckOut() {
                try {
                    const coords = await this.locate();
                    @this.processCheckOut(coords.lat, coords.lng);
                } catch (e) {
                    console.error('Check-out failed', e);
                }
            }
        }
    }
</script>
