<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerAvailability extends Model
{
    use HasFactory, HasUuids;

    // Eloquent's default pluralization would guess "worker_availabilities";
    // the migration/spec use the singular "worker_availability".
    protected $table = 'worker_availability';

    protected $fillable = [
        'worker_id', 'type', 'day_of_week', 'specific_date', 'start_time', 'end_time', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'specific_date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
