/**
 * Viewer 3D "Coba Virtual" di <dialog> yang dibuka modules/modal.js.
 * @google/model-viewer (three.js) di-import dinamis saat modal pertama dibuka,
 * jadi chunk-nya terpisah dan tidak membebani halaman lain.
 */
const ROTATE_STEP = Math.PI / 4;
const ZOOM_FACTOR = 0.8;
const NOTICE_MS = 3500;

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let modelViewerImport = null;

function loadModelViewer() {
    modelViewerImport ??= import('@google/model-viewer').catch((error) => {
        modelViewerImport = null;
        throw error;
    });

    return modelViewerImport;
}

export function initProductViewer(dialog) {
    const viewer = dialog.querySelector('[data-viewer]');
    const shell = dialog.querySelector('[data-viewer-shell]');
    const variants = [...dialog.querySelectorAll('[data-viewer-variant]')];
    const progress = dialog.querySelector('[data-viewer-progress]');
    const progressBar = dialog.querySelector('[data-viewer-progress-bar]');
    const status = dialog.querySelector('[data-viewer-status]');
    const percent = dialog.querySelector('[data-viewer-percent]');
    const notice = dialog.querySelector('[data-viewer-notice]');
    const errorBox = dialog.querySelector('[data-viewer-error]');
    const arButton = dialog.querySelector('[data-viewer-ar]');
    const arTip = dialog.querySelector('[data-viewer-ar-tip]');
    const fullscreenButton = dialog.querySelector('[data-viewer-fullscreen]');
    const { msgFullscreen, msgExitFullscreen, msgArFailed } = dialog.dataset;
    const initialOrbit = viewer.getAttribute('camera-orbit') ?? '0deg 75deg 105%';

    let current = variants.find((button) => button.getAttribute('aria-pressed') === 'true') ?? variants[0];
    let noticeTimer = null;

    if (!current) {
        return;
    }
    if (reducedMotion.matches) {
        viewer.setAttribute('interaction-prompt', 'none');
    }

    const setProgress = (value) => {
        const done = value >= 1;
        progress.hidden = done;
        status.hidden = done;
        progressBar.style.transform = `scaleX(${value})`;
        percent.textContent = `${Math.round(value * 100)}%`;
    };

    const showNotice = (message) => {
        notice.textContent = message;
        notice.hidden = false;
        clearTimeout(noticeTimer);
        noticeTimer = setTimeout(() => (notice.hidden = true), NOTICE_MS);
    };

    const showError = () => {
        setProgress(1);
        errorBox.hidden = false;
    };

    const syncAr = () => {
        const available = Boolean(viewer.canActivateAR);
        arButton.setAttribute('aria-disabled', String(!available));
        arTip.hidden = available;
        if (available) {
            arButton.removeAttribute('aria-describedby');
        } else {
            arButton.setAttribute('aria-describedby', arTip.id);
        }
    };

    const showVariant = async (button) => {
        current = button;
        variants.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        errorBox.hidden = true;
        setProgress(0);

        try {
            await loadModelViewer();
        } catch {
            showError();
            return;
        }
        // Model-viewer di-upgrade setelah import; setAttribute aman sebelum/sesudah upgrade.
        viewer.setAttribute('alt', button.dataset.alt);
        if (button.dataset.poster) {
            viewer.setAttribute('poster', button.dataset.poster);
        } else {
            viewer.removeAttribute('poster');
        }
        viewer.setAttribute('src', button.dataset.src);
    };

    // Kamera: animasi halus bawaan, langsung lompat bila reduced-motion.
    const settle = () => {
        if (reducedMotion.matches) {
            viewer.jumpCameraToGoal?.();
        }
    };
    const rotate = (direction) => {
        const { theta, phi, radius } = viewer.getCameraOrbit();
        viewer.cameraOrbit = `${theta + direction * ROTATE_STEP}rad ${phi}rad ${radius}m`;
        settle();
    };
    const zoom = (factor) => {
        viewer.fieldOfView = `${viewer.getFieldOfView() * factor}deg`;
        settle();
    };
    const reset = () => {
        // Set nilai berbeda dulu agar perubahan tetap terdeteksi walau nilainya sama.
        viewer.cameraOrbit = '';
        viewer.cameraOrbit = initialOrbit;
        viewer.cameraTarget = '';
        viewer.cameraTarget = 'auto auto auto';
        viewer.fieldOfView = '';
        viewer.fieldOfView = 'auto';
        settle();
    };

    dialog.addEventListener('modal:open', () => {
        if (!viewer.getAttribute('src') || !errorBox.hidden) {
            showVariant(current);
        }
    });
    dialog.addEventListener('close', () => {
        if (document.fullscreenElement === shell) {
            document.exitFullscreen();
        }
    });

    // Prefetch chunk saat pengguna mengarah ke tombol pembuka.
    document.querySelectorAll(`[data-modal-open="${dialog.id}"]`).forEach((opener) => {
        ['pointerenter', 'focus'].forEach((type) => opener.addEventListener(type, () => loadModelViewer().catch(() => {}), { once: true }));
    });

    viewer.addEventListener('progress', (event) => setProgress(event.detail.totalProgress));
    viewer.addEventListener('load', () => {
        setProgress(1);
        syncAr();
    });
    viewer.addEventListener('error', (event) => {
        if (event.detail?.type === 'loadfailure') {
            showError();
        }
    });
    viewer.addEventListener('ar-status', (event) => {
        arButton.setAttribute('aria-pressed', String(event.detail.status === 'session-started'));
        if (event.detail.status === 'failed') {
            showNotice(msgArFailed);
        }
    });

    dialog.addEventListener('click', (event) => {
        const target = event.target.closest('button');
        if (!target || !dialog.contains(target)) {
            return;
        }
        if (target.matches('[data-viewer-variant]') && target !== current) {
            showVariant(target);
        } else if (target.matches('[data-viewer-retry]')) {
            viewer.removeAttribute('src');
            showVariant(current);
        } else if (target.matches('[data-viewer-ar]') && target.getAttribute('aria-disabled') !== 'true') {
            viewer.activateAR();
        } else if (!viewer.loaded) {
            return;
        } else if (target.matches('[data-viewer-rotate]')) {
            rotate(Number(target.dataset.viewerRotate));
        } else if (target.matches('[data-viewer-zoom]')) {
            zoom(target.dataset.viewerZoom === 'in' ? ZOOM_FACTOR : 1 / ZOOM_FACTOR);
        } else if (target.matches('[data-viewer-reset]')) {
            reset();
        }
    });

    // Fullscreen pada isi dialog (elemen <dialog> sendiri tidak boleh fullscreen).
    if (document.fullscreenEnabled) {
        fullscreenButton.hidden = false;
        fullscreenButton.addEventListener('click', () => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                shell.requestFullscreen().catch(() => {});
            }
        });
        document.addEventListener('fullscreenchange', () => {
            const active = document.fullscreenElement === shell;
            fullscreenButton.setAttribute('aria-pressed', String(active));
            fullscreenButton.querySelector('[data-icon-enter]').toggleAttribute('hidden', active);
            fullscreenButton.querySelector('[data-icon-exit]').toggleAttribute('hidden', !active);
            fullscreenButton.querySelector('[data-label]').textContent = active ? msgExitFullscreen : msgFullscreen;
        });
    }
}
