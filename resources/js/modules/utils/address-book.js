import { readLocal, writeLocal } from './storage';

/** Buku alamat mode demo (localStorage perangkat ini); dipakai halaman akun & checkout. */
export const ADDRESS_KEY = 'ri_addresses';
export const ADDRESS_LABELS = ['Rumah', 'Kantor', 'Lainnya'];
const LIMITS = { name: 60, phone: 16, address: 200, city: 60, postal: 5 };
// Entri lama tanpa provinsi/kecamatan tetap valid (dinormalisasi ke '').
const OPTIONAL_LIMITS = { province: 60, district: 60 };

/** null = belum pernah disimpan (perlu seed); data rusak dibuang, bukan dipercaya. */
export function readAddresses() {
    const stored = readLocal(ADDRESS_KEY);
    if (stored === null) {
        return null;
    }
    if (!Array.isArray(stored)) {
        return [];
    }

    const ids = new Set();
    const valid = stored.filter((item) => item !== null && typeof item === 'object'
        && typeof item.id === 'string' && ADDRESS_LABELS.includes(item.label)
        && Object.keys(LIMITS).every((key) => typeof item[key] === 'string' && item[key] !== '' && item[key].length <= LIMITS[key])
        // id ganda: hanya entri pertama dipakai.
        && !ids.has(item.id) && ids.add(item.id));

    let hasPrimary = false;
    const addresses = valid.map((item) => {
        const primary = item.primary === true && !hasPrimary;
        hasPrimary ||= primary;
        const optional = Object.fromEntries(Object.entries(OPTIONAL_LIMITS).map(([key, limit]) => [
            key, typeof item[key] === 'string' && item[key].length <= limit ? item[key] : '',
        ]));

        return { id: item.id, label: item.label, name: item.name, phone: item.phone, address: item.address, city: item.city, postal: item.postal, ...optional, primary };
    });
    if (!hasPrimary && addresses.length > 0) {
        addresses[0].primary = true;
    }

    return addresses;
}

export function writeAddresses(addresses) {
    writeLocal(ADDRESS_KEY, addresses);
}

/** Tambah alamat; alamat pertama jadi utama. @returns {'added'|'duplicate'|'invalid'} */
export function saveAddress(entry) {
    const addresses = readAddresses() ?? [];
    const candidate = {
        id: `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 8)}`,
        label: ADDRESS_LABELS.includes(entry.label) ? entry.label : ADDRESS_LABELS[0],
        ...Object.fromEntries([...Object.keys(LIMITS), ...Object.keys(OPTIONAL_LIMITS)].map((key) => [key, String(entry[key] ?? '').trim()])),
        primary: addresses.length === 0,
    };
    const isValid = Object.entries(LIMITS).every(([key, limit]) => candidate[key] !== '' && candidate[key].length <= limit)
        && Object.entries(OPTIONAL_LIMITS).every(([key, limit]) => candidate[key].length <= limit);
    if (!isValid) {
        return 'invalid';
    }
    // Duplikat = jalan + kode pos sama (abaikan huruf besar/kecil & spasi berlebih).
    const street = (value) => value.toLowerCase().replace(/\s+/g, ' ');
    if (addresses.some((item) => street(item.address) === street(candidate.address) && item.postal === candidate.postal)) {
        return 'duplicate';
    }
    writeAddresses([...addresses, candidate]);

    return 'added';
}

/** "Kecamatan, Kota, Provinsi 12345"; bagian kosong dilewati. */
export function formatRegion(item) {
    return `${[item.district, item.city, item.province].filter(Boolean).join(', ')} ${item.postal}`;
}
