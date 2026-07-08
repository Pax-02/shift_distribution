<?php

namespace Database\Factories;

use App\Models\Worker;
use App\Models\WorkerAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkerAvailability>
 */
class WorkerAvailabilityFactory extends Factory
{
    protected $model = WorkerAvailability::class;

    public function definition(): array
    {
        return [
            'worker_id' => Worker::factory(),
            'type' => 'recurring',
            'day_of_week' => fake()->numberBetween(0, 6),
            'specific_date' => null,
            'start_time' => '08:00',
            'end_time' => '17:00',
            'is_available' => true,
        ];
    }

    public function dateSpecific(string $date): static
    {
        return $this->state(fn () => [
            'type' => 'date_specific',
            'day_of_week' => null,
            'specific_date' => $date,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn () => ['is_available' => false]);
    }
}
