import { bindLiveValidation, validateForm, validateInput } from './utils/validate';

const DEMO_CONTACT = 'demo@routeindonesia.id';
const KIND_LABELS = { booking: 'kode booking', order: 'ID pesanan' };

/**
 * Pratinjau tanpa request: kode demo menampilkan hasil contoh, kode lain "tidak ditemukan".
 * Lookup ke server (POST + rate limit) dibuat di fase berikutnya.
 */
export function initOrderCheck(root) {
    const tabs = [...root.querySelectorAll('[data-check-tab]')];
    const forms = [...root.querySelectorAll('[data-order-check-form]')];
    if (tabs.length === 0 || forms.length === 0) {
        return;
    }

    const results = [...root.querySelectorAll('[data-result]')];
    const status = root.querySelector('[data-check-status]');
    const hideResults = () => results.forEach((result) => {
        result.hidden = true;
    });

    const selectTab = (tab) => {
        tabs.forEach((item) => {
            const isActive = item === tab;
            item.setAttribute('aria-selected', String(isActive));
            item.tabIndex = isActive ? 0 : -1;
            document.getElementById(item.getAttribute('aria-controls')).hidden = !isActive;
        });
        hideResults();
        status.textContent = '';
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        // Arrow/Home/End roving focus per WAI-ARIA tabs pattern.
        tab.addEventListener('keydown', (event) => {
            const targets = { ArrowRight: index + 1, ArrowLeft: index - 1, Home: 0, End: tabs.length - 1 };
            if (!(event.key in targets)) {
                return;
            }
            event.preventDefault();
            const next = tabs[(targets[event.key] + tabs.length) % tabs.length];
            selectTab(next);
            next.focus();
        });
    });

    const showResult = (name, form, code) => {
        hideResults();
        const panel = root.querySelector(`[data-result="${name}"]`);
        if (name === 'not-found') {
            panel.querySelector('[data-not-found-label]').textContent = KIND_LABELS[form.dataset.kind];
            panel.querySelector('[data-not-found-code]').textContent = code;
            panel.querySelector('[data-not-found-demo]').textContent = form.dataset.demoCode;
        }
        panel.hidden = false;
        status.textContent = name === 'not-found' ? 'Pesanan tidak ditemukan.' : `Pesanan ${code} ditemukan.`;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        panel.focus({ preventScroll: true });
        panel.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
    };

    forms.forEach((form) => {
        const codeInput = form.querySelector('[data-order-code]');
        const contactInput = form.querySelector('[data-order-contact]');

        codeInput.addEventListener('input', () => {
            codeInput.value = codeInput.value.toUpperCase().replace(/\s/g, '');
        });
        bindLiveValidation(form);

        form.querySelector('[data-fill-demo]').addEventListener('click', () => {
            codeInput.value = form.dataset.demoCode;
            contactInput.value = DEMO_CONTACT;
            [codeInput, contactInput].forEach(validateInput);
            form.querySelector('[type="submit"]').focus();
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!validateForm(form)) {
                return;
            }
            const code = codeInput.value.trim();
            showResult(code === form.dataset.demoCode ? form.dataset.kind : 'not-found', form, code);
        });
    });
}
