<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerHours extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id', 'week_start_date', 'contracted_hours', 'allocated_hours', 'worked_hours',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'contracted_hours' => 'decimal:2',
            'allocated_hours' => 'decimal:2',
            'worked_hours' => 'decimal:2',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
