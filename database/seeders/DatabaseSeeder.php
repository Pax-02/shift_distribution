<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. RoleSeeder/UserSeeder are safe to
     * re-run (updateOrCreate); DemoDataSeeder is not — it always inserts
     * fresh rows, so it's meant for a clean `migrate:fresh --seed`.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
