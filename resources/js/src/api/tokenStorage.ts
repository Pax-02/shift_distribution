// Bearer token persistence, shared by the API client (for attaching the
// Authorization header and clearing on 401) and, from step (c) onward, the
// auth context (for persisting the session across page loads). LocalStorage
// is the only option available without changing the backend's auth mode —
// the API is intentionally pure bearer-token (supports_credentials: false),
// not cookie/CSRF SPA mode, so there is no httpOnly-cookie alternative here.
const TOKEN_KEY = 'shift_distribution.token';

export function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
    localStorage.removeItem(TOKEN_KEY);
}
