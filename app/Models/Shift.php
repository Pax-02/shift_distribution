<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Enums\GenderRequirement;
use App\Enums\ShiftStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use Auditable, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id', 'pickup_address_id', 'pickup_address_text',
        'destination_address_id', 'destination_address_text',
        'shift_date', 'start_time', 'end_time', 'gender_requirement', 'status',
        'source_message_id', 'created_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'gender_requirement' => GenderRequirement::class,
            'status' => ShiftStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pickupAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class, 'pickup_address_id');
    }

    public function destinationAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class, 'destination_address_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(ShiftMessage::class, 'source_message_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The single confirmed assignment for this shift, if one exists — the partial unique index guarantees at most one. */
    public function confirmedAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->where('status', 'confirmed');
    }
}
