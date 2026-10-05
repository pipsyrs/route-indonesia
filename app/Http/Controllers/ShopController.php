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
        $origin = $this->matchCity($this->stringQuery($request, 'from'));
        $destination = $this->matchCity($this->stringQuery($request, 'to'));
        $date = $this->resolveDate($request);
        $isSameCity = $origin !== null && $origin === $destination;

        $trips = $isSameCity ? [] : array_values(array_filter(
            DummyShop::trips(),
            fn (array $trip) => ($origin === null || $trip['origin'] === $origin)
                && ($destination === null || $trip['destination'] === $destination),
        ));

        return view('pages.catalog.index', [
            'cities' => DummyShop::cities(),
            'trips' => $trips,
            'facilities' => DummyShop::facilityLabels(),
            'query' => [
                'from' => $origin,
                'to' => $destination,
                'date' => $date->toDateString(),
                'date_label' => $date->translatedFormat('l, j F Y'),
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

    /** Hanya Y-m-d; kosong, invalid, atau sebelum hari ini → besok. */
    private function resolveDate(Request $request): Carbon
    {
        $value = $this->stringQuery($request, 'date');
        if ($value === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $this->tomorrow();
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $this->tomorrow();
        }

        // Tanggal meluap (2026-02-31) ditolak.
        if (! $date || $date->format('Y-m-d') !== $value || $date->lessThan(now()->startOfDay())) {
            return $this->tomorrow();
        }

        return $date->locale('id');
    }

    private function tomorrow(): Carbon
    {
        return now()->addDay()->startOfDay()->locale('id');
    }
}
