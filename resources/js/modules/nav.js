export function initNav(root) {
    const toggle = root.querySelector('[data-nav-toggle]');
    const menu = document.getElementById(toggle?.getAttribute('aria-controls'));
    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!isOpen));
        menu.hidden = isOpen;
        root.querySelector('[data-nav-icon="open"]')?.classList.toggle('hidden', !isOpen);
        root.querySelector('[data-nav-icon="close"]')?.classList.toggle('hidden', isOpen);
    });
}
