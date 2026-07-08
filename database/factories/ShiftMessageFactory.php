<?php

namespace Database\Factories;

use App\Models\ShiftMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShiftMessage>
 */
class ShiftMessageFactory extends Factory
{
    protected $model = ShiftMessage::class;

    public function definition(): array
    {
        return [
            'channel' => 'manual',
            'raw_message' => 'Need a driver for Jane Doe on 12/08 from 09:00 to 11:00, pickup 12 High St, dropoff Willow Day Centre.',
            'sender_identifier' => fake()->safeEmail(),
            'parse_status' => 'pending',
            'received_at' => now(),
        ];
    }

    public function parsed(array $payload): static
    {
        return $this->state(fn () => [
            'parsed_payload' => $payload,
            'parse_status' => 'validated',
            'processed_at' => now(),
        ]);
    }

    public function failed(string $error): static
    {
        return $this->state(fn () => [
            'parse_status' => 'failed',
            'parse_error' => $error,
            'processed_at' => now(),
        ]);
    }
}
