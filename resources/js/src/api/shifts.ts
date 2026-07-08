import type { PaginatedResponse } from '../types';
import type { CreateShiftPayload, Shift, UpdateShiftPayload } from '../types/shift';
import { apiFetch } from './client';

export interface ListShiftsParams {
    page?: number;
    status?: string;
}

export function listShifts(params: ListShiftsParams = {}): Promise<PaginatedResponse<Shift>> {
    const query = new URLSearchParams();
    if (params.page) query.set('page', String(params.page));
    if (params.status) query.set('status', params.status);
    const qs = query.toString();
    return apiFetch<PaginatedResponse<Shift>>(`/shifts${qs ? `?${qs}` : ''}`);
}

export function getShift(id: string): Promise<{ data: Shift }> {
    return apiFetch<{ data: Shift }>(`/shifts/${id}`);
}

export function createShift(payload: CreateShiftPayload): Promise<{ data: Shift }> {
    return apiFetch<{ data: Shift }>('/shifts', { method: 'POST', body: JSON.stringify(payload) });
}

export function updateShift(id: string, payload: UpdateShiftPayload): Promise<{ data: Shift }> {
    return apiFetch<{ data: Shift }>(`/shifts/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
}

export function deleteShift(id: string): Promise<void> {
    return apiFetch<void>(`/shifts/${id}`, { method: 'DELETE' });
}
