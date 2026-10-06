const TOAST_DURATION_MS = 2600;

export function showToast(message) {
    const region = document.querySelector('[data-toast-region]');
    if (!region) {
        return;
    }

    const toast = document.createElement('p');
    toast.className = 'pointer-events-auto rounded-md bg-ink px-4 py-2.5 text-sm font-medium text-canvas shadow-lg';
    toast.textContent = message;
    region.append(toast);
    setTimeout(() => toast.remove(), TOAST_DURATION_MS);
}
