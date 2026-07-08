import type { PaginatedResponse } from '../types';
import type { Worker, WorkerPayload } from '../types/worker';
import { apiFetch } from './client';

export interface ListWorkersParams {
    page?: number;
}

export function listWorkers(params: ListWorkersParams = {}): Promise<PaginatedResponse<Worker>> {
    const query = params.page ? `?page=${params.page}` : '';
    return apiFetch<PaginatedResponse<Worker>>(`/workers${query}`);
}

export function getWorker(id: string): Promise<{ data: Worker }> {
    return apiFetch<{ data: Worker }>(`/workers/${id}`);
}

export function createWorker(payload: WorkerPayload): Promise<{ data: Worker }> {
    return apiFetch<{ data: Worker }>('/workers', { method: 'POST', body: JSON.stringify(payload) });
}

export function updateWorker(id: string, payload: Partial<WorkerPayload>): Promise<{ data: Worker }> {
    return apiFetch<{ data: Worker }>(`/workers/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
}

export function deleteWorker(id: string): Promise<void> {
    return apiFetch<void>(`/workers/${id}`, { method: 'DELETE' });
}
