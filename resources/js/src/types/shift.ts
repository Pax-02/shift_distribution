import type { GenderRequirement, ShiftStatus } from './enums';

// Mirrors app/Http/Resources/ShiftResource.php. No relations are
// eager-loaded here — fetch client/addresses separately if needed.
export interface Shift {
    id: string;
    client_id: string;
    pickup_address_id: string | null;
    pickup_address_text: string | null;
    destination_address_id: string | null;
    destination_address_text: string | null;
    shift_date: string;
    // Serialized as "HH:MM:SS" on read, but the H:i validation rule on
    // write (CreateShiftPayload/UpdateShiftPayload below) expects "HH:MM".
    start_time: string;
    end_time: string;
    gender_requirement: GenderRequirement | null;
    status: ShiftStatus;
    source_message_id: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

// Mirrors StoreShiftRequest.
export interface CreateShiftPayload {
    client_id: string;
    pickup_address_id?: string | null;
    pickup_address_text?: string | null;
    destination_address_id?: string | null;
    destination_address_text?: string | null;
    shift_date: string;
    start_time: string;
    end_time: string;
    gender_requirement?: GenderRequirement;
    notes?: string | null;
}

// Mirrors UpdateShiftRequest — same fields as create, all optional, plus status.
export type UpdateShiftPayload = Partial<CreateShiftPayload> & {
    status?: ShiftStatus;
};
