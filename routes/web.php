<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\SetLocale;
use App\Livewire\Pages\Checkout;
use App\Livewire\Pages\Payment;
use App\Livewire\Pages\PaymentSuccess;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/check-order', 'checkOrder')->name('order.check');
    Route::get('/shop', 'shop')->name('shop');
    Route::get('/shop/{slug}', 'productShow')->where('slug', '[a-z0-9-]+')->name('shop.show');
    Route::get('/trip', 'tripPage')->name('trip');
    Route::get('/community', 'community')->name('community');
    Route::get('/collaborations', 'collaboration')->name('collaboration');
    Route::get('/careers', 'career')->name('career');
});

Route::get('/lang/{locale}', LocaleController::class)
    ->whereIn('locale', SetLocale::SUPPORTED)
    ->name('locale.switch');

Route::controller(ShopController::class)->group(function () {
    Route::get('/catalog', 'index')->name('catalog.index');
    // Maksimal 10 digit tanpa nol di depan: angka di luar batas int jadi 404, bukan 500.
    Route::get('/catalog/{id}', 'show')->where('id', '[1-9][0-9]{0,9}')->name('catalog.show');
    Route::get('/cart', 'cart')->name('cart');
});

Route::get('/checkout', Checkout::class)->name('checkout');
Route::get('/payment', Payment::class)->name('payment');
Route::get('/payment/success', PaymentSuccess::class)->name('payment.success');

Route::controller(AccountController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::get('/register', 'register')->name('register');
    Route::get('/account', 'dashboard')->name('account.dashboard');
    Route::get('/account/orders', 'orders')->name('account.orders');
    Route::get('/account/addresses', 'addresses')->name('account.addresses');
    Route::get('/account/profile', 'profile')->name('account.profile');
});

// Redirect URL lama (tanpa nama route).
// Route::redirect membuang query string, jadi URL yang query-nya dipakai lewat controller dengan whitelist key.
Route::get('/search', LegacyRedirectController::class)
    ->defaults('target', 'catalog.index')->defaults('keys', ['q', 'from', 'to', 'date']);
Route::get('/katalog', LegacyRedirectController::class)
    ->defaults('target', 'catalog.index')->defaults('keys', ['route', 'from', 'to', 'q', 'date']);
Route::get('/katalog/{id}', LegacyRedirectController::class)
    ->defaults('target', 'catalog.show')->defaults('keys', ['date'])->where('id', '[1-9][0-9]{0,9}');
Route::redirect('/trip/{id}', '/catalog/{id}', 301)->where('id', '[1-9][0-9]{0,9}');
Route::redirect('/booking', '/cart', 301);
Route::redirect('/booking/success', '/payment/success', 301);
Route::redirect('/keranjang', '/cart', 301);
Route::redirect('/pembayaran', '/payment', 301);
Route::redirect('/pembayaran/selesai', '/payment/success', 301);
// Halaman cek pesanan, akun & auth tidak membaca query string.
Route::redirect('/cek-pesanan', '/check-order', 301);
Route::redirect('/masuk', '/login', 301);
Route::redirect('/daftar', '/register', 301);
Route::redirect('/akun', '/account', 301);
Route::redirect('/akun/pesanan', '/account/orders', 301);
Route::redirect('/akun/alamat', '/account/addresses', 301);
Route::redirect('/akun/profil', '/account/profile', 301);
Route::redirect('/promo', '/', 301);
Route::redirect('/kolaborasi', '/collaborations', 301);
Route::redirect('/karir', '/careers', 301);
