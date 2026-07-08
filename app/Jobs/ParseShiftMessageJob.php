<?php

namespace App\Jobs;

use App\Events\ShiftCreated;
use App\Events\ShiftMessageParsed;
use App\Models\Client;
use App\Models\Shift;
use App\Models\ShiftMessage;
use App\Services\AI\AIParserInterface;
use App\Services\AI\ParsedMessageValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs AIParser::parseMessage() against a stored ShiftMessage, validates the
 * result, and — when it validates and the client name resolves to a known
 * Client — creates the Shift. QUEUE_CONNECTION=sync for now, so this runs
 * inline; nothing here assumes synchronous execution though.
 */
class ParseShiftMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly ShiftMessage $shiftMessage) {}

    public function handle(AIParserInterface $parser, ParsedMessageValidator $validator): void
    {
        $dto = $parser->parseMessage($this->shiftMessage->raw_message);
        $errors = $validator->validate($dto);

        if (! $errors->isEmpty()) {
            $this->shiftMessage->update([
                'parse_status' => 'failed',
                'parse_error' => $errors->first(),
                'ai_model' => $parser->modelIdentifier(),
                'processed_at' => now(),
            ]);

            return;
        }

        $this->shiftMessage->update([
            'parsed_payload' => $dto->toArray(),
            'parse_status' => 'validated',
            'ai_model' => $parser->modelIdentifier(),
            'processed_at' => now(),
        ]);

        event(new ShiftMessageParsed($this->shiftMessage));

        // Naive exact-name match for phase 1 — good enough for the mock
        // parser's output. A real parser/integration would likely need
        // fuzzy matching or a dispatcher confirmation step here.
        $client = Client::where('name', $dto->client)->first();

        if ($client === null) {
            return;
        }

        $shift = Shift::create([
            'client_id' => $client->id,
            'pickup_address_text' => $dto->pickup,
            'destination_address_text' => $dto->destination,
            'shift_date' => $dto->date,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
            'gender_requirement' => $dto->genderRequirement,
            'source_message_id' => $this->shiftMessage->id,
        ]);

        $this->shiftMessage->update(['shift_id' => $shift->id]);

        event(new ShiftCreated($shift));
    }
}
