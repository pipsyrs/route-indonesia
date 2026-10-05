<?php

namespace App\Support;

use App\Models\Promo;
use App\Models\Schedule;
use App\Models\ScheduleStop;
use App\Models\TravelRoute;

/**
 * Mengubah model menjadi array yang dipakai Blade. View tidak pernah
 * menerima model, sehingga kontrak data halaman tetap stabil dan
 * tidak ada lazy loading yang terpicu dari template.
 */
class TripPresenter
{
    /** Relasi yang wajib di-eager-load sebelum memanggil presentTrip(). */
    public const SCHEDULE_RELATIONS = [
        'vehicle.operator',
        'vehicle.vehicleType.facilities',
        'route.origin',
        'route.destination',
        'stops.location',
    ];

    /**
     * Master data yang dirujuk jadwal bisa saja sudah soft-deleted
     * (relasi null), atau jadwal belum punya titik jemput/antar.
     * Jadwal seperti itu tidak boleh dijual.
     */
    public static function isPresentable(Schedule $schedule): bool
    {
        return $schedule->vehicle?->operator !== null
            && $schedule->vehicle->vehicleType !== null
            && $schedule->route?->origin !== null
            && $schedule->route->destination !== null
            && $schedule->stops->contains('type', ScheduleStop::TYPE_PICKUP)
            && $schedule->stops->contains('type', ScheduleStop::TYPE_DROPOFF);
    }

    /** @param  list<string>  $occupied  hanya diisi di halaman trip/pesanan */
    public static function presentTrip(Schedule $schedule, array $occupied = []): array
    {
        $vehicle = $schedule->vehicle;
        $minutes = $schedule->duration_minutes;

        return [
            'id' => $schedule->id,
            'operator' => $vehicle->operator->name,
            'vehicle' => $vehicle->vehicleType->name,
            'origin' => $schedule->route->origin->name,
            'destination' => $schedule->route->destination->name,
            'depart' => $schedule->departure_at->format('H:i'),
            'arrive' => $schedule->arrival_at->format('H:i'),
            'arrives_next_day' => ! $schedule->arrival_at->isSameDay($schedule->departure_at),
            'duration_minutes' => $minutes,
            'duration' => intdiv($minutes, 60).'j '.($minutes % 60).'m',
            'price' => $schedule->price,
            'seats_left' => $schedule->seats_left,
            'facilities' => $vehicle->vehicleType->facilities->pluck('code')->all(),
            'rating' => $vehicle->operator->rating_average,
            'occupied' => $occupied,
            'pickups' => self::presentStops($schedule, ScheduleStop::TYPE_PICKUP),
            'dropoffs' => self::presentStops($schedule, ScheduleStop::TYPE_DROPOFF),
        ];
    }

    public static function presentStop(ScheduleStop $stop): array
    {
        return [
            'id' => $stop->id,
            'name' => $stop->location->name,
            'time' => $stop->stop_at->format('H:i'),
            'address' => $stop->location->address,
        ];
    }

    /** @param  int  $tripsTomorrow  jumlah jadwal besok (hasil withCount) */
    public static function presentPopularRoute(TravelRoute $route, int $tripsTomorrow): array
    {
        return [
            'from' => $route->origin->name,
            'to' => $route->destination->name,
            'price' => $route->base_price,
            'duration' => self::formatApproxHours($route->estimated_duration_minutes),
            'trips' => $tripsTomorrow,
        ];
    }

    public static function presentPromo(Promo $promo): array
    {
        $rules = $promo->rules ?? [];

        return [
            'code' => $promo->code,
            'title' => $promo->title,
            'desc' => $promo->description,
            'until' => $promo->valid_until->copy()->locale('id')->translatedFormat('j M Y'),
            'type' => $promo->discount_type->value,
            'value' => $promo->discount_value,
            'max_discount' => $promo->max_discount,
            'min_transaction' => $promo->min_transaction,
            'weekdays' => $rules['departure_weekdays'] ?? [],
            'methods' => $rules['payment_method_codes'] ?? [],
        ];
    }

    /** 180 → "± 3 jam", 210 → "± 3,5 jam" (dibulatkan ke setengah jam terdekat). */
    public static function formatApproxHours(int $minutes): string
    {
        $hours = round($minutes / 60 * 2) / 2;
        $label = fmod($hours, 1.0) === 0.0 ? (string) (int) $hours : number_format($hours, 1, ',', '');

        return "± {$label} jam";
    }

    private static function presentStops(Schedule $schedule, string $type): array
    {
        return $schedule->stops
            ->where('type', $type)
            ->map(fn (ScheduleStop $stop) => self::presentStop($stop))
            ->values()
            ->all();
    }
}
