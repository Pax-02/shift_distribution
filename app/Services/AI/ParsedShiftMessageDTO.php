<?php

namespace App\Services\AI;

/**
 * Structured output of AIParser::parseMessage(), matching the JSON contract
 * in the spec (client, date, pickup, destination, start_time, end_time,
 * gender_requirement) plus confidence/raw for debugging. Fields are nullable
 * because a parser (mock or real) may fail to extract some of them — that's
 * exactly what ParsedMessageValidator checks before anything is persisted.
 */
final class ParsedShiftMessageDTO
{
    public function __construct(
        public readonly ?string $client,
        public readonly ?string $date,
        public readonly ?string $pickup,
        public readonly ?string $destination,
        public readonly ?string $startTime,
        public readonly ?string $endTime,
        public readonly ?string $genderRequirement,
        public readonly float $confidence,
        public readonly string $raw,
    ) {}

    /** Field names match the spec's JSON contract, not this class's camelCase properties. */
    public function toArray(): array
    {
        return [
            'client' => $this->client,
            'date' => $this->date,
            'pickup' => $this->pickup,
            'destination' => $this->destination,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'gender_requirement' => $this->genderRequirement,
            'confidence' => $this->confidence,
        ];
    }
}
