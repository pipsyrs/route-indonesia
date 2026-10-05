import { SESSION_KEY } from './cart';
import { readLocal } from './utils/storage';

/** Tukar link Masuk ↔ Akun berdasarkan sesi dummy (bukan autentikasi). */
export function initAccountNav(root) {
    const render = () => {
        const session = readLocal(SESSION_KEY);
        const isUser = typeof session?.name === 'string' && session.name.trim() !== '';
        root.querySelector('[data-session="guest"]').hidden = isUser;
        root.querySelector('[data-session="user"]').hidden = !isUser;
        if (isUser) {
            root.querySelector('[data-session-initial]').textContent = session.name.trim().charAt(0).toUpperCase() || 'A';
        }
    };

    window.addEventListener('storage', (event) => {
        if (event.key === SESSION_KEY || event.key === null) {
            render();
        }
    });
    render();
}
