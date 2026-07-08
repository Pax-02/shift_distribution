import { clearToken, getToken } from './tokenStorage';

const API_URL = import.meta.env.VITE_API_URL;

// Laravel validation-exception body shape: { message, errors: { field: [msg] } }.
export class ApiError extends Error {
    readonly status: number;
    readonly body: unknown;
    readonly errors?: Record<string, string[]>;

    constructor(status: number, body: unknown) {
        const message =
            typeof body === 'object' && body !== null && 'message' in body && typeof body.message === 'string'
                ? body.message
                : `Request failed with status ${status}`;
        super(message);
        this.status = status;
        this.body = body;
        this.errors =
            typeof body === 'object' && body !== null && 'errors' in body
                ? (body.errors as Record<string, string[]>)
                : undefined;
    }
}

// Thin fetch wrapper: attaches the bearer token, centralizes the base URL,
// and normalizes error handling. Deliberately takes a raw path/options
// rather than baking in REST conventions (list/create/update/delete) — that
// convention layer belongs in per-entity API modules built in later steps.
export async function apiFetch<T>(path: string, options: RequestInit = {}): Promise<T> {
    const headers = new Headers(options.headers);
    headers.set('Accept', 'application/json');
    if (options.body && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }
    const token = getToken();
    if (token) {
        headers.set('Authorization', `Bearer ${token}`);
    }

    const response = await fetch(`${API_URL}${path}`, { ...options, headers });

    if (response.status === 401) {
        // Session is gone or was never valid — there's no page state worth
        // preserving yet (no in-SPA return-to-here redirect until a later
        // step has more than one protected page), so a hard redirect is the
        // simplest correct behavior.
        clearToken();
        window.location.href = '/login';
        throw new ApiError(401, { message: 'Unauthenticated' });
    }

    const isJson = response.headers.get('content-type')?.includes('application/json') ?? false;
    const body = isJson ? await response.json() : null;

    if (!response.ok) {
        throw new ApiError(response.status, body);
    }

    return body as T;
}
