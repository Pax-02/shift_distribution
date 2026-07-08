<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Nullable: system/queue-triggered changes (e.g. worker_hours
            // recalculated by a listener) have no acting user.
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action'); // e.g. "created", "updated", "deleted", "allocated"

            // Polymorphic target of the change, e.g. Worker/Shift/Assignment.
            $table->string('auditable_type');
            $table->uuid('auditable_id');

            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // created_at only — audit rows are immutable, never updated.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
