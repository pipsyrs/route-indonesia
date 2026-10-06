/**
 * Modal generik di atas <dialog> native: showModal() memberi focus trap, Esc, dan
 * inert ke halaman. Modul ini menambah tutup lewat backdrop, kunci scroll, dan
 * mengembalikan fokus ke pemicu.
 */
const triggers = new WeakMap();

export function initModals() {
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');
        if (opener) {
            openModal(opener.dataset.modalOpen, opener);
            return;
        }
        event.target.closest('[data-modal-close]')?.closest('dialog')?.close();
    });
}

export function openModal(id, trigger = document.activeElement) {
    const dialog = document.getElementById(id);
    if (!(dialog instanceof HTMLDialogElement) || dialog.open) {
        return;
    }

    if (!dialog.dataset.modalBound) {
        dialog.dataset.modalBound = '';
        // Klik tepat di <dialog> (bukan isinya) = klik backdrop.
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
        dialog.addEventListener('close', () => {
            document.documentElement.classList.remove('overflow-hidden');
            triggers.get(dialog)?.focus();
        });
    }

    triggers.set(dialog, trigger);
    // Modul lain bisa mengisi konten sebelum modal tampil.
    dialog.dispatchEvent(new CustomEvent('modal:open', { detail: { trigger } }));
    document.documentElement.classList.add('overflow-hidden');
    dialog.showModal();
}
