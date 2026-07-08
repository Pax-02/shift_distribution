// Mirrors ReportController's three endpoints — plain arrays/objects, not Resources.

export interface WorkerHoursReportRow {
    worker_id: string;
    worker_name: string;
    week_start_date: string;
    contracted_hours: number;
    allocated_hours: number;
    worked_hours: number | null;
    utilization: number | null;
}

export interface WorkerRosterReport {
    total_workers: number;
    active_workers: number;
    inactive_workers: number;
    standby_workers: number;
    by_gender: Record<string, number>;
}

export interface ShiftsReport {
    total_shifts: number;
    by_status: Record<string, number>;
    confirmed_allocation_rate: number | null;
}
