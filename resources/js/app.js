import './bootstrap';
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
import { initProductBuy } from './modules/product-buy';
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
    'product-buy': initProductBuy,
};

document.querySelectorAll('[data-module]').forEach((element) => {
    MODULES[element.dataset.module]?.(element);
});

initClipboard();
initModals();
