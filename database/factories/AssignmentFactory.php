<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Shift;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'shift_id' => Shift::factory(),
            'worker_id' => Worker::factory(),
            'status' => 'proposed',
            'assigned_at' => now(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'confirmed_at' => now()]);
    }

    public function withScore(float $total, array $breakdown = []): static
    {
        return $this->state(fn () => ['allocation_score' => $total, 'score_breakdown' => $breakdown]);
    }
}
