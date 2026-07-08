import { apiFetch } from './client';
import type { User } from '../types';

export interface LoginResponse {
    token: string;
    user: User;
}

export function login(email: string, password: string): Promise<LoginResponse> {
    return apiFetch<LoginResponse>('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
    });
}

export function logout(): Promise<void> {
    return apiFetch<void>('/logout', { method: 'POST' });
}
