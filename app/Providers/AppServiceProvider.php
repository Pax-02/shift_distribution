<?php

namespace App\Providers;

use App\Events\AssignmentCancelled;
use App\Events\AssignmentCreated;
use App\Events\ShiftAllocated;
use App\Listeners\LogAllocationRun;
use App\Listeners\RecalculateWorkerHours;
use App\Listeners\SendAssignmentNotification;
use App\Listeners\SendCancellationNotification;
use App\Policies\AllocationPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // AllocationPolicy doesn't map 1:1 to a single Eloquent model the way
        // WorkerPolicy/ClientPolicy/ShiftPolicy do (those are auto-discovered
        // by naming convention), so its abilities are registered explicitly.
        Gate::define('runAllocation', [AllocationPolicy::class, 'run']);
        Gate::define('viewAllocationHistory', [AllocationPolicy::class, 'viewHistory']);
        Gate::define('viewReports', [ReportPolicy::class, 'view']);

        // The app skeleton's AppServiceProvider doesn't extend
        // Illuminate\Foundation\Support\Providers\EventServiceProvider, so
        // listener discovery isn't automatic — registered explicitly here.
        Event::listen(AssignmentCreated::class, RecalculateWorkerHours::class);
        Event::listen(AssignmentCancelled::class, RecalculateWorkerHours::class);
        Event::listen(AssignmentCreated::class, SendAssignmentNotification::class);
        Event::listen(AssignmentCancelled::class, SendCancellationNotification::class);
        Event::listen(ShiftAllocated::class, LogAllocationRun::class);
    }
}
