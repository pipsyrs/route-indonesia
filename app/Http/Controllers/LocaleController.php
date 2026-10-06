<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        // Route sudah whereIn, dicek ulang agar controller aman bila dipakai di route lain.
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        $request->session()->put('locale', $locale);

        return redirect()->to($this->safePrevious());
    }

    /** Hanya URL internal (bukan /lang/*); Referer eksternal jatuh ke home. */
    private function safePrevious(): string
    {
        $base = url('/');
        $previous = url()->previous();
        $isInternal = $previous === $base || str_starts_with($previous, $base.'/');
        $isLangRoute = $previous === $base.'/lang' || str_starts_with($previous, $base.'/lang/');

        return $isInternal && ! $isLangRoute ? $previous : route('home');
    }
}
