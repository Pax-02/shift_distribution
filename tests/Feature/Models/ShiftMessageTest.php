<?php

use App\Enums\ParseStatus;
use App\Models\Shift;
use App\Models\ShiftMessage;

it('casts parse_status to an enum and parsed_payload to an array', function () {
    $message = ShiftMessage::factory()->parsed([
        'client' => 'Acme Care',
        'date' => '2026-08-12',
    ])->create();

    expect($message->parse_status)->toBe(ParseStatus::Validated)
        ->and($message->parsed_payload)->toBeArray()
        ->and($message->parsed_payload['client'])->toBe('Acme Care');
});

it('links to the shift created from it', function () {
    $shift = Shift::factory()->create();
    $message = ShiftMessage::factory()->create(['shift_id' => $shift->id]);

    expect($message->shift->is($shift))->toBeTrue();
});
