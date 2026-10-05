<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rute antarkota (tabel `routes`). Tidak dinamai `Route` agar tidak
 * bentrok dengan facade Illuminate\Support\Facades\Route.
 */
class TravelRoute extends Model
{
    use SoftDeletes;

    protected $table = 'routes';

    protected $fillable = ['origin_city_id', 'destination_city_id', 'estimated_duration_minutes', 'base_price', 'is_popular'];

    protected function casts(): array
    {
        return [
            'is_popular' => 'boolean',
            'base_price' => 'integer',
            'estimated_duration_minutes' => 'integer',
        ];
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'route_id');
    }

    public function scopePopular(Builder $query): void
    {
        $query->where('is_popular', true);
    }

    public function scopeBetween(Builder $query, int $originId, int $destinationId): void
    {
        $query->where('origin_city_id', $originId)->where('destination_city_id', $destinationId);
    }
}
