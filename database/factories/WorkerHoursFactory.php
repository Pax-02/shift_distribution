<?php

namespace Database\Factories;

use App\Models\Worker;
use App\Models\WorkerHours;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WorkerHours>
 */
class WorkerHoursFactory extends Factory
{
    protected $model = WorkerHours::class;

    public function definition(): array
    {
        return [
            'worker_id' => Worker::factory(),
            'week_start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString(),
            'contracted_hours' => 20,
            'allocated_hours' => 0,
            'worked_hours' => null,
        ];
    }
}
