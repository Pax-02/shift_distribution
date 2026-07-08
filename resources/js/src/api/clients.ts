import type { PaginatedResponse } from '../types';
import type { Client, ClientPayload } from '../types/client';
import { apiFetch } from './client';

export interface ListClientsParams {
    page?: number;
}

export function listClients(params: ListClientsParams = {}): Promise<PaginatedResponse<Client>> {
    const query = params.page ? `?page=${params.page}` : '';
    return apiFetch<PaginatedResponse<Client>>(`/clients${query}`);
}

export function getClient(id: string): Promise<{ data: Client }> {
    return apiFetch<{ data: Client }>(`/clients/${id}`);
}

export function createClient(payload: ClientPayload): Promise<{ data: Client }> {
    return apiFetch<{ data: Client }>('/clients', { method: 'POST', body: JSON.stringify(payload) });
}

export function updateClient(id: string, payload: Partial<ClientPayload>): Promise<{ data: Client }> {
    return apiFetch<{ data: Client }>(`/clients/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
}

export function deleteClient(id: string): Promise<void> {
    return apiFetch<void>(`/clients/${id}`, { method: 'DELETE' });
}
