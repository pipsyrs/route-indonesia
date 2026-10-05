<?php

namespace App\Models;

use App\Enums\ScheduleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = ['route_id', 'vehicle_id', 'departure_at', 'arrival_at', 'price', 'status'];

    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'arrival_at' => 'datetime',
            'price' => 'integer',
            'status' => ScheduleStatus::class,
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TravelRoute::class, 'route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function operator(): HasOneThrough
    {
        return $this->hasOneThrough(Operator::class, Vehicle::class, 'id', 'id', 'vehicle_id', 'operator_id');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(ScheduleStop::class)->orderBy('sort_order');
    }

    public function pickups(): HasMany
    {
        return $this->stops()->where('type', ScheduleStop::TYPE_PICKUP);
    }

    public function dropoffs(): HasMany
    {
        return $this->stops()->where('type', ScheduleStop::TYPE_DROPOFF);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function seatReservations(): HasMany
    {
        return $this->hasMany(SeatReservation::class);
    }

    /** Kursi yang sedang terpakai: sudah dibooking atau hold yang belum kedaluwarsa. */
    public function activeSeatReservations(): HasMany
    {
        return $this->seatReservations()->active();
    }

    /** Jadwal yang masih bisa dipesan: terjadwal dan belum berangkat. */
    public function scopeBookable(Builder $query): void
    {
        $query->where('status', ScheduleStatus::Scheduled)->where('departure_at', '>', now());
    }

    public function scopeOnDate(Builder $query, Carbon $date): void
    {
        $query->whereBetween('departure_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);
    }

    public function scopeWithSeatsTaken(Builder $query): void
    {
        $query->withCount(['activeSeatReservations as seats_taken']);
    }

    /** Dihitung, tidak disimpan: kapasitas tipe kendaraan dikurangi kursi aktif. */
    protected function seatsLeft(): Attribute
    {
        return Attribute::get(function (): int {
            $capacity = $this->vehicle->vehicleType->capacity;

            return max(0, $capacity - $this->countSeatsTaken());
        });
    }

    protected function durationMinutes(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->departure_at->diffInMinutes($this->arrival_at));
    }

    private function countSeatsTaken(): int
    {
        if (array_key_exists('seats_taken', $this->attributes)) {
            return (int) $this->attributes['seats_taken'];
        }

        if ($this->relationLoaded('activeSeatReservations')) {
            return $this->activeSeatReservations->count();
        }

        return $this->activeSeatReservations()->count();
    }
}
