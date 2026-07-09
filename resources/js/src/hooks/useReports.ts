import { useQuery } from '@tanstack/react-query';
import { getHoursReport, getShiftsReport, getWorkersReport, type ShiftsReportParams } from '../api/reports';

export const reportKeys = {
    all: ['reports'] as const,
    hours: (week: string) => [...reportKeys.all, 'hours', week] as const,
    workers: () => [...reportKeys.all, 'workers'] as const,
    shifts: (params: ShiftsReportParams) => [...reportKeys.all, 'shifts', params] as const,
};

// Empty string means "current week" — left for the backend to compute
// (Carbon's start-of-week-Monday logic) rather than duplicating it here.
export function useHoursReportQuery(week: string) {
    return useQuery({
        queryKey: reportKeys.hours(week),
        queryFn: () => getHoursReport(week || undefined),
    });
}

export function useWorkersReportQuery() {
    return useQuery({
        queryKey: reportKeys.workers(),
        queryFn: () => getWorkersReport(),
    });
}

export function useShiftsReportQuery(params: ShiftsReportParams = {}) {
    return useQuery({
        queryKey: reportKeys.shifts(params),
        queryFn: () => getShiftsReport(params),
    });
}
