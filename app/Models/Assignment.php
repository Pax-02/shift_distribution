<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'shift_id', 'worker_id', 'status', 'allocation_score', 'score_breakdown',
        'assigned_by', 'hours', 'assigned_at', 'confirmed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'allocation_score' => 'decimal:4',
            'score_breakdown' => 'array',
            'hours' => 'decimal:2',
            'assigned_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
