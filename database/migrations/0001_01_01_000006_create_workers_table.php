<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();

            // Used by GenderRequirementRule.
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say']);
            $table->date('date_of_birth')->nullable();

            // Home base — used as the "previous location" fallback for the
            // first shift of a day in DistanceFromPreviousShiftRule.
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->default('GB');
            $table->decimal('home_latitude', 10, 7)->nullable();
            $table->decimal('home_longitude', 10, 7)->nullable();

            // Used by WeeklyContractHourFairnessRule / MaximumWeeklyHoursRule.
            $table->decimal('contracted_weekly_hours', 5, 2)->default(0);
            $table->decimal('max_weekly_hours', 5, 2)->nullable();

            $table->boolean('standby')->default(false);
            $table->enum('journey_preference', ['short', 'long', 'any'])->nullable();

            // Preferred/avoided client ids for ClientPreferenceRule. A JSON
            // column is enough for a phase-1 soft-scoring signal; if this
            // needs richer per-pair metadata later, promote it to a
            // worker_client_preferences pivot table without touching the
            // rule's public interface.
            $table->jsonb('preferred_client_ids')->nullable();
            $table->jsonb('avoided_client_ids')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('gender');
            $table->index('standby');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
