// sessionStorage bisa melempar error (mode privat, kuota penuh); halaman harus tetap jalan.
export function readSession(key) {
    try {
        const raw = sessionStorage.getItem(key);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

export function writeSession(key, value) {
    try {
        sessionStorage.setItem(key, JSON.stringify(value));
    } catch {
        // abaikan: data hanya dipakai untuk kenyamanan tampilan
    }
}

export function removeSession(key) {
    try {
        sessionStorage.removeItem(key);
    } catch {
        // abaikan
    }
}

// localStorage: keranjang & alur checkout bertahan antar tab/refresh.
export function readLocal(key) {
    try {
        const raw = localStorage.getItem(key);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

export function writeLocal(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // abaikan: mode privat / kuota penuh
    }
}

export function removeLocal(key) {
    try {
        localStorage.removeItem(key);
    } catch {
        // abaikan
    }
}
