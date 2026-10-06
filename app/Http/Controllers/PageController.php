<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Facility;
use App\Models\PaymentMethod;
use App\Models\Promo;
use App\Models\Schedule;
use App\Models\Testimonial;
use App\Models\TravelRoute;
use App\Support\TripPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Controller halaman frontend RouteIndonesia.
 *
 * Data diambil dari database lalu diubah ke array lewat TripPresenter,
 * sehingga Blade tidak pernah menerima model.
 *
 * Semua input query string disanitasi di sini (bukan di view) karena
 * parameter bisa dikirim sebagai array (`?date[]=x`) dan harus tetap
 * menghasilkan halaman yang valid, bukan error 500.
 */
class PageController extends Controller
{
    /** Biaya layanan per pesanan (belum ada tabel pengaturan). */
    private const SERVICE_FEE = 5000;

    private const MAX_PASSENGERS = 6;

    /** Sama dengan batas parameter route trip.show: tanpa nol di depan, maksimal 10 digit. */
    private const ID_PATTERN = '/^[1-9][0-9]{0,9}$/';

    private const DEFAULT_ORIGIN = 'Jakarta';

    private const DEFAULT_DESTINATION = 'Bandung';

    /** Kode pesan yang boleh ditampilkan di halaman trip (bukan teks bebas dari URL). */
    private const TRIP_NOTICES = [
        'kursi' => 'Pilihan kursi tidak valid atau sudah terisi. Silakan pilih ulang.',
    ];

    /** Kode pesan yang boleh ditampilkan di halaman pencarian. */
    private const SEARCH_NOTICES = [
        'penuh' => 'Jadwal yang Anda pilih sudah penuh. Silakan pilih jadwal lain.',
    ];

    /** @var array<string, int>|null nama kota aktif => id, di-query sekali per request */
    private ?array $cityIds = null;

    /** @var list<array<string, mixed>>|null */
    private ?array $activePromos = null;

    public function home(): View
    {
        return view('pages.home', [
            'cities' => $this->cities(),
            'popularRoutes' => $this->popularRoutes(),
            'promos' => $this->promos(),
            'testimonials' => $this->testimonials(),
            'products' => $this->products(),
            'defaultDate' => $this->tomorrow()->toDateString(),
        ]);
    }

    public function search(Request $request): View
    {
        [$origin, $destination] = $this->resolveRoute($request);
        $date = $this->resolveDate($request);
        $passengers = $this->resolvePassengers($request);
        $isSameCity = $origin === $destination;

        $trips = $isSameCity ? [] : $this->trips($origin, $destination, $date);

        return view('pages.search', [
            'cities' => $this->cities(),
            'trips' => $trips,
            'operators' => array_values(array_unique(array_column($trips, 'operator'))),
            'facilities' => $this->facilityLabels(),
            'dateStrip' => $this->dateStrip($date),
            'searchError' => $isSameCity ? 'Kota asal dan tujuan tidak boleh sama.' : null,
            'notice' => self::SEARCH_NOTICES[$this->stringQuery($request, 'notice')] ?? null,
            'query' => [
                'from' => $origin,
                'to' => $destination,
                'date' => $date->toDateString(),
                'date_label' => $date->translatedFormat('l, j F Y'),
                'passengers' => $passengers,
            ],
        ]);
    }

