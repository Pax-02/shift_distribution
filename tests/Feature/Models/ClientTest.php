<?php

use App\Models\Client;
use App\Models\ClientAddress;

it('has many addresses', function () {
    $client = Client::factory()->create();
    ClientAddress::factory()->count(2)->create(['client_id' => $client->id]);

    expect($client->addresses)->toHaveCount(2);
});

it('address belongs to a client', function () {
    $address = ClientAddress::factory()->create();

    expect($address->client)->toBeInstanceOf(Client::class);
});

it('rejects a worker_availability row where end_time is not after start_time', function () {
    $worker = \App\Models\Worker::factory()->create();

    \Illuminate\Support\Facades\DB::table('worker_availability')->insert([
        'id' => (string) \Illuminate\Support\Str::uuid7(),
        'worker_id' => $worker->id,
        'type' => 'recurring',
        'day_of_week' => 1,
        'start_time' => '17:00',
        'end_time' => '08:00',
        'is_available' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(\Illuminate\Database\QueryException::class);
