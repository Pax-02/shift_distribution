// Mirrors app/Http/Resources/ClientAddressResource.php.
export interface ClientAddress {
    id: string;
    label: string | null;
    line1: string;
    line2: string | null;
    city: string | null;
    postcode: string | null;
    country: string | null;
    // Decimal columns are serialized as strings by Laravel, same as
    // contracted_weekly_hours (e.g. "51.5810230").
    latitude: string | null;
    longitude: string | null;
    is_primary: boolean;
}

// Mirrors app/Http/Resources/ClientResource.php. `addresses` is only
// present when the controller eager-loads the relation.
export interface Client {
    id: string;
    name: string;
    contact_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    is_active: boolean;
    notes: string | null;
    addresses?: ClientAddress[];
    created_at: string;
    updated_at: string;
}

// Mirrors StoreClientRequest/UpdateClientRequest.
export interface ClientPayload {
    name: string;
    contact_name?: string | null;
    contact_email?: string | null;
    contact_phone?: string | null;
    is_active?: boolean;
    notes?: string | null;
}