    public function trip(Request $request, int $id): View|RedirectResponse
    {
        $schedule = $this->loadSchedule($id) ?? abort(404);
        $trip = $this->presentScheduleWithSeats($schedule);
        $date = $this->scheduleDate($schedule);
        $requestedPassengers = $this->resolvePassengers($request);

        if ($trip['seats_left'] === 0) {
            return redirect()->route('search', [
                'from' => $trip['origin'],
                'to' => $trip['destination'],
                'date' => $date->toDateString(),
                'passengers' => $requestedPassengers,
                'notice' => 'penuh',
            ]);
        }

        $passengers = min($requestedPassengers, $trip['seats_left']);
        $noticeKey = $this->stringQuery($request, 'notice');

        return view('pages.trip', [
            'trip' => $trip,
            'seatMap' => $schedule->vehicle->vehicleType->seat_layout,
            'occupied' => $trip['occupied'],
            'date' => $date->toDateString(),
            'dateLabel' => $date->translatedFormat('l, j F Y'),
            'passengers' => $passengers,
            'passengersReduced' => $passengers < $requestedPassengers,
            'serviceFee' => self::SERVICE_FEE,
            'facilityLabels' => $this->facilityLabels(),
            'notice' => self::TRIP_NOTICES[$noticeKey] ?? null,
            'backQuery' => [
                'from' => $trip['origin'],
                'to' => $trip['destination'],
                'date' => $date->toDateString(),
                'passengers' => $requestedPassengers,
            ],
        ]);
    }

    public function booking(Request $request): View|RedirectResponse
    {
        $context = $this->orderContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('pages.booking', array_merge($context, [
            'promos' => $this->promos(),
        ]));
    }

    public function payment(Request $request): View|RedirectResponse
    {
        $context = $this->orderContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('pages.payment', array_merge($context, [
            // Deadline awal; frontend menyimpannya di sessionStorage agar refresh tidak mereset.
            'deadline' => now()->addMinutes(30)->toIso8601String(),
            'paymentMethods' => $this->paymentMethods(),
            'promos' => $this->promos(),
        ]));
    }

    public function success(Request $request): View|RedirectResponse
    {
        $context = $this->orderContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('pages.success', array_merge($context, [
            // Kode simulasi, tidak disimpan. Booking asli dibuat di fase berikutnya (POST /booking).
            'bookingCode' => $this->generateBookingCode(),
            'paidWith' => $this->findPaymentMethodName($this->stringQuery($request, 'method')),
            'promos' => $this->promos(),
        ]));
    }

    public function checkOrder(): View
    {
        return view('pages.cek-pesanan');
    }

    public function shop(): View
    {
        return view('pages.shop', ['products' => $this->products()]);
    }

    public function productShow(string $slug): View
    {
        $products = collect($this->products());
        $product = $products->firstWhere('slug', $slug) ?? abort(404);

        return view('pages.shop-show', [
            'product' => $product,
            'related' => $products->where('slug', '!=', $slug)->take(3)->values()->all(),
        ]);
    }

    /** Nama `trip()` dan `trips()` sudah dipakai alur lama. */
    public function tripPage(): View
    {
        return view('pages.trips');
    }

    public function collaboration(): View
    {
        return view('pages.collaboration');
    }

    public function career(): View
    {
        return view('pages.career');
    }

    /* ------------------------------------------------------------------ */
    /* Sanitasi input */
    /* ------------------------------------------------------------------ */

