<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->enum('channel', ['email', 'sms', 'whatsapp', 'manual'])->default('manual');
            $table->text('raw_message');
            $table->string('sender_identifier')->nullable(); // phone/email of sender

            // Validated AI JSON conforming to the parse contract (client,
            // date, pickup, destination, start_time, end_time, gender_requirement).
            $table->jsonb('parsed_payload')->nullable();
            $table->enum('parse_status', ['pending', 'parsed', 'validated', 'failed', 'rejected'])->default('pending');
            $table->text('parse_error')->nullable();
            $table->string('ai_model')->nullable(); // e.g. "mock-v1", later a real Claude model id

            // FK to shifts is added in a later migration (0001_01_01_000011)
            // once the shifts table exists — shifts.source_message_id also
            // points back here, so the two tables have a circular reference.
            $table->uuid('shift_id')->nullable();

            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('parse_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_messages');
    }
};
