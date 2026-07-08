<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_messages', function (Blueprint $table) {
            // Set once a Shift is created from this message. Added here,
            // after shifts exists, to close the circular FK between the two.
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shift_messages', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });
    }
};
