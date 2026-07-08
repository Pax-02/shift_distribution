<?php

namespace App\Services\AI;

/**
 * Extracts structured shift data from a free-text inbound message. Bound in
 * AIServiceProvider via config('services.ai.parser_driver') — swapping the
 * mock stub for a real Claude-backed implementation later is a one-line
 * .env change, not a call-site change.
 */
interface AIParserInterface
{
    public function parseMessage(string $message): ParsedShiftMessageDTO;

    /** Identifier recorded on shift_messages.ai_model, e.g. "mock-v1" or a real Claude model id. */
    public function modelIdentifier(): string;
}
