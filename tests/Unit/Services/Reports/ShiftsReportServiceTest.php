<?php

use App\Models\Shift;
use App\Services\Reports\ShiftsReportService;

it('summarizes shift counts by status and the confirmed-allocation rate', function () {
    Shift::factory()->count(2)->create(['status' => 'pending', 'shift_date' => '2026-08-10']);
    Shift::factory()->create(['status' => 'confirmed', 'shift_date' => '2026-08-11']);
    Shift::factory()->create(['status' => 'cancelled', 'shift_date' => '2026-08-12']);

    $summary = (new ShiftsReportService())->summary();

    expect($summary['total_shifts'])->toBe(4)
        ->and($summary['by_status']['pending'])->toBe(2)
        ->and($summary['by_status']['confirmed'])->toBe(1)
        ->and($summary['confirmed_allocation_rate'])->toBe(0.25);
});

it('filters by date range', function () {
    Shift::factory()->create(['shift_date' => '2026-08-01']);
    Shift::factory()->create(['shift_date' => '2026-08-15']);

    $summary = (new ShiftsReportService())->summary('2026-08-10', '2026-08-20');

    expect($summary['total_shifts'])->toBe(1);
});

it('returns a null rate when there are no shifts in range', function () {
    $summary = (new ShiftsReportService())->summary();

    expect($summary['total_shifts'])->toBe(0)
        ->and($summary['confirmed_allocation_rate'])->toBeNull();
});
