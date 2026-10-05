<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'user_id', 'schedule_id', 'pickup_stop_id', 'dropoff_stop_id', 'promo_id',
        'contact_name', 'contact_email', 'contact_phone',
        'subtotal', 'service_fee', 'discount_amount', 'total_amount', 'status', 'expires_at',
    ];

    /** Data kontak (PII) tidak ikut toArray()/JSON. */
    protected $hidden = ['contact_email', 'contact_phone'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'service_fee' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'expires_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** withTrashed: riwayat booking tetap tampil walau jadwalnya dihapus. */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
    }

    public function pickupStop(): BelongsTo
    {
        return $this->belongsTo(ScheduleStop::class, 'pickup_stop_id');
    }

    public function dropoffStop(): BelongsTo
    {
        return $this->belongsTo(ScheduleStop::class, 'dropoff_stop_id');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class)->withTrashed();
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function seatReservations(): HasMany
    {
        return $this->hasMany(SeatReservation::class);
    }

    public function testimonial(): HasOne
    {
        return $this->hasOne(Testimonial::class);
    }

    public function scopeByCode(Builder $query, string $code): void
    {
        $query->where('booking_code', strtoupper(trim($code)));
    }

    /** Disimpan dalam format 08xxx agar lookup Cek Pesanan konsisten. */
    protected function contactPhone(): Attribute
    {
        return Attribute::set(fn (string $value) => self::normalizePhone($value));
    }

    protected function contactEmail(): Attribute
    {
        return Attribute::set(fn (string $value) => strtolower(trim($value)));
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9+]/', '', $phone);

        return preg_replace('/^(\+62|62)/', '0', $digits);
    }
}
