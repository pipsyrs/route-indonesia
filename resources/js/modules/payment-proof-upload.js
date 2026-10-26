// Samakan dengan rules PaymentProofForm; server tetap sumber kebenaran.
const MAX_BYTES = 2 * 1024 * 1024;
const ALLOWED_EXTENSION = /\.(jpe?g|png|pdf)$/i;

/** Alpine.data: validasi client sebelum Livewire upload, drag & drop, progres. */
export function paymentProofUpload() {
    return {
        uploading: false,
        progress: 0,
        dragging: false,
        sent: false,
        clientError: '',
        status: '',

        // Dipasang di fase capture agar file invalid tidak sampai ke listener upload Livewire.
        check(event) {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            this.clientError = fileError(file);
            if (this.clientError) {
                event.stopPropagation();
                event.target.value = '';
            }
        },

        drop(event) {
            this.dragging = false;
            const file = event.dataTransfer?.files?.[0];
            if (!file) {
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            this.$refs.input.files = transfer.files;
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        start() {
            this.uploading = true;
            this.progress = 0;
            this.status = 'Mengunggah file…';
        },

        end(message) {
            this.uploading = false;
            this.status = message;
        },

        reset() {
            Object.assign(this, { uploading: false, progress: 0, dragging: false, sent: false, clientError: '', status: '' });
        },
    };
}

function fileError(file) {
    // file.type bisa kosong/non-standar (image/jpg, application/x-pdf): pakai ekstensi; server yang cek isi file.
    if (!ALLOWED_EXTENSION.test(file.name)) {
        return 'Format file harus JPG, PNG, atau PDF.';
    }
    if (file.size > MAX_BYTES) {
        return 'Ukuran file maksimal 2 MB.';
    }

    return '';
}
