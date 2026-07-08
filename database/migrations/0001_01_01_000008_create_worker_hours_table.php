<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();

            $table->date('week_start_date'); // Monday of the ISO week

            // Snapshot of workers.contracted_weekly_hours at aggregation time,
            // not a live join — keeps history accurate if a worker's contract
            // changes later without rewriting past weeks.
            $table->decimal('contracted_hours', 5, 2);

            // Upserted by RecalculateWorkerHours whenever an assignment for
            // this worker/week changes, so allocation scoring rules read a
            // precomputed total instead of re-summing assignments each time.
            $table->decimal('allocated_hours', 6, 2)->default(0);
            $table->decimal('worked_hours', 6, 2)->nullable(); // future timesheet integration

            $table->timestamps();

            $table->unique(['worker_id', 'week_start_date']);
            $table->index('week_start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_hours');
    }
};
