import type { Gender, JourneyPreference } from './enums';

// Mirrors app/Http/Resources/WorkerResource.php's read shape.
export interface Worker {
    id: string;
    first_name: string;
    last_name: string;
    email: string | null;
    phone: string | null;
    gender: Gender | null;
    date_of_birth: string | null;
    address: {
        line1: string | null;
        line2: string | null;
        city: string | null;
        postcode: string | null;
        country: string | null;
        latitude: number | null;
        longitude: number | null;
    };
    // Decimal columns are serialized as strings by Laravel (e.g. "37.50").
    contracted_weekly_hours: string;
    max_weekly_hours: string | null;
    standby: boolean;
    journey_preference: JourneyPreference | null;
    preferred_client_ids: string[] | null;
    avoided_client_ids: string[] | null;
    is_active: boolean;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

// Mirrors StoreWorkerRequest/UpdateWorkerRequest — field names are FLAT
// here (address_line1, home_latitude, ...) unlike the nested `address` on
// the read-side Worker type above; a form-layer mapping is needed between
// the two shapes when a later step builds the worker create/edit form.
export interface WorkerPayload {
    first_name: string;
    last_name: string;
    email?: string | null;
    phone?: string | null;
    gender: Gender;
    date_of_birth?: string | null;
    address_line1?: string | null;
    address_line2?: string | null;
    city?: string | null;
    postcode?: string | null;
    country?: string | null;
    home_latitude?: number | null;
    home_longitude?: number | null;
    contracted_weekly_hours: number;
    max_weekly_hours?: number | null;
    standby?: boolean;
    journey_preference?: JourneyPreference | null;
    preferred_client_ids?: string[] | null;
    avoided_client_ids?: string[] | null;
    is_active?: boolean;
    notes?: string | null;
}
