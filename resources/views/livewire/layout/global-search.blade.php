<?php

use App\Models\User;
use App\Support\Features;
use App\Support\Navigation;
use Livewire\Volt\Component;

new class extends Component
{
    public string $query = '';
    public bool $open = false;
    public array $results = [];
    public array $flatResults = [];
    public int $selectedIndex = -1;

    public function updatedQuery(): void
    {
        $this->selectedIndex = -1;
        if (strlen(trim($this->query)) < 2) {
            $this->results = [];
            $this->flatResults = [];
            return;
        }
        $this->search();
    }

    public function search(): void
    {
        $q = trim($this->query);
        $groups = [];
        $flat = [];

        $user = auth()->user();

        /**
         * Tiap bagian = satu model yang dicari. Modul baru cukup menambah entri di sini:
         * fields (kolom yang dicari), url (tujuan), sub (teks kecil di bawah label), flags (badge).
         */
        $sections = [];

        if ($user->can('view-master-data')) {
            $sections[] = [
                'model'  => \App\Models\Student::class,
                'fields' => ['name', 'nis', 'nisn', 'nickname'],
                'group'  => 'Siswa / Santri',
                'icon'   => 'graduation-cap',
                'color'  => 'emerald',
                'url'    => fn($r) => route('master-data.students.show', $r->id),
                'sub'    => fn($r) => collect(['NIS: '.$r->nis, $r->currentClass()?->name, $r->guardian?->name])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with(['currentEnrollment.schoolClass', 'guardian'])->orderBy('name'),
                'flags'  => fn($r) => [['label' => $r->statusLabel(), 'color' => $r->statusBadgeColor()]],
            ];

            $sections[] = [
                'model'  => \App\Models\Teacher::class,
                'fields' => ['name', 'nip', 'phone', 'specialization'],
                'group'  => 'Guru & Staf',
                'icon'   => 'briefcase',
                'color'  => 'sky',
                'url'    => fn($r) => route('master-data.teachers'),
                'sub'    => fn($r) => collect([$r->specialization, $r->phone])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->orderBy('name'),
                'flags'  => fn($r) => $r->is_active ? [] : [['label' => 'Nonaktif', 'color' => 'slate']],
            ];

            $sections[] = [
                'model'  => \App\Models\SchoolClass::class,
                'fields' => ['name'],
                'group'  => 'Kelas & Rombel',
                'icon'   => 'layout-grid',
                'color'  => 'indigo',
                'url'    => fn($r) => route('master-data.classes', ['search' => $r->name]),
                'sub'    => fn($r) => collect(['Tingkat '.$r->grade_level, 'Wali: '.($r->homeroomTeacher?->name ?? 'Belum ada')])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with('homeroomTeacher')->orderBy('name'),
                'flags'  => fn($r) => [],
            ];
        }

        if ($user->can('view-finance')) {
            $sections[] = [
                'model'  => \App\Models\PaymentTransaction::class,
                'fields' => ['receipt_number', 'notes'],
                'group'  => 'Kuitansi Pembayaran',
                'icon'   => 'receipt',
                'color'  => 'emerald',
                'url'    => fn($r) => route('finance.receipts.print', $r->id),
                'sub'    => fn($r) => collect([$r->student?->name, 'Rp '.number_format((float) $r->total_amount, 0, ',', '.'), $r->paid_at?->format('d/m/Y H:i')])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with('student')->orderByDesc('paid_at'),
                'flags'  => fn($r) => [['label' => $r->payment_method_label, 'color' => $r->payment_method === 'cash' ? 'emerald' : 'sky']],
            ];
        }

        if ($user->can('view-boarding')) {
            $sections[] = [
                'model'  => \App\Models\StudentLeaveRequest::class,
                'fields' => ['leave_number', 'reason', 'pickup_person'],
                'group'  => 'Izin Santri',
                'icon'   => 'file-text',
                'color'  => 'amber',
                'url'    => fn($r) => route('boarding.leaves', ['search' => $r->leave_number]),
                'sub'    => fn($r) => collect([$r->student?->name, $r->leaveTypeLabel(), $r->departure_plan?->format('d/m/Y')])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with('student')->latest(),
                'flags'  => fn($r) => [['label' => $r->statusLabel(), 'color' => $r->statusBadgeColor()]],
            ];
        }

        if ($user->can('view-admissions')) {
            $sections[] = [
                'model'  => \App\Models\AdmissionCandidate::class,
                'fields' => ['student_name', 'registration_number', 'guardian_name', 'guardian_phone'],
                'group'  => 'Calon Santri (PPDB)',
                'icon'   => 'user-plus',
                'color'  => 'teal',
                'url'    => fn($r) => route('admissions.candidates', ['search' => $r->registration_number]),
                'sub'    => fn($r) => collect([$r->registration_number, $r->batch?->name, 'Wali: '.$r->guardian_name])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with('batch')->latest(),
                'flags'  => fn($r) => [['label' => $r->statusLabel(), 'color' => $r->statusBadgeColor()]],
            ];
        }

        if ($user->can('view-inventory')) {
            $sections[] = [
                'model'  => \App\Models\InventoryItem::class,
                'fields' => ['name', 'code', 'brand', 'qr_code_token'],
                'group'  => 'Inventaris Aset',
                'icon'   => 'package',
                'color'  => 'indigo',
                'url'    => fn($r) => route('inventory.items', ['search' => $r->code]),
                'sub'    => fn($r) => collect([$r->code, $r->room?->name, 'Kondisi: '.ucfirst($r->condition)])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with('room')->latest(),
                'flags'  => fn($r) => [['label' => ucfirst($r->condition), 'color' => $r->condition === 'baik' ? 'emerald' : 'amber']],
            ];
        }

        if ($user->can('view-library')) {
            $sections[] = [
                'model'  => \App\Models\LibraryBook::class,
                'fields' => ['title', 'code', 'author', 'isbn'],
                'group'  => 'Buku Perpustakaan',
                'icon'   => 'book-open',
                'color'  => 'sky',
                'label'  => fn($r) => $r->title,
                'url'    => fn($r) => route('library.books', ['search' => $r->title]),
                'sub'    => fn($r) => collect([$r->code, $r->author, 'Tersedia: '.$r->available_copies.'/'.$r->total_copies])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->latest(),
                'flags'  => fn($r) => [['label' => $r->available_copies > 0 ? 'Tersedia' : 'Habis', 'color' => $r->available_copies > 0 ? 'emerald' : 'rose']],
            ];
        }

        if ($user->can('manage-cashless')) {
            $sections[] = [
                'model'  => \App\Models\RfidCard::class,
                'fields' => ['card_uid', 'label'],
                'group'  => 'Kartu RFID Santri',
                'icon'   => 'credit-card',
                'color'  => 'amber',
                'label'  => fn($r) => 'Kartu: '.$r->card_uid.' ('.$r->student?->name.')',
                'url'    => fn($r) => route('cashless.cards', ['search' => $r->card_uid]),
                'sub'    => fn($r) => collect([$r->student?->nis, 'Saldo: Rp '.number_format((float) ($r->student?->wallet?->balance ?? 0), 0, ',', '.')])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with(['student.wallet'])->latest(),
                'flags'  => fn($r) => [['label' => $r->is_active ? 'Aktif' : 'Terblokir', 'color' => $r->is_active ? 'emerald' : 'rose']],
            ];
        }

        if ($user->can('manageUserRoles', \Spatie\Permission\Models\Role::class)) {
            $sections[] = [
                'model'  => User::class,
                'fields' => ['name', 'username', 'email', 'phone'],
                'group'  => 'Pengguna',
                'icon'   => 'user-cog',
                'color'  => 'violet',
                'url'    => fn($r) => route('settings.roles-and-permissions', ['tab' => 'users', 'cari' => $r->username ?? $r->email]),
                'sub'    => fn($r) => collect([$r->username ? '@'.$r->username : null, $r->email])->filter()->implode(' · '),
                'scope'  => fn($qb) => $qb->with('roles:id,name')->orderBy('name'),
                'flags'  => fn($r) => $r->roles->map(fn ($role) => ['label' => \Illuminate\Support\Str::headline($role->name), 'color' => 'slate'])->all(),
            ];
        }

        $menuResults = $this->searchMenus($q);
        if ($menuResults !== []) {
            $groups[] = ['group' => 'Menu', 'icon' => 'layout-grid', 'color' => 'slate', 'items' => $menuResults];
            array_push($flat, ...$menuResults);
        }

        foreach ($sections as $sec) {
            $modelClass = $sec['model'];
            $table = (new $modelClass)->getTable();
            $qb = $modelClass::query()->select("{$table}.*");
            if (isset($sec['queryFilter']) && is_callable($sec['queryFilter'])) {
                ($sec['queryFilter'])($qb, $q);
            } else {
                $fields = $sec['fields'];
                $qb->where(function ($query) use ($fields, $q, $table) {
                    foreach ($fields as $i => $field) {
                        $method = $i === 0 ? 'where' : 'orWhere';
                        $query->$method("{$table}.{$field}", 'like', "%{$q}%");
                    }
                });
            }
            ($sec['scope'])($qb);
            $rows = $qb->limit(4)->get();

            if ($rows->isEmpty()) continue;

            $group = [
                'group' => $sec['group'],
                'icon'  => $sec['icon'],
                'color' => $sec['color'],
                'items' => [],
            ];
            foreach ($rows as $row) {
                $label = isset($sec['label']) && is_callable($sec['label'])
                    ? ($sec['label'])($row)
                    : $row->name;
                $url = ($sec['url'])($row);
                if (! Features::allowsUrl($url)) {
                    continue;
                }
                $entry = [
                    'label' => $label,
                    'sub'   => ($sec['sub'])($row),
                    'url'   => $url,
                    'icon'  => $sec['icon'],
                    'color' => $sec['color'],
                    'flags' => ($sec['flags'])($row),
                ];
                $group['items'][] = $entry;
                $flat[] = $entry;
            }
            if ($group['items'] !== []) {
                $groups[] = $group;
            }
        }

        $this->results = $groups;
        $this->flatResults = $flat;
    }

    /**
     * Escape teks lalu tebalkan bagian yang cocok dengan kata kunci (case-insensitive).
     */
    public function highlight(?string $text): string
    {
        $text = (string) $text;
        $keyword = trim($this->query);

        if ($keyword === '' || $text === '') {
            return e($text);
        }

        $parts = preg_split('/('.preg_quote($keyword, '/').')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts)
            ->map(fn (string $part, int $i) => $i % 2 === 1
                ? '<mark class="bg-transparent font-semibold text-slate-50">'.e($part).'</mark>'
                : e($part))
            ->implode('');
    }

    /**
     * Daftar menu yang bisa dicari: tautan sidebar yang lolos izin (App\Support\Navigation).
     *
     * @return list<array{label: string, section: string, icon: string, route: string, keywords: string}>
     */
    protected function menus(): array
    {
        $menus = collect(Navigation::linksForUser(auth()->user()))
            ->map(fn (array $link) => [
                'label' => $link['label'],
                'section' => $link['section'],
                'icon' => $link['icon'],
                'route' => $link['route'],
                'keywords' => $link['keywords'] ?? '',
            ])
            ->all();

        $menus[] = ['label' => 'Profil Saya', 'section' => 'Akun', 'icon' => 'user-round', 'route' => 'profile', 'keywords' => 'akun password kata sandi email'];

        return $menus;
    }

    /**
     * Menu cocok bila setiap kata di kata kunci muncul di label, grup, atau kata kunci menu,
     * jadi "lap penj" tetap menemukan "Laporan Penjualan".
     *
     * @return list<array{label: string, sub: string, url: string, icon: string, color: string, flags: array}>
     */
    protected function searchMenus(string $q): array
    {
        $words = preg_split('/\s+/', mb_strtolower($q));

        return collect($this->menus())
            ->filter(function (array $menu) use ($words) {
                if (! Features::allowsRoute($menu['route'])) {
                    return false;
                }

                $haystack = mb_strtolower("{$menu['label']} {$menu['section']} {$menu['keywords']}");

                return collect($words)->every(fn (string $word) => str_contains($haystack, $word));
            })
            ->take(5)
            ->map(fn (array $menu) => [
                'label' => $menu['label'],
                'sub'   => $menu['section'],
                'url'   => route($menu['route']),
                'icon'  => $menu['icon'],
                'color' => 'slate',
                'flags' => [],
            ])
            ->values()
            ->all();
    }

    public function openSearch(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->query = '';
        $this->results = [];
        $this->flatResults = [];
        $this->selectedIndex = -1;
    }
}; ?>

<div @keydown.window.cmd.k.prevent="$wire.openSearch()" @keydown.window.ctrl.k.prevent="$wire.openSearch()" class="contents">
    {{-- Trigger button desktop --}}
    <button
        wire:click="openSearch"
        type="button"
        class="hidden sm:inline-flex items-center gap-2 h-8 pl-2.5 pr-3 bg-slate-100 dark:bg-slate-800/60 hover:bg-slate-200 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600 rounded-lg text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition group w-full max-w-sm lg:max-w-md shadow-xs dark:shadow-none"
        title="Cari data (⌘K)"
    >
        <i data-lucide="search" class="w-3.5 h-3.5 shrink-0 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition"></i>
        <span class="flex-1 text-left">Cari data…</span>
        <kbd class="hidden lg:inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-white dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600/60 rounded text-[9px] font-mono text-slate-500">⌘K</kbd>
    </button>

    {{-- Trigger button mobile --}}
    <x-icon-button icon="search" label="Buka pencarian" wire:click="openSearch" class="sm:hidden" />

    {{-- Command Palette Modal (teleported to body) --}}
    @if ($open)
    @teleport('body')
<div
    x-data="{
        selectedIndex: -1,
        get flatCount() { return document.querySelectorAll('[data-result-item]').length; },
        moveDown() { const n = this.flatCount; if(!n) return; this.selectedIndex = (this.selectedIndex+1)%n; },
        moveUp()   { const n = this.flatCount; if(!n) return; this.selectedIndex = this.selectedIndex<=0 ? n-1 : this.selectedIndex-1; },
        go()       { const el = document.querySelectorAll('[data-result-item]')[this.selectedIndex]; if(el) el.click(); },
    }"
    x-init="$nextTick(() => $refs.searchModal?.focus())"
    @keydown.escape.window="$wire.close()"
    @keydown.arrow-down.window.prevent="moveDown()"
    @keydown.arrow-up.window.prevent="moveUp()"
    @keydown.enter.window.prevent="go()"
    class="fixed inset-0 z-[60] flex items-start justify-center pt-[calc(0.75rem+env(safe-area-inset-top))] px-3 sm:pt-[10vh] sm:px-4"
    wire:key="global-search-modal"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"
        wire:click="close"
    ></div>

    {{-- Dialog --}}
    <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl shadow-slate-950/20 dark:shadow-slate-950/90 overflow-hidden z-10">
        {{-- Search Input --}}
        <div class="flex items-center gap-3 px-4 border-b border-slate-100 dark:border-slate-800/80">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
            <input
                x-ref="searchModal"
                wire:model.live.debounce.250ms="query"
                type="text"
                placeholder="Cari santri, NIS, kelas, kartu RFID, menu…"
                autocomplete="off"
                spellcheck="false"
                class="input-bare flex-1 h-12 p-0 bg-transparent border-0 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 outline-none focus:outline-none focus:ring-0"
            >
            <div wire:loading wire:target="query" class="shrink-0">
                <x-spinner class="text-slate-400" />
            </div>
            <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[10px] font-mono text-slate-500 shrink-0">Esc</kbd>
            <button type="button" wire:click="close" class="sm:hidden -me-2 px-2 h-11 text-xs font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 shrink-0">Tutup</button>
        </div>

        {{-- Results --}}
        <div class="max-h-[min(420px,60dvh)] overflow-y-auto overscroll-contain custom-scrollbar">
            @if (strlen(trim($query)) < 2)
                {{-- Hint state --}}
                <div class="px-4 py-8 text-center">
                    <i data-lucide="search" class="w-8 h-8 text-slate-300 dark:text-slate-700 mx-auto mb-3"></i>
                    <p class="text-xs text-slate-500">Ketik minimal 2 karakter untuk mulai mencari</p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-600 mt-1">Santri · Kelas · RFID · Menu · Pengguna</p>
                </div>
            @elseif (empty($results))
                <div class="px-4 py-8 text-center">
                    <i data-lucide="search-x" class="w-8 h-8 text-slate-300 dark:text-slate-700 mx-auto mb-3"></i>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada hasil untuk <span class="text-slate-800 dark:text-slate-300 font-medium">"{{ $query }}"</span></p>
                </div>
            @else
                @php
                    $colorMap = [
                        'emerald' => ['text' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ring' => 'ring-emerald-200 dark:ring-emerald-500/20'],
                        'sky'     => ['text' => 'text-sky-600 dark:text-sky-400',     'bg' => 'bg-sky-50 dark:bg-sky-500/10',     'ring' => 'ring-sky-200 dark:ring-sky-500/20'],
                        'amber'   => ['text' => 'text-amber-600 dark:text-amber-400',   'bg' => 'bg-amber-50 dark:bg-amber-500/10',   'ring' => 'ring-amber-200 dark:ring-amber-500/20'],
                        'violet'  => ['text' => 'text-violet-600 dark:text-violet-400',  'bg' => 'bg-violet-50 dark:bg-violet-500/10',  'ring' => 'ring-violet-200 dark:ring-violet-500/20'],
                        'orange'  => ['text' => 'text-orange-600 dark:text-orange-400',  'bg' => 'bg-orange-50 dark:bg-orange-500/10',  'ring' => 'ring-orange-200 dark:ring-orange-500/20'],
                        'teal'    => ['text' => 'text-teal-600 dark:text-teal-400',    'bg' => 'bg-teal-50 dark:bg-teal-500/10',    'ring' => 'ring-teal-200 dark:ring-teal-500/20'],
                        'rose'    => ['text' => 'text-rose-600 dark:text-rose-400',    'bg' => 'bg-rose-50 dark:bg-rose-500/10',    'ring' => 'ring-rose-200 dark:ring-rose-500/20'],
                        'indigo'  => ['text' => 'text-indigo-600 dark:text-indigo-400',  'bg' => 'bg-indigo-50 dark:bg-indigo-500/10',  'ring' => 'ring-indigo-200 dark:ring-indigo-500/20'],
                        'slate'   => ['text' => 'text-slate-600 dark:text-slate-400',   'bg' => 'bg-slate-100 dark:bg-slate-800',      'ring' => 'ring-slate-200 dark:ring-slate-700'],
                    ];
                    $flatIdx = 0;
                @endphp
                <div class="py-2">
                    @foreach ($results as $group)
                        <div class="px-3 pt-3 pb-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest {{ $colorMap[$group['color']]['text'] ?? 'text-slate-500' }}">
                                {{ $group['group'] }}
                            </span>
                        </div>
                        @foreach ($group['items'] as $item)
                            @php $currentIdx = $flatIdx++; $c = $colorMap[$item['color']] ?? []; @endphp
                            <a
                                href="{{ $item['url'] }}"
                                wire:navigate
                                data-result-item
                                data-idx="{{ $currentIdx }}"
                                wire:click="close"
                                :class="selectedIndex === {{ $currentIdx }} ? 'bg-slate-100 dark:bg-slate-800' : ''"
                                class="flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group cursor-pointer"
                                x-on:mouseenter="selectedIndex = {{ $currentIdx }}"
                            >
                                <div class="w-8 h-8 rounded-lg {{ $c['bg'] ?? 'bg-slate-100 dark:bg-slate-800' }} ring-1 {{ $c['ring'] ?? 'ring-slate-200 dark:ring-slate-700' }} flex items-center justify-center shrink-0">
                                    <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 {{ $c['text'] ?? 'text-slate-400' }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-300 truncate">{!! $this->highlight($item['label']) !!}</p>
                                    @if ($item['sub'])
                                        <p class="text-xs text-slate-500 truncate">{!! $this->highlight($item['sub']) !!}</p>
                                    @endif
                                    @if (! empty($item['flags']))
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                             @foreach ($item['flags'] as $flag)
                                                @php $fc = $colorMap[$flag['color']] ?? $colorMap['slate']; @endphp
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium ring-1 {{ $fc['bg'] }} {{ $fc['ring'] }} {{ $fc['text'] }}">{{ $flag['label'] }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600 group-hover:text-slate-600 dark:group-hover:text-slate-400 transition shrink-0"></i>
                            </a>
                        @endforeach
                        @if (!$loop->last)
                            <div class="border-t border-slate-100 dark:border-slate-800/40 mx-4 my-1"></div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="border-t border-slate-100 dark:border-slate-800/60 px-4 py-2.5 flex items-center gap-4 text-[10px] text-slate-500 dark:text-slate-600">
            {{-- Petunjuk keyboard hanya relevan di perangkat berkeyboard --}}
            <span class="hidden sm:flex items-center gap-1.5"><kbd class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[9px] font-mono">↑↓</kbd> navigasi</span>
            <span class="hidden sm:flex items-center gap-1.5"><kbd class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[9px] font-mono">↵</kbd> buka</span>
            <span class="hidden sm:flex items-center gap-1.5"><kbd class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[9px] font-mono">Esc</kbd> tutup</span>
            <span class="ml-auto">{{ collect($results)->sum(fn($g) => count($g['items'])) }} hasil</span>
        </div>
    </div>
    </div>
    @endteleport
    @endif
</div>
