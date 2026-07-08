<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shift_id')->constrained('shifts')->cascadeOnDelete();

            // restrictOnDelete: a worker with assignment history can't be
            // hard-deleted (they're soft-deleted instead — see Worker model).
            $table->foreignUuid('worker_id')->constrained('workers')->restrictOnDelete();

            $table->enum('status', ['proposed', 'confirmed', 'declined', 'cancelled'])->default('proposed');

            // Snapshot of AllocationService's output at proposal time, so the
            // history stays meaningful even if rule weights change later.
            $table->decimal('allocation_score', 8, 4)->nullable();
            $table->jsonb('score_breakdown')->nullable();

            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('hours', 5, 2)->nullable();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index('shift_id');
            $table->index('worker_id');
            $table->index('status');
        });

        // A shift can accumulate many proposed/declined/cancelled assignment
        // rows over time (history), but only one CONFIRMED assignment at a
        // time. A plain unique(shift_id) would block that history; a partial
        // index scoped to status='confirmed' allows it.
        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX assignments_one_confirmed_per_shift
            ON assignments (shift_id) WHERE status = 'confirmed'
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
