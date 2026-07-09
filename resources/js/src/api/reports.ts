import type { ShiftsReport, WorkerHoursReportRow, WorkerRosterReport } from '../types/report';
import { apiFetch } from './client';

export function getHoursReport(week?: string): Promise<{ data: WorkerHoursReportRow[] }> {
    const qs = week ? `?week=${week}` : '';
    return apiFetch<{ data: WorkerHoursReportRow[] }>(`/reports/hours${qs}`);
}

export function getWorkersReport(): Promise<{ data: WorkerRosterReport }> {
    return apiFetch<{ data: WorkerRosterReport }>('/reports/workers');
}

export interface ShiftsReportParams {
    from?: string;
    to?: string;
}

export function getShiftsReport(params: ShiftsReportParams = {}): Promise<{ data: ShiftsReport }> {
    const query = new URLSearchParams();
    if (params.from) query.set('from', params.from);
    if (params.to) query.set('to', params.to);
    const qs = query.toString();
    return apiFetch<{ data: ShiftsReport }>(`/reports/shifts${qs ? `?${qs}` : ''}`);
}
