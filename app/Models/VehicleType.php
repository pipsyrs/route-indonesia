<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    use SoftDeletes;

    /** Sel denah untuk posisi sopir. */
    public const DRIVER_CELL = 'D';

    protected $fillable = ['name', 'capacity', 'seat_layout'];

    protected function casts(): array
    {
        return [
            'seat_layout' => 'array',
            'capacity' => 'integer',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_vehicle_type')->orderBy('facilities.id');
    }

    /** Nomor kursi penumpang dari denah (tanpa sopir dan lorong). */
    protected function seatNumbers(): Attribute
    {
        return Attribute::get(function (): array {
            $cells = array_merge(...($this->seat_layout ?? [[]]));

            return array_values(array_filter(
                $cells,
                fn ($cell) => is_string($cell) && $cell !== self::DRIVER_CELL,
            ));
        });
    }
}
