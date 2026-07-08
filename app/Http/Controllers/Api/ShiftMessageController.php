<?php

namespace App\Http\Controllers\Api;

use App\Events\ShiftMessageReceived;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftMessage\StoreShiftMessageRequest;
use App\Http\Resources\ShiftMessageResource;
use App\Jobs\ParseShiftMessageJob;
use App\Models\ShiftMessage;

class ShiftMessageController extends Controller
{
    /** Ingests a raw inbound message and queues it for AI parsing (see ParseShiftMessageJob). */
    public function store(StoreShiftMessageRequest $request): ShiftMessageResource
    {
        $message = ShiftMessage::create($request->validated() + ['received_at' => now()]);

        event(new ShiftMessageReceived($message));

        ParseShiftMessageJob::dispatch($message);

        // The job (de)serializes its own ShiftMessage instance — even on the
        // sync queue driver — so it never mutates $message directly. Reload
        // to pick up both DB-level column defaults (channel, parse_status)
        // and whatever the job just wrote.
        return new ShiftMessageResource($message->refresh());
    }
}
