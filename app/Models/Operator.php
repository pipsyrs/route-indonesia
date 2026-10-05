<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'logo_path', 'phone', 'rating_average', 'rating_count', 'is_verified'];

    protected function casts(): array
    {
        return [
            // float, bukan decimal:1, agar view menerima angka (bukan string "4.8").
            'rating_average' => 'float',
            'rating_count' => 'integer',
            'is_verified' => 'boolean',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function schedules(): HasManyThrough
    {
        return $this->hasManyThrough(Schedule::class, Vehicle::class);
    }
}
