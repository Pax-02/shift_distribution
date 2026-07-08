<?php

use App\Models\Worker;
use App\Models\WorkerHours;
use App\Services\Reports\HoursReportService;
use Illuminate\Support\Carbon;

it('reports contracted vs allocated hours with a utilization ratio for the given week', function () {
    $worker = Worker::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe', 'contracted_weekly_hours' => 20]);
    $weekStart = Carbon::parse('2026-08-10')->startOfWeek(Carbon::MONDAY)->toDateString();

    WorkerHours::factory()->create([
        'worker_id' => $worker->id,
        'week_start_date' => $weekStart,
        'contracted_hours' => 20,
        'allocated_hours' => 15,
    ]);

    $report = (new HoursReportService())->forWeek('2026-08-12'); // any day in that week

    expect($report)->toHaveCount(1);
    expect($report->first())->toMatchArray([
        'worker_id' => $worker->id,
        'worker_name' => 'Jane Doe',
        'contracted_hours' => 20.0,
        'allocated_hours' => 15.0,
        'utilization' => 0.75,
    ]);
});

it('defaults to the current week when none is given', function () {
    Carbon::setTestNow('2026-08-12'); // Wednesday
    $worker = Worker::factory()->create();

    WorkerHours::factory()->create([
        'worker_id' => $worker->id,
        'week_start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString(),
    ]);

    expect((new HoursReportService())->forWeek())->toHaveCount(1);

    Carbon::setTestNow();
});
