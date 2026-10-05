<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    public const TYPE_POOL = 'pool';

    public const TYPE_REST_AREA = 'rest_area';

    public const TYPE_POINT = 'point';

    protected $fillable = ['city_id', 'name', 'address', 'latitude', 'longitude', 'type'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scheduleStops(): HasMany
    {
        return $this->hasMany(ScheduleStop::class);
    }
}
