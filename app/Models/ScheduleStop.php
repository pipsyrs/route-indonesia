<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleStop extends Model
{
    public const TYPE_PICKUP = 'pickup';

    public const TYPE_DROPOFF = 'dropoff';

    protected $fillable = ['schedule_id', 'location_id', 'type', 'stop_at', 'sort_order'];

    protected function casts(): array
    {
        return [
            'stop_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /** withTrashed: titik yang sudah dihapus tetap tampil di riwayat perjalanan. */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class)->withTrashed();
    }
}
