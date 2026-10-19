// Form di modal/halaman panjang: tanpa ini pesan error muncul di luar layar dan terasa seperti tombol simpan tidak bekerja.
document.addEventListener('livewire:init', () => {
    const submitted = new WeakSet();

    Livewire.hook('commit', ({ component, commit, succeed }) => {
        if (!commit.calls?.length) {
            return;
        }

        succeed(() => submitted.add(component));
    });

    Livewire.hook('morphed', ({ component }) => {
        if (!submitted.has(component)) {
            return;
        }
        submitted.delete(component);

        requestAnimationFrame(() => {
            const firstError = [...component.el.querySelectorAll('[data-input-error]')]
                .find((el) => el.getClientRects().length > 0);

            if (!firstError) {
                return;
            }

            firstError.scrollIntoView({ block: 'center', behavior: 'smooth' });
            const field = firstError.parentElement?.querySelector('input:not([type=hidden]), select, textarea, [tabindex]');
            field?.focus({ preventScroll: true });
        });
    });
});
