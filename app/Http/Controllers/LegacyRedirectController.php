<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/** Redirect 301 URL lama ke route bernama; dikonfigurasi lewat route defaults `target` & `keys`. */
class LegacyRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $route = $request->route();

        // Hanya key whitelist bertipe string & tidak kosong yang diteruskan.
        $query = array_filter(
            $request->only($route->defaults['keys'] ?? []),
            fn ($value) => is_string($value) && $value !== '',
        );

        // parameters() ikut memuat defaults, jadi target/keys dibuang agar tidak jadi query.
        $params = Arr::except($route->parameters(), ['target', 'keys']);

        return redirect()->route($route->defaults['target'], $params + $query, 301);
    }
}
