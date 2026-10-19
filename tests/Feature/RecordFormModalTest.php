<?php

use Illuminate\Support\Facades\Blade;

test('record form modal renders the actions slot as a footer', function () {
    $html = Blade::render(<<<'BLADE'
        <x-record-form-modal name="uji" title="Uji Modal">
            <p>Isi</p>
            <x-slot:actions><button type="submit">Simpan Uji</button></x-slot:actions>
        </x-record-form-modal>
    BLADE);

    expect($html)->toContain('Simpan Uji');
});

test('every record form modal in the views has a way to submit', function () {
    $modals = collect(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/livewire'))))
        ->filter(fn (SplFileInfo $file) => str_ends_with($file->getFilename(), '.blade.php'))
        ->map(fn (SplFileInfo $file) => $file->getPathname())
        ->flatMap(function (string $path): array {
            preg_match_all('/<x-record-form-modal\b.*?<\/x-record-form-modal>/s', file_get_contents($path), $matches);

            return collect($matches[0])->map(fn (string $block) => [$path, $block])->all();
        });

    expect($modals)->not->toBeEmpty();

    $modals->each(fn (array $modal) => expect(str_contains($modal[1], '<x-slot:actions>') || str_contains($modal[1], 'type="submit"'))
        ->toBeTrue('Modal tanpa tombol aksi di '.basename($modal[0])));
});
