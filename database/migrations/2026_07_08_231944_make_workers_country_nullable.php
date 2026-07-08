<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * workers.country was created NOT NULL (with only a default) while
     * StoreWorkerRequest/UpdateWorkerRequest validate it as `nullable` and
     * WorkerResource types it `string|null` — any client that omits country
     * (or sends it explicitly null, which the validated payload always
     * does) hits a NOT NULL constraint violation. Raw SQL, not
     * Schema::table()->change(), since this repo doesn't depend on
     * doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE workers ALTER COLUMN country DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE workers ALTER COLUMN country SET NOT NULL");
    }
};
