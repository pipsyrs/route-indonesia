<?php

namespace App\Http\Controllers;

use App\Support\DummyShop;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Alur katalog → keranjang → checkout → pembayaran (tampilan saja, data dummy).
 * Keranjang & pembayaran hidup di localStorage; server hanya mengirim data statis.
 */
class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $routeSlug = $this->stringQuery($request, 'route');
        // Slug tidak dikenal → 404, bukan diam-diam menampilkan semua trip.
        $namedRoute = $routeSlug === null ? null : (DummyShop::findRoute($routeSlug) ?? abort(404));

        $origin = $namedRoute ? null : $this->matchCity($this->stringQuery($request, 'from'));
        $destination = $namedRoute ? null : $this->matchCity($this->stringQuery($request, 'to'));
        $keyword = $this->stringQuery($request, 'q');
        $date = $this->parseDate($request);
        $isSameCity = $origin !== null && $origin === $destination;

        $trips = $namedRoute ? DummyShop::routeTrips($namedRoute) : ($isSameCity ? [] : array_values(array_filter(
            DummyShop::trips(),
            fn (array $trip) => ($origin === null || $trip['origin'] === $origin)
                && ($destination === null || $trip['destination'] === $destination),
        )));

        if ($keyword !== null) {
            $trips = $this->filterByKeyword($trips, $keyword);
        }

        return view('pages.catalog.index', [
            'trips' => $trips,
            'facilities' => DummyShop::facilityLabels(),
            'query' => [
                'q' => $keyword,
                'from' => $origin,
                'to' => $destination,
                'date' => $date?->toDateString(),
                'date_label' => $date?->translatedFormat('l, j F Y'),
                'route' => $namedRoute['slug'] ?? null,
                'route_name' => $namedRoute['name'] ?? null,
            ],
            'searchError' => $isSameCity ? 'Kota asal dan tujuan tidak boleh sama.' : null,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $trip = DummyShop::findTrip($id) ?? abort(404);
        $date = $this->resolveDate($request);

        return view('pages.catalog.show', [
            'trip' => $trip,
            'date' => $date->toDateString(),
            'dateLabel' => $date->translatedFormat('l, j F Y'),
            'maxQty' => min($trip['seats_left'], DummyShop::MAX_QTY),
            'facilityLabels' => DummyShop::facilityLabels(),
            'seatMap' => DummyShop::seatMap($trip, $date->toDateString()),
        ]);
    }

    public function cart(): View
    {
        return view('pages.cart', [
            'serviceFee' => DummyShop::SERVICE_FEE,
            'maxQty' => DummyShop::MAX_QTY,
        ]);
    }

    public function checkout(): View
    {
        return view('pages.checkout', [
            'serviceFee' => DummyShop::SERVICE_FEE,
            'paymentMethods' => DummyShop::paymentMethods(),
        ]);
    }

    public function payment(): View
    {
        return view('pages.payment-new', [
            'paymentMethods' => DummyShop::paymentMethods(),
        ]);
    }

    public function success(): View
    {
        return view('pages.payment-success');
    }

    /* Sanitasi query: disalin dari PageController (bukan dipanggil). */

    /** Array atau tipe lain dianggap tidak ada. */
    private function stringQuery(Request $request, string $key): ?string
    {
        $value = $request->query($key);
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 100);
    }

    /** Ejaan kanonik dari DummyShop::cities(), case-insensitive. */
    private function matchCity(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        foreach (DummyShop::cities() as $city) {
            if (strcasecmp($city, $name) === 0) {
                return $city;
            }
        }

        return null;
    }

    /** Kata kunci case-insensitive pada asal, tujuan, operator, kendaraan, dan nama rute induk. */
    private function filterByKeyword(array $trips, string $keyword): array
    {
        $routeNames = [];
        foreach (DummyShop::routes() as $route) {
            foreach ($route['trip_ids'] as $tripId) {
                $routeNames[$tripId][] = $route['name'];
            }
        }

        return array_values(array_filter($trips, function (array $trip) use ($keyword, $routeNames) {
            $haystacks = [$trip['origin'], $trip['destination'], $trip['operator'], $trip['vehicle'], ...($routeNames[$trip['id']] ?? [])];

            foreach ($haystacks as $haystack) {
                if (mb_stripos($haystack, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        }));
    }

    /** Kosong, invalid, atau sebelum hari ini → besok. */
    private function resolveDate(Request $request): Carbon
    {
        return $this->parseDate($request) ?? $this->tomorrow();
    }

    /** Hanya Y-m-d hari ini atau setelahnya; selain itu null. */
    private function parseDate(Request $request): ?Carbon
    {
        $value = $this->stringQuery($request, 'date');
        if ($value === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        // Tanggal meluap (2026-02-31) ditolak.
        if (! $date || $date->format('Y-m-d') !== $value || $date->lessThan(now()->startOfDay())) {
            return null;
        }

        return $date->locale('id');
    }

    private function tomorrow(): Carbon
    {
        return now()->addDay()->startOfDay()->locale('id');
    }
}
