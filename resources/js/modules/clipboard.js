import { showToast } from './toast';

/** Tombol [data-copy] di mana pun (kode promo, kode booking). */
export function initClipboard() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');
        if (!button) {
            return;
        }

        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            showToast(button.dataset.copyMessage || 'Disalin');
        } catch {
            showToast('Gagal menyalin, salin manual ya.');
        }
    });
}
