import type { CreateShiftMessagePayload, ShiftMessage } from '../types/shiftMessage';
import { apiFetch } from './client';

// Runs synchronously server-side (QUEUE_CONNECTION=sync) — the response
// already carries the final parse_status/parsed_payload/shift_id, no
// polling needed.
export function createShiftMessage(payload: CreateShiftMessagePayload): Promise<{ data: ShiftMessage }> {
    return apiFetch<{ data: ShiftMessage }>('/shift-messages', { method: 'POST', body: JSON.stringify(payload) });
}
