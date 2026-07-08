<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** Enough clients/workers/shifts to exercise the allocation engine locally. */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::factory()->count(3)->create();

        $addressesByClient = $clients->mapWithKeys(fn (Client $client) => [
            $client->id => ClientAddress::factory()->count(2)->create(['client_id' => $client->id]),
        ]);

        $workers = collect()
            ->concat(Worker::factory()->count(4)->create()) // any gender/preference, per factory defaults
            ->concat(Worker::factory()->standby()->count(2)->create())
            ->concat(Worker::factory()->inactive()->count(1)->create()); // shouldn't be allocated — visible in reports

        $workers->each(function (Worker $worker) {
            // Available every weekday, so demo allocation runs have eligible candidates.
            foreach (range(1, 5) as $dayOfWeek) {
                WorkerAvailability::factory()->create([
                    'worker_id' => $worker->id,
                    'type' => 'recurring',
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '07:00',
                    'end_time' => '19:00',
                ]);
            }
        });

        foreach (range(1, 5) as $daysAhead) {
            $client = $clients->random();
            $addresses = $addressesByClient[$client->id];

            Shift::factory()->create([
                'client_id' => $client->id,
                'pickup_address_id' => $addresses->first()->id,
                'destination_address_id' => $addresses->last()->id,
                'shift_date' => Carbon::now()->addDays($daysAhead)->toDateString(),
            ]);
        }
    }
}
