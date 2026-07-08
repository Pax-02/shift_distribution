<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->restrictOnDelete();

            // Resolved address if the AI parser (or a dispatcher) matched it to
            // a known client_addresses row; the *_text columns are the raw
            // fallback when no match was found.
            $table->foreignUuid('pickup_address_id')->nullable()->constrained('client_addresses')->nullOnDelete();
            $table->string('pickup_address_text')->nullable();
            $table->foreignUuid('destination_address_id')->nullable()->constrained('client_addresses')->nullOnDelete();
            $table->string('destination_address_text')->nullable();

            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->enum('gender_requirement', ['any', 'male', 'female'])->default('any');
            $table->enum('status', ['pending', 'allocated', 'confirmed', 'completed', 'cancelled'])->default('pending');

            $table->foreignUuid('source_message_id')->nullable()->constrained('shift_messages')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Dominant query pattern: "what needs allocating on date X".
            $table->index(['shift_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
