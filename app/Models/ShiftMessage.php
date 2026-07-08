<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Enums\MessageChannel;
use App\Enums\ParseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftMessage extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'channel', 'raw_message', 'sender_identifier', 'parsed_payload', 'parse_status',
        'parse_error', 'ai_model', 'shift_id', 'processed_by', 'received_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => MessageChannel::class,
            'parsed_payload' => 'array',
            'parse_status' => ParseStatus::class,
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
