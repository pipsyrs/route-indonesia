<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/cek-pesanan', 'checkOrder')->name('order.check');
    Route::get('/shop', 'shop')->name('shop');
    Route::get('/shop/{slug}', 'productShow')->where('slug', '[a-z0-9-]+')->name('shop.show');
    Route::get('/trip', 'tripPage')->name('trip');
    Route::get('/kolaborasi', 'collaboration')->name('collaboration');
    Route::get('/karir', 'career')->name('career');
});

Route::get('/lang/{locale}', LocaleController::class)
    ->whereIn('locale', SetLocale::SUPPORTED)
    ->name('locale.switch');

Route::controller(ShopController::class)->group(function () {
    Route::get('/katalog', 'index')->name('catalog.index');
    // Maksimal 10 digit tanpa nol di depan: angka di luar batas int jadi 404, bukan 500.
    Route::get('/katalog/{id}', 'show')->where('id', '[1-9][0-9]{0,9}')->name('catalog.show');
    Route::get('/keranjang', 'cart')->name('cart');
    Route::get('/checkout', 'checkout')->name('checkout');
    Route::get('/pembayaran', 'payment')->name('payment');
    Route::get('/pembayaran/selesai', 'success')->name('payment.success');
});

Route::controller(AccountController::class)->group(function () {
    Route::get('/masuk', 'login')->name('login');
    Route::get('/daftar', 'register')->name('register');
    Route::get('/akun', 'dashboard')->name('account.dashboard');
    Route::get('/akun/pesanan', 'orders')->name('account.orders');
    Route::get('/akun/profil', 'profile')->name('account.profile');
});

// Redirect URL lama (tanpa nama route).
// Route::redirect membuang query string, jadi /search pakai closure dengan whitelist key.
Route::get('/search', function (Request $request) {
    $query = array_filter(
        $request->only(['from', 'to', 'date']),
        fn ($value) => is_string($value) && $value !== '',
    );

    return redirect()->route('catalog.index', $query, 301);
});
Route::redirect('/trip/{id}', '/katalog/{id}', 301)->where('id', '[1-9][0-9]{0,9}');
Route::redirect('/booking', '/keranjang', 301);
Route::redirect('/booking/success', '/pembayaran/selesai', 301);
Route::redirect('/payment', '/pembayaran', 301);
Route::redirect('/promo', '/', 301);
