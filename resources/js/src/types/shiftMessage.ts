import type { MessageChannel, ParseStatus } from './enums';

// Mirrors app/Http/Resources/ShiftMessageResource.php.
export interface ShiftMessage {
    id: string;
    channel: MessageChannel | null;
    raw_message: string;
    sender_identifier: string | null;
    parsed_payload: Record<string, unknown> | null;
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
