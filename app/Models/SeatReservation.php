<?php

namespace App\Models;

use App\Enums\SeatStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatReservation extends Model
{
    protected $fillable = ['schedule_id', 'seat_number', 'booking_id', 'hold_token', 'status', 'held_until'];

    protected $hidden = ['hold_token'];

    protected function casts(): array
    {
        return [
            'held_until' => 'datetime',
            'status' => SeatStatus::class,
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Kursi terpakai: sudah dibooking, atau masih dalam masa hold. */
    public function scopeActive(Builder $query): void
    {
        $query->where(fn (Builder $inner) => $inner
            ->where('status', SeatStatus::Booked)
            ->orWhere(fn (Builder $held) => $held
                ->where('status', SeatStatus::Held)
                ->where('held_until', '>', now())));
    }
}
