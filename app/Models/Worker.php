<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Enums\Gender;
use App\Enums\JourneyPreference;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Worker extends Model
{
    use Auditable, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'gender', 'date_of_birth',
        'address_line1', 'address_line2', 'city', 'postcode', 'country',
        'home_latitude', 'home_longitude',
        'contracted_weekly_hours', 'max_weekly_hours', 'standby', 'journey_preference',
        'preferred_client_ids', 'avoided_client_ids', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'journey_preference' => JourneyPreference::class,
            'date_of_birth' => 'date',
            'home_latitude' => 'decimal:7',
            'home_longitude' => 'decimal:7',
            'contracted_weekly_hours' => 'decimal:2',
            'max_weekly_hours' => 'decimal:2',
            'standby' => 'boolean',
            'preferred_client_ids' => 'array',
            'avoided_client_ids' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function availability(): HasMany
    {
        return $this->hasMany(WorkerAvailability::class);
    }

    public function hours(): HasMany
    {
        return $this->hasMany(WorkerHours::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
