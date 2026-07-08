<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'pickup_address_text' => fake()->streetAddress(),
            'destination_address_text' => fake()->streetAddress(),
            'shift_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'gender_requirement' => 'any',
            'status' => 'pending',
        ];
    }

    public function genderRequirement(string $gender): static
    {
        return $this->state(fn () => ['gender_requirement' => $gender]);
    }

    public function on(string $date): static
    {
        return $this->state(fn () => ['shift_date' => $date]);
    }

    public function between(string $start, string $end): static
    {
        return $this->state(fn () => ['start_time' => $start, 'end_time' => $end]);
    }
}
