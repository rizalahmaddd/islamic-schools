<?php

namespace App\Livewire\Library;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\LibraryBook;
use App\Services\DocumentNumberGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Katalog Buku & Kitab Perpustakaan'])]
#[Title('Katalog Buku & Kitab')]
class BookIndex extends Component
{
    use WithCrudActions;

    public string $code = '';

    public ?string $isbn = null;

    public string $title = '';

    public ?string $author = null;

    public ?string $publisher = null;

    public ?int $publish_year = null;

    public string $category = 'umum';

    public ?string $call_number = null;

    public ?string $rack_location = null;

    public int $total_copies = 1;

    // Filters
    public string $categoryFilter = '';

    public string $availabilityFilter = '';

    public function canManage(): bool
    {
        return auth()->user()?->can('manage-library') ?? false;
    }

    protected function modelClass(): string
    {
        return LibraryBook::class;
    }

    protected function resetForm(): void
    {
        $this->code = '';
        $this->isbn = null;
        $this->title = '';
        $this->author = null;
        $this->publisher = null;
        $this->publish_year = (int) now()->year;
        $this->category = 'umum';
        $this->call_number = null;
        $this->rack_location = null;
        $this->total_copies = 1;
    }

    /**
     * @param  LibraryBook  $record
     */
    protected function fillForm($record): void
    {
        $this->code = $record->code;
        $this->isbn = $record->isbn;
        $this->title = $record->title;
        $this->author = $record->author;
        $this->publisher = $record->publisher;
        $this->publish_year = $record->publish_year;
        $this->category = $record->category;
        $this->call_number = $record->call_number;
        $this->rack_location = $record->rack_location;
        $this->total_copies = $record->total_copies;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50', Rule::unique('library_books', 'code')->ignore($this->editingId)],
            'isbn' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publish_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'category' => ['required', 'string', 'in:umum,kitab_kuning,pelajaran,tahfidz,kamus,fiksi,lainnya'],
            'call_number' => ['nullable', 'string', 'max:100'],
            'rack_location' => ['nullable', 'string', 'max:100'],
            'total_copies' => ['required', 'integer', 'min:1'],
        ];
    }

    public function save(DocumentNumberGenerator $docGenerator): void
    {
        $this->authorizeManage();
        $validated = $this->validate();

        if ($this->editingId) {
            $book = LibraryBook::findOrFail($this->editingId);
            $diff = $validated['total_copies'] - $book->total_copies;
            $newAvailable = max(0, $book->available_copies + $diff);

            $book->update(array_merge($validated, [
                'available_copies' => $newAvailable,
            ]));
            $this->notify(__('Katalog buku perpustakaan berhasil diperbarui.'));
        } else {
            $code = $validated['code'] ?: $docGenerator->next(prefix: 'BK', pad: 4);
            $barcodeToken = 'LIB-'.Str::upper(Str::random(10));

            LibraryBook::create(array_merge($validated, [
                'code' => $code,
                'available_copies' => $validated['total_copies'],
                'barcode_token' => $barcodeToken,
            ]));
            $this->notify(__('Buku/Kitab baru berhasil ditambahkan ke katalog perpustakaan.'));
        }

        $this->closeModal();
    }

    protected function guardDelete($record): ?string
    {
        if ($record->activeLoans()->exists()) {
            return __('Buku ini sedang dalam status dipinjam oleh santri/guru dan belum dikembalikan.');
        }

        return null;
    }

    public function render(): View
    {
        $books = LibraryBook::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('author', 'like', "%{$this->search}%")
                        ->orWhere('isbn', 'like', "%{$this->search}%")
                        ->orWhere('rack_location', 'like', "%{$this->search}%");
                });
            })
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->availabilityFilter === 'available', fn ($q) => $q->where('available_copies', '>', 0))
            ->when($this->availabilityFilter === 'borrowed', fn ($q) => $q->where('available_copies', '<=', 0))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.library.book-index', [
            'books' => $books,
        ]);
    }
}
