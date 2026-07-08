<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\JourneyPreference;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worker>
 */
class WorkerFactory extends Factory
{
    protected $model = Worker::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(Gender::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'address_line1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postcode' => fake()->postcode(),
            'country' => 'GB',
            'home_latitude' => fake()->latitude(51.3, 51.7),
            'home_longitude' => fake()->longitude(-0.5, 0.3),
            'contracted_weekly_hours' => fake()->randomElement([16, 20, 24, 30, 37.5]),
            'max_weekly_hours' => null,
            'standby' => false,
            'journey_preference' => fake()->randomElement(JourneyPreference::cases()),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function standby(): static
    {
        return $this->state(fn () => ['standby' => true]);
    }

    public function gender(Gender $gender): static
    {
        return $this->state(fn () => ['gender' => $gender]);
    }
}
