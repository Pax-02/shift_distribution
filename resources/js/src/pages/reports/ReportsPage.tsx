import { useState } from 'react';
import { DataTable, type Column } from '../../components/ui/DataTable';
import { fieldInputClasses } from '../../components/ui/FormField';
import { StatCard } from '../../components/ui/StatCard';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { useHoursReportQuery, useShiftsReportQuery, useWorkersReportQuery } from '../../hooks/useReports';
import type { ShiftStatus } from '../../types/enums';
import type { WorkerHoursReportRow } from '../../types/report';

function prettifyLabel(value: string): string {
    return value.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

function formatPercent(value: number | null): string {
    return value === null ? '—' : `${Math.round(value * 100)}%`;
}

const hoursColumns: Column<WorkerHoursReportRow>[] = [
    { key: 'worker', header: 'Worker', render: (row) => row.worker_name },
    { key: 'contracted', header: 'Contracted', render: (row) => row.contracted_hours.toFixed(2) },
    { key: 'allocated', header: 'Allocated', render: (row) => row.allocated_hours.toFixed(2) },
    { key: 'worked', header: 'Worked', render: (row) => (row.worked_hours === null ? '—' : row.worked_hours.toFixed(2)) },
    { key: 'utilization', header: 'Utilization', render: (row) => formatPercent(row.utilization) },
];

export function ReportsPage() {
    const [week, setWeek] = useState('');
    const { data: hoursData, isLoading: hoursLoading } = useHoursReportQuery(week);
    const { data: workersData } = useWorkersReportQuery();
    const { data: shiftsData } = useShiftsReportQuery();

    const roster = workersData?.data;
    const shifts = shiftsData?.data;

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Reports</h1>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">Roster, shift, and hours summaries.</p>
            </div>

            <div>
                <h2 className="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Workforce</h2>
                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard label="Total workers" value={roster?.total_workers ?? '—'} />
                    <StatCard label="Active" value={roster?.active_workers ?? '—'} />
                    <StatCard label="Inactive" value={roster?.inactive_workers ?? '—'} />
                    <StatCard label="Standby" value={roster?.standby_workers ?? '—'} />
                </div>
                {roster && Object.keys(roster.by_gender).length > 0 && (
                    <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
                        <p className="mb-3 text-sm font-medium text-slate-700 dark:text-slate-300">By gender</p>
                        <div className="flex flex-wrap gap-6">
                            {Object.entries(roster.by_gender).map(([gender, count]) => (
                                <div key={gender}>
                                    <p className="text-lg font-semibold text-slate-900 dark:text-white">{count}</p>
                                    <p className="text-xs text-slate-400 dark:text-slate-500">{prettifyLabel(gender)}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            <div>
                <h2 className="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Shifts</h2>
                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard label="Total shifts" value={shifts?.total_shifts ?? '—'} />
                    <StatCard label="Confirmed allocation rate" value={formatPercent(shifts?.confirmed_allocation_rate ?? null)} />
                </div>
                {shifts && Object.keys(shifts.by_status).length > 0 && (
                    <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
                        <p className="mb-3 text-sm font-medium text-slate-700 dark:text-slate-300">By status</p>
                        <div className="flex flex-wrap gap-4">
                            {Object.entries(shifts.by_status).map(([status, count]) => (
                                <div key={status} className="flex items-center gap-2">
                                    <StatusBadge status={status as ShiftStatus} />
                                    <span className="text-sm text-slate-600 dark:text-slate-300">{count}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            <div>
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Hours by worker</h2>
                    <div className="flex items-center gap-2">
                        <label htmlFor="week" className="text-sm text-slate-500 dark:text-slate-400">
                            Week of
                        </label>
                        <input
                            id="week"
                            type="date"
                            className={`${fieldInputClasses} w-auto`}
                            value={week}
                            onChange={(event) => setWeek(event.target.value)}
                        />
                    </div>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                    <DataTable
                        isLoading={hoursLoading}
                        rows={hoursData?.data ?? []}
                        rowKey={(row) => row.worker_id}
                        emptyMessage="No hours recorded for this week."
                        columns={hoursColumns}
                    />
                </div>
            </div>
        </div>
    );
}
