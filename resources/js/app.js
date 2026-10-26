import './bootstrap';
// Alpine is bundled by Livewire; register Alpine.data() here before Livewire.start().
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { initAccountAddresses } from './modules/account-addresses';
import { initAccountForms } from './modules/account-forms';
import { initAccountNav } from './modules/account-nav';
import { initAddToCart } from './modules/add-to-cart';
import { initCartBadge } from './modules/cart';
import { initCartPage } from './modules/cart-page';
import { initCheckout } from './modules/checkout';
import { initCheckoutPayment } from './modules/checkout-payment';
import { initHeroSlider } from './modules/hero-slider';
import { initModals } from './modules/modal';
import { initOrderSuccess } from './modules/order-success';
import { initBookingForm } from './modules/booking-form';
import { initClipboard } from './modules/clipboard';
import { initNav } from './modules/nav';
import { initOrderCheck } from './modules/order-check';
import { initPayment } from './modules/payment';
import { paymentProofUpload } from './modules/payment-proof-upload';
import { initProductBuy } from './modules/product-buy';
import { initProductViewer } from './modules/product-viewer';
import { initSearchFilter } from './modules/search-filter';
import { initSearchForm } from './modules/search-form';
import { initSeatPicker } from './modules/seat-picker';
import { initSuccess } from './modules/success';

/** Elemen ber-atribut data-module="<nama>" diinisialisasi oleh modul yang sesuai. */
const MODULES = {
    nav: initNav,
    'search-form': initSearchForm,
    'search-filter': initSearchFilter,
    'seat-picker': initSeatPicker,
    'booking-form': initBookingForm,
    payment: initPayment,
    success: initSuccess,
    'order-check': initOrderCheck,
    'cart-badge': initCartBadge,
    'account-nav': initAccountNav,
    'hero-slider': initHeroSlider,
    'testimonial-slider': initHeroSlider,
    'add-to-cart': initAddToCart,
    'cart-page': initCartPage,
    checkout: initCheckout,
    'checkout-payment': initCheckoutPayment,
    'order-success': initOrderSuccess,
    'account-forms': initAccountForms,
    'account-addresses': initAccountAddresses,
    'product-buy': initProductBuy,
    'product-viewer': initProductViewer,
};

document.querySelectorAll('[data-module]').forEach((element) => {
    MODULES[element.dataset.module]?.(element);
});

initClipboard();
initModals();

Alpine.data('paymentProofUpload', paymentProofUpload);

// Started after vanilla modules so a Livewire error cannot block them.
Livewire.start();
