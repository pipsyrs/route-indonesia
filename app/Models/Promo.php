<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'discount_type', 'discount_value', 'max_discount',
        'min_transaction', 'valid_from', 'valid_until', 'rules', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'discount_value' => 'integer',
            'max_discount' => 'integer',
            'min_transaction' => 'integer',
            'is_active' => 'boolean',
            'discount_type' => DiscountType::class,
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->where(fn (Builder $inner) => $inner->whereNull('valid_from')->orWhere('valid_from', '<=', now()));
    }

    protected function code(): Attribute
    {
        return Attribute::set(fn (string $value) => strtoupper(trim($value)));
    }
}
