<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_availability', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();

            // "recurring" = weekly pattern (day_of_week); "date_specific" =
            // one-off availability or block (specific_date), e.g. holidays.
            $table->enum('type', ['recurring', 'date_specific']);
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0=Sun..6=Sat
            $table->date('specific_date')->nullable();

            $table->time('start_time');
            $table->time('end_time');

            // Allows explicit "blocked" windows, not just "available" ones —
            // e.g. a date_specific row with is_available=false for a holiday.
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->index(['worker_id', 'day_of_week']);
            $table->index(['worker_id', 'specific_date']);
        });

        // Postgres-specific check constraint: enforce ordering at the DB
        // layer, not just in application validation.
        DB::statement('ALTER TABLE worker_availability ADD CONSTRAINT worker_availability_time_order CHECK (start_time < end_time)');
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_availability');
    }
};
