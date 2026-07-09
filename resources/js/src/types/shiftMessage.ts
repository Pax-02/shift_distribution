import type { GenderRequirement, MessageChannel, ParseStatus } from './enums';

// Mirrors ParsedShiftMessageDTO::toArray() — every field nullable except
// confidence, since the parser (mock or real) may fail to extract any of
// them; that's exactly what ParsedMessageValidator checks before a Shift
// gets created from it.
export interface ParsedShiftMessagePayload {
    client: string | null;
    date: string | null;
    pickup: string | null;
    destination: string | null;
    start_time: string | null;
    end_time: string | null;
    gender_requirement: GenderRequirement | null;
    confidence: number;
}

// Mirrors app/Http/Resources/ShiftMessageResource.php.
export interface ShiftMessage {
    id: string;
    channel: MessageChannel | null;
    raw_message: string;
    sender_identifier: string | null;
    parsed_payload: ParsedShiftMessagePayload | null;
    parse_status: ParseStatus | null;
    parse_error: string | null;
    ai_model: string | null;
    shift_id: string | null;
    received_at: string | null;
    processed_at: string | null;
}

// Mirrors StoreShiftMessageRequest.
export interface CreateShiftMessagePayload {
    channel?: MessageChannel;
    raw_message: string;
    sender_identifier?: string | null;
}
