<x-layouts.print :title="$title ?? 'Label Stiker Aset Inventaris'">
    <div class="mx-auto max-w-4xl p-4">
        <div class="mb-4 flex items-center justify-between no-print">
            <div>
                <h1 class="text-base font-bold text-slate-900">{{ $title ?? 'Cetak Label Aset Inventaris' }}</h1>
                <p class="text-xs text-slate-500">Gunakan kertas stiker label atau kertas HVS standar untuk ditempel pada aset fisik sekolah/pesantren.</p>
            </div>
            <button onclick="window.print()" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">
                Cetak Label
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @forelse ($items as $item)
                @php /** @var \App\Models\InventoryItem $item */ @endphp
                <div class="rounded-lg border-2 border-dashed border-slate-400 p-3 bg-white text-slate-900 flex flex-col justify-between" style="page-break-inside: avoid; min-height: 140px;">
                    <div class="flex items-start justify-between gap-2 border-b border-slate-200 pb-1.5 mb-1.5">
                        <div class="leading-tight">
                            <span class="block text-[9px] font-bold uppercase tracking-wider text-emerald-800">INVENTARIS SEKOLAH</span>
                            <span class="font-mono text-xs font-bold text-slate-900">{{ $item->code }}</span>
                        </div>
                        <span class="rounded bg-slate-100 px-1 py-0.5 text-[9px] font-medium text-slate-600 uppercase">{{ $item->room?->name ?? 'Gudang' }}</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div role="img" aria-label="QR Aset {{ $item->code }}" class="h-16 w-16 shrink-0 rounded border border-slate-300 p-0.5 [&>svg]:h-full [&>svg]:w-full">{!! \App\Support\QrCodeSvg::render($item->qr_code_token) !!}</div>
                        <div class="min-w-0 text-[10px] space-y-0.5">
                            <p class="font-bold text-slate-900 line-clamp-2 text-[11px] leading-snug">{{ $item->name }}</p>
                            @if($item->brand)
                                <p class="text-slate-600">Merk: <span class="font-medium">{{ $item->brand }}</span></p>
                            @endif
                            <p class="text-slate-500">Kondisi: <span class="font-semibold text-slate-700 uppercase">{{ $item->condition }}</span></p>
                            <p class="font-mono text-[8px] text-slate-400 truncate">{{ $item->qr_code_token }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-500">
                    Tidak ada barang aset inventaris untuk dicetak.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.print>
