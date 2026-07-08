<?php

use App\Enums\Gender;
use App\Models\Worker;
use App\Services\Reports\WorkersReportService;

it('summarizes the worker roster by status, standby, and gender', function () {
    Worker::factory()->gender(Gender::Male)->create(['is_active' => true, 'standby' => true]);
    Worker::factory()->gender(Gender::Female)->create(['is_active' => true, 'standby' => false]);
    Worker::factory()->gender(Gender::Female)->inactive()->create();

    $summary = (new WorkersReportService())->summary();

    expect($summary)->toMatchArray([
        'total_workers' => 3,
        'active_workers' => 2,
        'inactive_workers' => 1,
        'standby_workers' => 1,
    ]);
    expect($summary['by_gender']['female'])->toBe(2);
    expect($summary['by_gender']['male'])->toBe(1);
});
