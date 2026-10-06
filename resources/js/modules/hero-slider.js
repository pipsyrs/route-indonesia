const AUTOPLAY_MS = 6000;
const SWIPE_THRESHOLD_PX = 50;

export function initHeroSlider(root) {
    const viewport = root.querySelector('[data-slider-viewport]');
    const track = root.querySelector('[data-slider-track]');
    const slides = [...root.querySelectorAll('[data-slide]')];
    const dots = [...root.querySelectorAll('[data-slider-dot]')];
    const toggle = root.querySelector('[data-slider-toggle]');
    if (!viewport || !track || slides.length < 2) {
        return;
    }

    let index = 0;
    let timer = null;
    // Default jeda bila user minta gerak minimal; tombol tetap bisa memutar.
    let isPaused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let isHeld = false;

    const goTo = (next) => {
        index = (next + slides.length) % slides.length;
        track.style.transform = `translateX(-${index * 100}%)`;
        slides.forEach((slide, i) => {
            const isActive = i === index;
            slide.toggleAttribute('inert', !isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
        });
        dots.forEach((dot, i) => dot.setAttribute('aria-current', String(i === index)));
    };

    // Autoplay jalan hanya jika tidak dijeda user, tidak di-hover/fokus, dan tab terlihat.
    const sync = () => {
        clearInterval(timer);
        if (!isPaused && !isHeld && !document.hidden) {
            timer = setInterval(() => goTo(index + 1), AUTOPLAY_MS);
        }
    };
    const hold = (value) => {
        isHeld = value;
        sync();
    };

    const renderToggle = () => {
        toggle.querySelector('[data-slider-icon="pause"]').classList.toggle('hidden', isPaused);
        toggle.querySelector('[data-slider-icon="play"]').classList.toggle('hidden', !isPaused);
        toggle.querySelector('[data-slider-toggle-label]').textContent = isPaused
            ? (toggle.dataset.labelPlay ?? 'Putar slide otomatis')
            : (toggle.dataset.labelPause ?? 'Jeda slide otomatis');
    };
    toggle?.addEventListener('click', () => {
        isPaused = !isPaused;
        renderToggle();
        sync();
    });

    root.querySelector('[data-slider-prev]')?.addEventListener('click', () => goTo(index - 1));
    root.querySelector('[data-slider-next]')?.addEventListener('click', () => goTo(index + 1));
    dots.forEach((dot) => dot.addEventListener('click', () => goTo(Number(dot.dataset.sliderDot))));

    root.addEventListener('mouseenter', () => hold(true));
    root.addEventListener('mouseleave', () => hold(false));
    root.addEventListener('focusin', () => hold(true));
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) {
            hold(false);
        }
    });
    document.addEventListener('visibilitychange', sync);

    initSwipe(viewport, (direction) => goTo(index + direction));

    if (toggle) {
        renderToggle();
    }
    goTo(0);
    sync();
}

/** Geser horizontal (sentuh/pena/mouse); gulir vertikal tetap milik browser lewat touch-action: pan-y. */
function initSwipe(viewport, onSwipe) {
    let startX = null;
    let startY = 0;
    let didSwipe = false;

    viewport.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }
        startX = event.clientX;
        didSwipe = false;
        startY = event.clientY;
    });
    viewport.addEventListener('pointerup', (event) => {
        if (startX === null) {
            return;
        }
        const deltaX = event.clientX - startX;
        const deltaY = event.clientY - startY;
        startX = null;
        if (Math.abs(deltaX) >= SWIPE_THRESHOLD_PX && Math.abs(deltaX) > Math.abs(deltaY)) {
            didSwipe = true;
            onSwipe(deltaX < 0 ? 1 : -1);
        }
    });
    // Seret di atas tombol CTA jangan dianggap klik.
    viewport.addEventListener('click', (event) => {
        if (didSwipe) {
            event.preventDefault();
            event.stopPropagation();
            didSwipe = false;
        }
    }, true);
    viewport.addEventListener('pointercancel', () => {
        startX = null;
    });
}
