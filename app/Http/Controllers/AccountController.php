<?php

namespace App\Http\Controllers;

use App\Support\DummyShop;
use Illuminate\View\View;

/**
 * Halaman akun mode demo: data dummy, tanpa auth.
 * Status login hanya disimulasikan di localStorage (`ri_session`).
 */
class AccountController extends Controller
{
    public function login(): View
    {
        return view('pages.account.login');
    }

    public function register(): View
    {
        return view('pages.account.register');
    }

    public function dashboard(): View
    {
        return view('pages.account.dashboard', [
            'user' => DummyShop::user(),
            'orders' => array_slice(DummyShop::orders(), 0, 3),
        ]);
    }

    public function orders(): View
    {
        return view('pages.account.orders', [
            'user' => DummyShop::user(),
            'orders' => DummyShop::orders(),
        ]);
    }

    public function profile(): View
    {
        return view('pages.account.profile', [
            'user' => DummyShop::user(),
        ]);
    }
}