    /** Ambil query sebagai string; array atau tipe lain dianggap tidak ada. */
    private function stringQuery(Request $request, string $key): ?string
    {
        $value = $request->query($key);
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 100);
    }

    /** @return array{0: string, 1: string} */
    private function resolveRoute(Request $request): array
    {
        return [
            $this->matchCity($this->stringQuery($request, 'from')) ?? self::DEFAULT_ORIGIN,
            $this->matchCity($this->stringQuery($request, 'to')) ?? self::DEFAULT_DESTINATION,
        ];
    }

    /**
     * Cocokkan nama kota tanpa peduli huruf besar/kecil, kembalikan ejaan kanonik.
     * Dibandingkan di PHP (bukan WHERE LIKE) karena collation SQLite dan MySQL berbeda.
     */
    private function matchCity(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        foreach ($this->cities() as $city) {
            if (strcasecmp($city, $name) === 0) {
                return $city;
            }
        }

        return null;
    }

    /**
     * Hanya format Y-m-d yang diterima (bukan "next monday"). Tanggal lampau
     * dinaikkan ke hari ini; nilai invalid jatuh ke besok.
     */
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

        // Tanggal "meluap" seperti 2026-02-31 ditolak, bukan digeser ke Maret.
        if (! $date || $date->format('Y-m-d') !== $value) {
            return $this->tomorrow();
        }

        $today = now()->startOfDay();

        return ($date->lessThan($today) ? $today : $date)->locale('id');
    }

    private function resolvePassengers(Request $request): int
    {
        $value = $this->stringQuery($request, 'passengers');
        $count = $value !== null && ctype_digit($value) ? (int) $value : 1;

        return max(1, min(self::MAX_PASSENGERS, $count));
    }

    /**
     * Kursi harus ada di denah, belum terisi, unik, dan maksimal 6.
     * Mengembalikan null bila ada satu pun yang tidak valid.
     *
     * @param  list<string>  $occupied
     * @return list<string>|null
     */
    private function resolveSeats(Request $request, Schedule $schedule, array $occupied): ?array
    {
        $rawSeats = $request->query('seats');
        if (! is_array($rawSeats) || $rawSeats === []) {
            return null;
        }

        $bookableSeats = array_diff($schedule->vehicle->vehicleType->seat_numbers, $occupied);
        $seats = [];
        foreach ($rawSeats as $seat) {
            if (! is_string($seat) || ! in_array($seat, $bookableSeats, true)) {
                return null;
            }
            $seats[$seat] = $seat;
        }

        return count($seats) <= self::MAX_PASSENGERS ? array_values($seats) : null;
    }

    /** Cari titik jemput/antar milik jadwal ini berdasarkan id; fallback ke opsi pertama. */
    private function resolveStop(Request $request, string $key, array $stops): array
    {
        $stopId = $this->stringQuery($request, $key);

        foreach ($stops as $stop) {
            if ((string) $stop['id'] === $stopId) {
                return $stop;
            }
        }

        return $stops[0];
    }

    /* ------------------------------------------------------------------ */
    /* Konteks pesanan */
    /* ------------------------------------------------------------------ */

    /**
     * Konteks pesanan (trip, kursi, titik jemput/antar, rincian harga)
     * dibaca dari query string lalu divalidasi terhadap database. Input
     * yang tidak valid diarahkan kembali ke halaman yang tepat.
     */
    private function orderContext(Request $request): array|RedirectResponse
    {
        $tripId = $this->stringQuery($request, 'trip');
        $schedule = $tripId !== null && preg_match(self::ID_PATTERN, $tripId) ? $this->loadSchedule((int) $tripId) : null;

        if ($schedule === null) {
            return redirect()->route('home');
        }

        $trip = $this->presentScheduleWithSeats($schedule);
        $date = $this->scheduleDate($schedule);
        $seats = $this->resolveSeats($request, $schedule, $trip['occupied']);

        if ($seats === null) {
            return redirect()->route('trip.show', [
                'id' => $trip['id'],
                'from' => $trip['origin'],
                'to' => $trip['destination'],
                'date' => $date->toDateString(),
                'notice' => 'kursi',
            ]);
        }

        $pickup = $this->resolveStop($request, 'pickup', $trip['pickups']);
        $dropoff = $this->resolveStop($request, 'dropoff', $trip['dropoffs']);
        $subtotal = $trip['price'] * count($seats);

        return [
            'trip' => $trip,
            'seats' => $seats,
            'date' => $date->toDateString(),
            'dateLabel' => $date->translatedFormat('l, j F Y'),
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'subtotal' => $subtotal,
            'serviceFee' => self::SERVICE_FEE,
            'total' => $subtotal + self::SERVICE_FEE,
            'orderQuery' => [
                'trip' => $trip['id'],
                'from' => $trip['origin'],
                'to' => $trip['destination'],
                'seats' => $seats,
                'date' => $date->toDateString(),
                'pickup' => $pickup['id'],
                'dropoff' => $dropoff['id'],
            ],
        ];
    }

    /**
     * Jadwal yang masih bisa dipesan beserta kursi aktifnya. Null bila
     * tidak ada, sudah berangkat/dibatalkan, terhapus, atau master datanya hilang.
     */
    private function loadSchedule(int $id): ?Schedule
    {
        $schedule = Schedule::bookable()
            ->withSeatsTaken()
            ->with([
                ...TripPresenter::SCHEDULE_RELATIONS,
                'activeSeatReservations:id,schedule_id,seat_number',
            ])
            ->find($id);

        return $schedule && TripPresenter::isPresentable($schedule) ? $schedule : null;
    }

    private function presentScheduleWithSeats(Schedule $schedule): array
    {
        $occupied = $schedule->activeSeatReservations->pluck('seat_number')->all();

        return TripPresenter::presentTrip($schedule, $occupied);
    }

    /** Tanggal jadwal adalah sumber kebenaran; parameter `date` di URL diabaikan. */
    private function scheduleDate(Schedule $schedule): Carbon
    {
        return $schedule->departure_at->copy()->startOfDay()->locale('id');
    }

    private function findPaymentMethodName(?string $methodCode): ?string
    {
        if ($methodCode === null) {
            return null;
        }

        return PaymentMethod::active()->where('code', $methodCode)->value('name');
    }

    /** Format `RID-XXXXXX`, tanpa karakter yang mudah tertukar (0/O, 1/I). */
    private function generateBookingCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return 'RID-'.$code;
    }

    private function tomorrow(): Carbon
    {
        return now()->addDay()->startOfDay()->locale('id');
    }

    /** Strip tanggal ±3 hari di halaman pencarian, tanpa tanggal lampau. */
    private function dateStrip(Carbon $selected): array
    {
        $today = now()->startOfDay();
        $start = $selected->copy()->subDays(3)->max($today);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i)->locale('id');
            $days[] = [
                'date' => $day->toDateString(),
                'day' => $day->translatedFormat('D'),
                'label' => $day->translatedFormat('j M'),
                'is_selected' => $day->isSameDay($selected),
            ];
        }

        return $days;
    }

    /* ------------------------------------------------------------------ */
    /* Query data */
    /* ------------------------------------------------------------------ */

    /** @return list<string> nama kota aktif, urut id */
    private function cities(): array
    {
        return array_keys($this->cityIds());
    }

    /** @return array<string, int> */
    private function cityIds(): array
    {
        return $this->cityIds ??= City::active()->orderBy('id')->pluck('id', 'name')->all();
    }

    /** @return array<string, string> code => label */
    private function facilityLabels(): array
    {
        return Facility::orderBy('id')->pluck('label', 'code')->all();
    }

    private function trips(string $origin, string $destination, Carbon $date): array
    {
        $cityIds = $this->cityIds();
        $route = TravelRoute::between($cityIds[$origin], $cityIds[$destination])->first();
        if ($route === null) {
            return [];
        }

        return $route->schedules()
            ->bookable()
            ->onDate($date)
            ->withSeatsTaken()
            ->with(TripPresenter::SCHEDULE_RELATIONS)
            ->orderBy('departure_at')
            ->get()
            ->filter(fn (Schedule $schedule) => TripPresenter::isPresentable($schedule))
            ->map(fn (Schedule $schedule) => TripPresenter::presentTrip($schedule))
            ->values()
            ->all();
    }

    private function popularRoutes(): array
    {
        $tomorrow = $this->tomorrow();

        return TravelRoute::popular()
            ->with(['origin:id,name', 'destination:id,name'])
            ->withCount(['schedules as trips' => fn ($query) => $query->bookable()->onDate($tomorrow)])
            ->orderBy('id')
            ->get()
            ->filter(fn (TravelRoute $route) => $route->origin !== null && $route->destination !== null)
            ->map(fn (TravelRoute $route) => TripPresenter::presentPopularRoute($route, (int) $route->trips))
            ->values()
            ->all();
    }

    /**
     * Promo aktif; penerapan di checkout masih simulasi client-side
     * (keputusan BOSS) dan tidak mengubah total server.
     */
    private function promos(): array
    {
        return $this->activePromos ??= Promo::active()
            ->orderBy('valid_until')
            ->get()
            ->map(fn (Promo $promo) => TripPresenter::presentPromo($promo))
            ->all();
    }

    private function testimonials(): array
    {
        return Testimonial::published()
            ->latest('id')
            ->limit(9)
            ->get()
            ->map(fn (Testimonial $testimonial) => [
                'name' => $testimonial->name,
                'city' => $testimonial->city_name,
                'rating' => $testimonial->rating,
                'text' => $testimonial->body,
            ])
            ->all();
    }

    /**
     * Produk dummy untuk home & shop (belum ada tabel).
     *
     * @return list<array{slug: string, name: string, brand: string, category: string, price: int, description: string, image: ?string, badge: ?string}>
     */
    private function products(): array
    {
        return [
            [
                'slug' => 'travel-neck-pillow',
                'name' => __('Travel Neck Pillow'),
                'brand' => 'RouteIndonesia',
                'category' => __('Travel Accessories'),
                'price' => 85000,
                'description' => __('Soft memory foam pillow for long road trips.'),
                'image' => 'https://picsum.photos/seed/routeindonesia-travel-neck-pillow/800/800',
                'badge' => __('Best Seller'),
            ],
            [
                'slug' => 'waterproof-daypack',
                'name' => __('Waterproof Daypack'),
                'brand' => 'RouteIndonesia',
                'category' => __('Bags'),
                'price' => 249000,
                'description' => __('Lightweight 20L backpack that keeps your gear dry.'),
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&q=80&auto=format&fit=crop',
                'badge' => __('New'),
            ],
            [
                'slug' => 'universal-travel-adapter',
                'name' => __('Universal Travel Adapter'),
                'brand' => 'RouteIndonesia',
                'category' => __('Electronics'),
                'price' => 129000,
                'description' => __('One adapter with USB ports for over 150 countries.'),
                'image' => 'https://picsum.photos/seed/routeindonesia-universal-travel-adapter/800/800',
                'badge' => null,
            ],
            [
                'slug' => 'packing-cube-set',
                'name' => __('Packing Cube Set'),
                'brand' => 'RouteIndonesia',
                'category' => __('Travel Accessories'),
                'price' => 159000,
                'description' => __('Three-piece set to keep your luggage neat and organized.'),
                'image' => 'https://picsum.photos/seed/routeindonesia-packing-cube-set/800/800',
                'badge' => null,
            ],
            [
                'slug' => 'cabin-suitcase',
                'name' => __('Cabin Suitcase'),
                'brand' => 'RouteIndonesia',
                'category' => __('Luggage'),
                'price' => 649000,
                'description' => __('Hard-shell 20-inch carry-on with smooth spinner wheels.'),
                'image' => 'https://picsum.photos/seed/routeindonesia-cabin-suitcase/800/800',
                'badge' => __('Best Seller'),
            ],
            [
                'slug' => 'leather-weekender-bag',
                'name' => __('Leather Weekender Bag'),
                'brand' => 'RouteIndonesia',
                'category' => __('Bags'),
                'price' => 899000,
                'description' => __('Spacious leather duffel for short getaways out of town.'),
                'image' => 'https://picsum.photos/seed/routeindonesia-leather-weekender-bag/800/800',
                'badge' => __('New'),
            ],
        ];
    }

    /** @return array<string, list<array{id: string, name: string, short: string}>> grup => metode */
    private function paymentMethods(): array
    {
        return PaymentMethod::active()
            ->get()
            ->groupBy('group_name')
            ->map(fn ($methods) => $methods->map(fn (PaymentMethod $method) => [
                'id' => $method->code,
                'name' => $method->name,
                'short' => $method->short_name,
            ])->values()->all())
            ->all();
    }
}
