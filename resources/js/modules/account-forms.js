import { SESSION_KEY } from './cart';
import { showToast } from './toast';
import { readLocal, removeLocal, writeLocal } from './utils/storage';
import { bindLiveValidation, validateForm } from './utils/validate';

/** Sesi dummy di localStorage: hanya untuk tampilan navbar, bukan autentikasi. */
const HANDLERS = {
    login: (form) => {
        const email = field(form, 'email').toLowerCase();
        saveSession(email.split('@')[0] || 'Pengguna', email);
        location.assign(form.dataset.redirect);
    },
    register: (form) => {
        saveSession(field(form, 'name'), field(form, 'email').toLowerCase());
        location.assign(form.dataset.redirect);
    },
    profile: (form) => {
        if (readLocal(SESSION_KEY)) {
            saveSession(field(form, 'name'), field(form, 'email').toLowerCase());
        }
        showToast('Profil disimpan (mode demo)');
    },
    password: (form) => {
        form.reset();
        showToast('Kata sandi diganti (mode demo)');
    },
};

export function initAccountForms(root) {
    root.querySelectorAll('form[data-form]').forEach((form) => {
        const handler = HANDLERS[form.dataset.form];
        if (!handler) {
            return;
        }
        bindLiveValidation(form);
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (validateForm(form)) {
                handler(form);
            }
        });
    });

    root.querySelectorAll('[data-action="logout"]').forEach((button) => {
        button.addEventListener('click', () => {
            removeLocal(SESSION_KEY);
            location.assign(button.dataset.redirect);
        });
    });
}

function field(form, name) {
    return form.querySelector(`[data-field="${name}"]`).value.trim();
}

function saveSession(name, email) {
    writeLocal(SESSION_KEY, { name, email, loggedInAt: new Date().toISOString() });
}
