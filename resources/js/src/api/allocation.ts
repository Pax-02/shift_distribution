import type { PaginatedResponse } from '../types';
import type { RunAllocationResponse } from '../types/allocation';
import type { Assignment } from '../types/assignment';
import { apiFetch } from './client';

// Not wrapped in { data } — AllocationController::run returns a plain
// response()->json([...]), unlike every other endpoint in this API.
export function runAllocation(shiftId: string): Promise<RunAllocationResponse> {
    return apiFetch<RunAllocationResponse>('/allocation/run', {
        method: 'POST',
        body: JSON.stringify({ shift_id: shiftId }),
    });
}

export interface AllocationHistoryParams {
    page?: number;
    shift_id?: string;
}

export function getAllocationHistory(params: AllocationHistoryParams = {}): Promise<PaginatedResponse<Assignment>> {
    const query = new URLSearchParams();
    if (params.page) query.set('page', String(params.page));
    if (params.shift_id) query.set('shift_id', params.shift_id);
    const qs = query.toString();
    return apiFetch<PaginatedResponse<Assignment>>(`/allocation/history${qs ? `?${qs}` : ''}`);
}

export function confirmAssignment(id: string): Promise<{ data: Assignment }> {
    return apiFetch<{ data: Assignment }>(`/assignments/${id}/confirm`, { method: 'PATCH' });
}

export function cancelAssignment(id: string, reason?: string): Promise<{ data: Assignment }> {
    return apiFetch<{ data: Assignment }>(`/assignments/${id}/cancel`, {
        method: 'PATCH',
        body: JSON.stringify(reason ? { reason } : {}),
    });
}
