<div class="space-y-8">
    {{-- ==================== BREADCRUMB NAVIGASI KE BERANDA ==================== --}}
    <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
        <a
            href="{{ route('home') }}"
            class="inline-flex items-center gap-1.5 text-slate-300 hover:text-emerald-400 font-semibold transition group"
        >
            <i data-lucide="home" class="w-3.5 h-3.5 text-emerald-400 group-hover:scale-110 transition-transform"></i>
            <span>Beranda Utama</span>
        </a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
        <span class="text-emerald-400 font-bold truncate">Pendaftaran Santri Baru (PPDB Online)</span>
    </nav>

    {{-- ==================== HEADER & HERO SECTION ==================== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-800 via-emerald-900 to-slate-900 p-6 sm:p-10 lg:p-12 text-white shadow-xl shadow-emerald-950/20 border border-emerald-700/40">
        <!-- Glowing Ambient Lighting -->
        <div class="absolute -top-12 -right-12 h-64 w-64 rounded-full bg-emerald-500/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 h-64 w-64 rounded-full bg-teal-400/15 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-3.5 max-w-2xl">
                <!-- Badges & Back Button -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md px-3 py-1 text-xs font-semibold text-emerald-100 ring-1 ring-white/20 transition active:scale-95"
                    >
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Kembali ke Beranda</span>
                    </a>

                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 backdrop-blur-md px-3.5 py-1 text-xs font-semibold text-emerald-200 ring-1 ring-emerald-400/30">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>PPDB Online Tahun Ajaran 2026/2027</span>
                    </div>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight text-white">
                    Pendaftaran Santri Baru (PPDB)
                </h1>

                <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed font-normal">
                    {{ \App\Support\Branding::tagline() ?: 'Isi formulir pendaftaran calon santri secara online, lalu simpan nomor registrasi untuk memeriksa status seleksi dan verifikasi berkas.' }}
                </p>
            </div>

            {{-- Action Cek Status Pendaftaran --}}
            <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0">
                <button
                    type="button"
                    wire:click="openLookupModal"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur-md px-5 py-3 text-xs sm:text-sm font-bold text-white border border-white/20 shadow-sm transition active:scale-95 cursor-pointer"
                >
                    <i data-lucide="search" class="w-4 h-4 text-emerald-200"></i>
                    <span>Cek Status Pendaftaran</span>
                </button>
                <a
                    href="#alur-ppdb"
                    class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-emerald-200 hover:text-white transition"
                >
                    <span>Lihat Alur & Panduan</span>
                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    @if($isSuccess && $registeredCandidate)
        {{-- ==================== SUKSES PENDAFTARAN CARD ==================== --}}
        <div class="rounded-3xl border border-emerald-500/30 bg-slate-900 p-6 sm:p-10 shadow-xl space-y-6 text-center animate-in zoom-in-95 duration-300">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-500/15 text-emerald-400 ring-8 ring-emerald-500/10 shadow-lg">
                <i data-lucide="check-circle-2" class="h-10 w-10 text-emerald-400"></i>
            </div>

            <div class="space-y-2 max-w-xl mx-auto">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-3.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Pendaftaran Berhasil Dikirim
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-100 pt-1">
                    Alhamdulillah, Data Telah Kami Terima!
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Nomor registrasi resmi telah diterbitkan. Harap simpan bukti pendaftaran ini dan konfirmasi berkas melalui panitia penerimaan santri baru.
                </p>
            </div>

            {{-- Box Nomor Registrasi --}}
            <div class="relative overflow-hidden rounded-2xl border-2 border-dashed border-emerald-500/60 bg-emerald-500/10 p-6 max-w-lg mx-auto" x-data="{ copied: false }">
                <div class="text-xs uppercase font-bold tracking-widest text-emerald-400">Nomor Registrasi Calon Santri</div>
                <div class="mt-2 flex items-center justify-center gap-3">
                    <div class="font-mono text-3xl sm:text-4xl font-black text-emerald-300 tracking-wider">
                        {{ $registeredCandidate->registration_number }}
                    </div>
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText('{{ $registeredCandidate->registration_number }}'); copied = true; setTimeout(() => copied = false, 2500)"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/40 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-emerald-300 hover:bg-slate-700 transition shadow-xs cursor-pointer"
                        title="Salin Nomor Registrasi"
                    >
                        <span x-show="!copied" class="flex items-center gap-1">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span>Salin</span>
                        </span>
                        <span x-show="copied" class="text-emerald-400 font-bold flex items-center gap-1">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Tersalin!</span>
                        </span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    Simpan nomor ini untuk memeriksa pengumuman seleksi dan verifikasi berkas administrasi.
                </p>
            </div>

            {{-- Ringkasan Pendaftar --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-800/50 p-5 text-xs space-y-3 text-left max-w-lg mx-auto">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-2.5">
                    <span class="text-slate-400">Nama Calon Santri:</span>
                    <span class="font-bold text-slate-100 text-sm">{{ $registeredCandidate->student_name }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-2.5">
                    <span class="text-slate-400">Gelombang PPDB:</span>
                    <span class="font-semibold text-slate-200">{{ $registeredCandidate->batch->name }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-2.5">
                    <span class="text-slate-400">Orang Tua / Wali:</span>
                    <span class="font-medium text-slate-200">{{ $registeredCandidate->guardian_name }} ({{ $registeredCandidate->guardian_phone }})</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Biaya Formulir & Seleksi:</span>
                    <span class="font-bold text-emerald-400 text-sm">
                        Rp {{ number_format((float) $registeredCandidate->batch->registration_fee, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Panduan Langkah Selanjutnya --}}
            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 text-xs text-left max-w-lg mx-auto space-y-2">
                <div class="font-bold text-amber-300 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                    <span>Langkah Selanjutnya Calon Wali Santri:</span>
                </div>
                <ol class="list-decimal list-inside space-y-1 text-amber-200/90 pl-1 leading-relaxed">
                    <li>Unduh atau cetak bukti formulir pendaftaran melalui tombol di bawah.</li>
                    <li>Lakukan konfirmasi ke WhatsApp panitia PPDB dengan melampirkan nomor registrasi.</li>
                    <li>Ikuti jadwal tes observasi, baca Qur'an, dan wawancara sesuai jadwal gelombang terpilih.</li>
                </ol>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a
                    href="{{ \Illuminate\Support\Facades\URL::signedRoute('ppdb.print', $registeredCandidate->registration_number) }}"
                    target="_blank"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition"
                >
                    <i data-lucide="printer" class="h-4 w-4"></i>
                    <span>Cetak Bukti Formulir PDF</span>
                </a>

                <button
                    type="button"
                    wire:click="resetForm"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-700 bg-slate-800 px-5 py-3.5 text-sm font-semibold text-slate-200 hover:bg-slate-700 active:scale-95 transition cursor-pointer"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4 text-slate-400"></i>
                    <span>Daftar Calon Santri Lain</span>
                </button>

                <a
                    href="{{ route('home') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-3.5 text-sm font-bold text-emerald-400 hover:bg-emerald-500/20 active:scale-95 transition"
                >
                    <i data-lucide="home" class="w-4 h-4"></i>
                    <span>Kembali ke Beranda Utama</span>
                </a>
            </div>
        </div>
    @else
        {{-- ==================== MULTI-STEP PROGRESS STEPPER ==================== --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                {{-- Step 1 --}}
                <button
                    type="button"
                    wire:click="goToStep(1)"
                    class="flex flex-col sm:flex-row items-center gap-2 group text-left cursor-pointer {{ $currentStep >= 1 ? 'text-emerald-400' : 'text-slate-400' }}"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold text-sm transition {{ $currentStep === 1 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/30 ring-4 ring-emerald-500/20' : ($currentStep > 1 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400') }}">
                        @if($currentStep > 1)
                            <i data-lucide="check" class="w-5 h-5 text-emerald-400"></i>
                        @else
                            1
                        @endif
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-xs font-semibold leading-none">Langkah 1</div>
                        <div class="text-[11px] text-slate-400 mt-1">Pilih Gelombang</div>
                    </div>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 {{ $currentStep > 1 ? 'bg-emerald-500' : 'bg-slate-800' }}"></div>

                {{-- Step 2 --}}
                <button
                    type="button"
                    wire:click="goToStep(2)"
                    @disabled($currentStep < 2)
                    class="flex flex-col sm:flex-row items-center gap-2 group text-left {{ $currentStep >= 2 ? 'text-emerald-400 cursor-pointer' : 'text-slate-500 cursor-not-allowed' }}"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold text-sm transition {{ $currentStep === 2 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/30 ring-4 ring-emerald-500/20' : ($currentStep > 2 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400') }}">
                        @if($currentStep > 2)
                            <i data-lucide="check" class="w-5 h-5 text-emerald-400"></i>
                        @else
                            2
                        @endif
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-xs font-semibold leading-none">Langkah 2</div>
                        <div class="text-[11px] text-slate-400 mt-1">Identitas Santri</div>
                    </div>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 {{ $currentStep > 2 ? 'bg-emerald-500' : 'bg-slate-800' }}"></div>

                {{-- Step 3 --}}
                <button
                    type="button"
                    wire:click="goToStep(3)"
                    @disabled($currentStep < 3)
                    class="flex flex-col sm:flex-row items-center gap-2 group text-left {{ $currentStep >= 3 ? 'text-emerald-400 cursor-pointer' : 'text-slate-500 cursor-not-allowed' }}"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold text-sm transition {{ $currentStep === 3 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/30 ring-4 ring-emerald-500/20' : ($currentStep > 3 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400') }}">
                        @if($currentStep > 3)
                            <i data-lucide="check" class="w-5 h-5 text-emerald-400"></i>
                        @else
                            3
                        @endif
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-xs font-semibold leading-none">Langkah 3</div>
                        <div class="text-[11px] text-slate-400 mt-1">Data Orang Tua</div>
                    </div>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 {{ $currentStep > 3 ? 'bg-emerald-500' : 'bg-slate-800' }}"></div>

                {{-- Step 4 --}}
                <div class="flex flex-col sm:flex-row items-center gap-2 text-left {{ $currentStep === 4 ? 'text-emerald-400' : 'text-slate-500' }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold text-sm transition {{ $currentStep === 4 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/30 ring-4 ring-emerald-500/20' : 'bg-slate-800 text-slate-400' }}">
                        4
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-xs font-semibold leading-none">Langkah 4</div>
                        <div class="text-[11px] text-slate-400 mt-1">Konfirmasi</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== FORM CONTAINER ==================== --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-10 shadow-xl">
            {{-- STEP 1: PILIH GELOMBANG PENDAFTARAN --}}
            @if($currentStep === 1)
                <div class="space-y-6 animate-in fade-in duration-200">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500/20 text-xs font-bold text-emerald-400 border border-emerald-500/30">1</span>
                            Pilih Gelombang Pendaftaran
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Tentukan jalur dan periode seleksi masuk yang sesuai dengan jenjang pendidikan calon santri.
                        </p>
                    </div>

                    @if($openBatches->isEmpty())
                        <div class="rounded-2xl bg-amber-500/10 p-6 text-center border border-amber-500/30 text-amber-200">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-amber-400 mx-auto mb-2"></i>
                            <div class="font-bold text-amber-200 text-sm">Belum Ada Gelombang Pendaftaran Dibuka</div>
                            <p class="text-xs text-amber-300/80 mt-1 max-w-md mx-auto">
                                Mohon maaf, saat ini periode pendaftaran calon santri baru belum aktif. Silakan hubungi layanan panitia PPDB untuk info jadwal pembukaan gelombang berikutnya.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($openBatches as $batch)
                                <label class="group relative flex flex-col justify-between cursor-pointer rounded-2xl border-2 p-5 shadow-xs transition duration-150 {{ $admission_batch_id === $batch->id ? 'border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/20' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700 hover:bg-slate-800/40' }}">
                                    <input
                                        type="radio"
                                        wire:model.live="admission_batch_id"
                                        value="{{ $batch->id }}"
                                        class="sr-only"
                                    />

                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="inline-block rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-500/30">
                                                    {{ $batch->academicYear->name ?? 'Tahun Ini' }}
                                                </span>
                                                <h3 class="text-base font-bold text-slate-100 mt-2">
                                                    {{ $batch->name }}
                                                </h3>
                                            </div>

                                            <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border {{ $admission_batch_id === $batch->id ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-600' }}">
                                                @if($admission_batch_id === $batch->id)
                                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-2 text-xs text-slate-300">
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span>Batas: {{ $batch->end_date->format('d M Y') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span>Biaya Pendaftaran: <strong class="text-slate-100 font-bold">Rp {{ number_format((float) $batch->registration_fee, 0, ',', '.') }}</strong></span>
                                            </div>
                                            @if($batch->quota)
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                    <span>Kuota: {{ $batch->quota }} Santri</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] font-medium text-emerald-400">
                                        <span>Klik untuk memilih gelombang ini</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('admission_batch_id')" class="mt-2" />

                        <div class="pt-4 flex justify-end">
                            <button
                                type="button"
                                wire:click="nextStep"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-emerald-600/25 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition cursor-pointer"
                            >
                                <span>Lanjut ke Data Calon Santri</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- STEP 2: IDENTITAS CALON SANTRI --}}
            @if($currentStep === 2)
                <div class="space-y-6 animate-in fade-in duration-200">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500/20 text-xs font-bold text-emerald-400 border border-emerald-500/30">2</span>
                            Identitas Calon Santri
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Masukkan nama dan data pokok calon santri sesuai dengan dokumen kependudukan resmi.
                        </p>
                    </div>

                    {{-- Nama Santri --}}
                    <div>
                        <x-input-label for="student_name" value="Nama Lengkap Calon Santri *" />
                        <x-text-input
                            id="student_name"
                            wire:model="student_name"
                            type="text"
                            class="mt-1 block w-full rounded-2xl"
                            placeholder="Contoh: Ahmad Farhan Baihaqi (sesuai akta kelahiran)"
                        />
                        <x-input-error :messages="$errors->get('student_name')" class="mt-1" />
                    </div>

                    {{-- Jenis Kelamin (Dual Cards) --}}
                    <div>
                        <x-input-label value="Jenis Kelamin *" />
                        <div class="grid grid-cols-2 gap-3 mt-1.5">
                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition {{ $gender === 'male' ? 'border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/20' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700' }}">
                                <input type="radio" wire:model.live="gender" value="male" class="sr-only" />
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $gender === 'male' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400' }}">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-100">Laki-laki (Ikhwan)</div>
                                    <div class="text-[10px] text-slate-400">Santri Putra</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition {{ $gender === 'female' ? 'border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/20' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700' }}">
                                <input type="radio" wire:model.live="gender" value="female" class="sr-only" />
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $gender === 'female' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400' }}">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-100">Perempuan (Akhwat)</div>
                                    <div class="text-[10px] text-slate-400">Santri Putri</div>
                                </div>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                    </div>

                    {{-- Asal Sekolah --}}
                    <div>
                        <x-input-label for="previous_school" value="Asal Sekolah / Madrasah Sebelumnya" />
                        <x-text-input
                            id="previous_school"
                            wire:model="previous_school"
                            type="text"
                            class="mt-1 block w-full rounded-2xl"
                            placeholder="Contoh: SD Negeri 1 Bandung / MI Al-Hikmah"
                        />
                        <x-input-error :messages="$errors->get('previous_school')" class="mt-1" />
                    </div>

                    {{-- Tempat & Tanggal Lahir --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="pob" value="Tempat Lahir" />
                            <x-text-input
                                id="pob"
                                wire:model="pob"
                                type="text"
                                class="mt-1 block w-full rounded-2xl"
                                placeholder="Kota / Kabupaten kelahiran..."
                            />
                            <x-input-error :messages="$errors->get('pob')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="dob" value="Tanggal Lahir" />
                            <x-text-input
                                id="dob"
                                wire:model="dob"
                                type="date"
                                class="mt-1 block w-full rounded-2xl"
                            />
                            <x-input-error :messages="$errors->get('dob')" class="mt-1" />
                        </div>
                    </div>

                    {{-- NISN & NIK --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nisn" value="NISN (Nomor Induk Siswa Nasional)" />
                            <x-text-input
                                id="nisn"
                                wire:model="nisn"
                                type="text"
                                class="mt-1 block w-full rounded-2xl font-mono text-sm"
                                placeholder="10 digit nomor NISN..."
                            />
                            <x-input-error :messages="$errors->get('nisn')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nik" value="NIK (Nomor Induk Kependudukan)" />
                            <x-text-input
                                id="nik"
                                wire:model="nik"
                                type="text"
                                class="mt-1 block w-full rounded-2xl font-mono text-sm"
                                placeholder="16 digit NIK santri..."
                            />
                            <x-input-error :messages="$errors->get('nik')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Navigation Actions --}}
                    <div class="pt-4 flex items-center justify-between border-t border-slate-800">
                        <button
                            type="button"
                            wire:click="prevStep"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-700 bg-slate-800 px-5 py-3 text-xs sm:text-sm font-semibold text-slate-200 hover:bg-slate-700 transition cursor-pointer"
                        >
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali ke Gelombang</span>
                        </button>

                        <button
                            type="button"
                            wire:click="nextStep"
                            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-emerald-600/25 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition cursor-pointer"
                        >
                            <span>Lanjut ke Data Wali</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 3: DATA ORANG TUA / WALI SANTRI --}}
            @if($currentStep === 3)
                <div class="space-y-6 animate-in fade-in duration-200">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500/20 text-xs font-bold text-emerald-400 border border-emerald-500/30">3</span>
                            Data Orang Tua / Wali Santri
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Kontak wali akan digunakan untuk konfirmasi penerimaan, informasi hasil seleksi, dan tagihan sekolah.
                        </p>
                    </div>

                    {{-- Nama & Hubungan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="guardian_name" value="Nama Lengkap Orang Tua / Wali *" />
                            <x-text-input
                                id="guardian_name"
                                wire:model="guardian_name"
                                type="text"
                                class="mt-1 block w-full rounded-2xl"
                                placeholder="Nama ayah, ibu, atau wali yang bertanggung jawab..."
                            />
                            <x-input-error :messages="$errors->get('guardian_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="guardian_relationship" value="Hubungan Keluarga *" />
                            <x-select id="guardian_relationship" wire:model="guardian_relationship" class="mt-1 block w-full rounded-2xl">
                                <option value="Ayah">Ayah Kandung</option>
                                <option value="Ibu">Ibu Kandung</option>
                                <option value="Wali">Wali / Keluarga</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('guardian_relationship')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Kontak: WhatsApp & Email --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="guardian_phone" value="Nomor WhatsApp Aktif *" />
                            <div class="relative mt-1">
                                <x-text-input
                                    id="guardian_phone"
                                    wire:model="guardian_phone"
                                    type="tel"
                                    class="block w-full rounded-2xl font-mono text-sm pl-9"
                                    placeholder="081299887766"
                                />
                                <i data-lucide="phone" class="w-4 h-4 text-emerald-400 absolute left-3 top-3.5"></i>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Konfirmasi nomor registrasi dan jadwal tes dikirim via WhatsApp.
                            </p>
                            <x-input-error :messages="$errors->get('guardian_phone')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="guardian_email" value="Alamat Email (Opsional)" />
                            <x-text-input
                                id="guardian_email"
                                wire:model="guardian_email"
                                type="email"
                                class="mt-1 block w-full rounded-2xl text-sm"
                                placeholder="nama@domain.com"
                            />
                            <x-input-error :messages="$errors->get('guardian_email')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Alamat Lengkap --}}
                    <div>
                        <x-input-label for="address" value="Alamat Domisili Lengkap" />
                        <x-textarea
                            id="address"
                            wire:model="address"
                            rows="3"
                            class="mt-1 block w-full rounded-2xl text-sm"
                            placeholder="Jalan, RT/RW, Kelurahan/Desa, Kecamatan, Kota/Kabupaten, Provinsi..."
                        />
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>

                    {{-- Catatan Khusus --}}
                    <div>
                        <x-input-label for="notes" value="Catatan Tambahan (Opsional)" />
                        <x-textarea
                            id="notes"
                            wire:model="notes"
                            rows="2"
                            class="mt-1 block w-full rounded-2xl text-sm"
                            placeholder="Riwayat hafalan Qur'an, prestasi akademik/non-akademik, riwayat alergi/kesehatan, dll..."
                        />
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>

                    {{-- Navigation Actions --}}
                    <div class="pt-4 flex items-center justify-between border-t border-slate-800">
                        <button
                            type="button"
                            wire:click="prevStep"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-700 bg-slate-800 px-5 py-3 text-xs sm:text-sm font-semibold text-slate-200 hover:bg-slate-700 transition cursor-pointer"
                        >
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali ke Data Santri</span>
                        </button>

                        <button
                            type="button"
                            wire:click="nextStep"
                            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-emerald-600/25 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition cursor-pointer"
                        >
                            <span>Tinjau Ringkasan Pendaftaran</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 4: KONFIRMASI & VERIFIKASI SEBELUM KIRIM --}}
            @if($currentStep === 4)
                <div class="space-y-6 animate-in fade-in duration-200">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500/20 text-xs font-bold text-emerald-400 border border-emerald-500/30">4</span>
                            Tinjau & Konfirmasi Data
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Pastikan seluruh identitas calon santri dan kontak wali yang dimasukkan sudah benar sebelum dikirimkan ke panitia.
                        </p>
                    </div>

                    {{-- Ringkasan Review Box --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Card 1: Data Calon Santri --}}
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/40 p-5 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-700/80 pb-2">
                                <div class="text-xs font-bold uppercase tracking-wider text-emerald-400">
                                    Data Calon Santri
                                </div>
                                <button
                                    type="button"
                                    wire:click="goToStep(2)"
                                    class="text-xs font-semibold text-emerald-400 hover:text-emerald-300"
                                >
                                    Ubah
                                </button>
                            </div>
                            <div class="text-xs space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Nama:</span>
                                    <span class="font-bold text-slate-100">{{ $student_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Jenis Kelamin:</span>
                                    <span class="font-medium text-slate-200">{{ $gender === 'male' ? 'Laki-laki (Ikhwan)' : 'Perempuan (Akhwat)' }}</span>
                                </div>
                                @if($previous_school)
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Asal Sekolah:</span>
                                        <span class="font-medium text-slate-200">{{ $previous_school }}</span>
                                    </div>
                                @endif
                                @if($pob || $dob)
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">TTL:</span>
                                        <span class="font-medium text-slate-200">{{ $pob ?: '-' }}, {{ $dob ?: '-' }}</span>
                                    </div>
                                @endif
                                @if($nisn)
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">NISN:</span>
                                        <span class="font-mono text-slate-200">{{ $nisn }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card 2: Data Orang Tua & Gelombang --}}
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/40 p-5 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-700/80 pb-2">
                                <div class="text-xs font-bold uppercase tracking-wider text-emerald-400">
                                    Wali & Gelombang
                                </div>
                                <button
                                    type="button"
                                    wire:click="goToStep(3)"
                                    class="text-xs font-semibold text-emerald-400 hover:text-emerald-300"
                                >
                                    Ubah
                                </button>
                            </div>
                            <div class="text-xs space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Nama Wali:</span>
                                    <span class="font-bold text-slate-100">{{ $guardian_name }} ({{ $guardian_relationship }})</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">WhatsApp:</span>
                                    <span class="font-mono text-slate-200">{{ $guardian_phone }}</span>
                                </div>
                                @if($guardian_email)
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Email:</span>
                                        <span class="text-slate-200">{{ $guardian_email }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Gelombang:</span>
                                    <span class="font-semibold text-emerald-400">{{ $selectedBatch?->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Biaya Pendaftaran:</span>
                                    <span class="font-bold text-slate-100">Rp {{ number_format((float) ($selectedBatch?->registration_fee ?? 0), 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pernyataan Persetujuan --}}
                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                        <x-checkbox
                            checked
                            required
                            label="Saya menyatakan dengan sesungguhnya bahwa seluruh data yang diisikan dalam formulir PPDB online ini adalah benar, dan saya bersedia mengikuti seluruh peraturan serta tahapan seleksi yang telah ditetapkan."
                        />
                    </div>

                    {{-- Navigation Actions --}}
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-800">
                        <button
                            type="button"
                            wire:click="prevStep"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-700 bg-slate-800 px-5 py-3 text-xs sm:text-sm font-semibold text-slate-200 hover:bg-slate-700 transition cursor-pointer"
                        >
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali Perbaiki Data</span>
                        </button>

                        <button
                            type="button"
                            wire:click="register"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-emerald-600/30 hover:from-emerald-500 hover:to-teal-500 active:scale-95 transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="register" class="flex items-center gap-2">
                                <i data-lucide="send" class="w-5 h-5"></i>
                                <span>Kirim Pendaftaran Santri Baru</span>
                            </span>
                            <span wire:loading wire:target="register" class="flex items-center gap-2">
                                <i data-lucide="loader" class="w-5 h-5 animate-spin"></i>
                                <span>Memproses Registrasi...</span>
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== INFORMASI ALUR PPDB ==================== --}}
    <div id="alur-ppdb" class="rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                    <i data-lucide="compass" class="w-5 h-5 text-emerald-400"></i>
                    <span>Tahapan & Alur Penerimaan Santri Baru</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">4 langkah mudah pendaftaran santri baru hingga penetapan asrama</p>
            </div>
            <a
                href="{{ route('home') }}#faq"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300"
            >
                <span>Lihat Panduan FAQ Lengkap</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-800/40 p-4 space-y-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold text-xs">1</div>
                <div class="font-bold text-xs text-slate-100">Pendaftaran Online</div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Mengisi formulir biodata calon santri dan orang tua secara lengkap di situs PPDB resmi.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-800/40 p-4 space-y-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold text-xs">2</div>
                <div class="font-bold text-xs text-slate-100">Konfirmasi & Berkas</div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Mendapatkan nomor registrasi dan menyerahkan salinan akta, KK, dan bukti pembayaran formulir.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-800/40 p-4 space-y-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold text-xs">3</div>
                <div class="font-bold text-xs text-slate-100">Tes Seleksi & Wawancara</div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Mengikuti observasi baca Al-Qur'an, potensi akademik dasar, serta wawancara komitmen wali.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-800/40 p-4 space-y-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white font-bold text-xs">4</div>
                <div class="font-bold text-xs text-slate-100">Pengumuman & Daftar Ulang</div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Pengumuman kelulusan online, penyelesaian administrasi daftar ulang, dan penempatan asrama.
                </p>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL CEK STATUS PENDAFTARAN ==================== --}}
    <x-modal name="lookup-modal" :show="$showLookupModal" max-width="lg">
        <div class="p-6 sm:p-7 space-y-5">
            <x-modal-header
                title="Cek Status Pendaftaran"
                subtitle="Lacak perkembangan formulir calon santri baru"
                icon="search"
            />

            {{-- Input Nomor Registrasi --}}
            <div class="space-y-3">
                <div>
                    <x-input-label for="lookup_number" value="Nomor Registrasi PPDB *" />
                    <div class="flex gap-2 mt-1">
                        <x-text-input
                            id="lookup_number"
                            wire:model.live.debounce.300ms="lookup_registration_number"
                            wire:keydown.enter="checkStatus"
                            type="text"
                            class="block w-full font-mono uppercase text-sm"
                            placeholder="Contoh: REG-2026-0001"
                        />
                        <x-primary-button
                            type="button"
                            wire:click="checkStatus"
                            class="shrink-0"
                        >
                            <i data-lucide="search" class="w-3.5 h-3.5 mr-1.5"></i>
                            <span>Periksa</span>
                        </x-primary-button>
                    </div>
                </div>

                <div>
                    <x-input-label for="lookup_phone_last4" value="4 Digit Terakhir No. WhatsApp Wali *" />
                    <x-text-input
                        id="lookup_phone_last4"
                        wire:model="lookup_phone_last4"
                        wire:keydown.enter="checkStatus"
                        type="text"
                        inputmode="numeric"
                        maxlength="4"
                        class="block w-full font-mono text-sm mt-1"
                        placeholder="Contoh: 7890"
                    />
                    <x-input-error :messages="$errors->get('lookup_phone_last4')" class="mt-1" />
                    <p class="text-[11px] text-slate-400 mt-1">Nomor registrasi tertera pada bukti pendaftaran yang diunduh saat mendaftar.</p>
                </div>
            </div>

            {{-- Hasil Pencarian Jika Ditemukan --}}
            @if($lookupResult)
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs space-y-2.5">
                    <div class="flex items-center justify-between border-b border-emerald-500/20 pb-2">
                        <span class="font-bold text-emerald-300">Data Pendaftar Ditemukan</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ match($lookupResult->status) {
                            'accepted' => 'bg-emerald-500/30 text-emerald-300 border border-emerald-500/40',
                            'rejected' => 'bg-rose-500/30 text-rose-300 border border-rose-500/40',
                            'verified' => 'bg-sky-500/30 text-sky-300 border border-sky-500/40',
                            default => 'bg-amber-500/30 text-amber-300 border border-amber-500/40',
                        } }}">
                            {{ match($lookupResult->status) {
                                'accepted' => 'Diterima',
                                'rejected' => 'Tidak Lulus',
                                'verified' => 'Terverifikasi (Jadwal Tes)',
                                default => 'Menunggu Verifikasi',
                            } }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nama Santri:</span>
                        <span class="font-bold text-slate-100">{{ $lookupResult->student_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Gelombang:</span>
                        <span class="font-medium text-slate-200">{{ $lookupResult->batch->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Wali:</span>
                        <span class="font-medium text-slate-200">{{ $lookupResult->guardian_name }}</span>
                    </div>

                    <div class="pt-2">
                        <x-secondary-button
                            :href="\Illuminate\Support\Facades\URL::signedRoute('ppdb.print', $lookupResult->registration_number)"
                            target="_blank"
                            class="w-full justify-center text-emerald-400 border-emerald-500/40"
                        >
                            <i data-lucide="printer" class="w-3.5 h-3.5 mr-1.5"></i>
                            <span>Cetak Ulang Bukti Pendaftaran</span>
                        </x-secondary-button>
                    </div>
                </div>
            @elseif($lookupNotFound)
                <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs text-rose-300">
                    <div class="font-bold">Nomor Registrasi Tidak Ditemukan</div>
                    <div class="text-[11px] mt-1 text-rose-300/80">
                        Harap periksa kembali nomor registrasi yang Anda masukkan. Pastikan sesuai format (contoh: REG-2026-0001).
                    </div>
                </div>
            @endif

            <x-modal-actions>
                <x-secondary-button wire:click="closeLookupModal">
                    Tutup
                </x-secondary-button>
            </x-modal-actions>
        </div>
    </x-modal>
</div>
