import { useAuth } from '../auth/AuthContext';
import { STATUS_BAR_FILL, STATUS_LABELS } from '../components/ui/StatusBadge';
import { StatCard } from '../components/ui/StatCard';
import { useClientsQuery } from '../hooks/useClients';
import { useShiftsReportQuery } from '../hooks/useReports';
import { useShiftsQuery } from '../hooks/useShifts';
import { useWorkersQuery } from '../hooks/useWorkers';
import type { ShiftStatus } from '../types/enums';

// Fixed order (the shift workflow's natural progression, cancelled as the
// exit state) rather than sorted by count — a bar chart's category order
// should be stable across refetches, not jump around as counts change.
const SHIFT_STATUS_ORDER: ShiftStatus[] = ['pending', 'allocated', 'confirmed', 'completed', 'cancelled'];

export function DashboardPage() {
    // Non-null: this page only renders inside RequireAuth, which guarantees
    // an authenticated user.
    const { user, hasPermission } = useAuth();
    const canViewWorkers = hasPermission('workers.view');
    const canViewClients = hasPermission('clients.view');
    const canViewShifts = hasPermission('shifts.view');
    const canViewReports = hasPermission('reports.view');

    const { data: workersData } = useWorkersQuery({});
    const { data: clientsData } = useClientsQuery({});
    const { data: shiftsData } = useShiftsQuery({});
    const { data: shiftsReportData } = useShiftsReportQuery({});

    const byStatus = shiftsReportData?.data.by_status ?? {};
    const maxCount = Math.max(1, ...SHIFT_STATUS_ORDER.map((status) => byStatus[status] ?? 0));

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Welcome, {user!.name.split(' ')[0]}</h1>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">Here's what's happening across your workforce.</p>
            </div>

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
                {canViewWorkers && <StatCard label="Total workers" value={workersData?.meta.total ?? '—'} />}
                {canViewClients && <StatCard label="Total clients" value={clientsData?.meta.total ?? '—'} />}
                {canViewShifts && <StatCard label="Total shifts" value={shiftsData?.meta.total ?? '—'} />}
            </div>

            {canViewReports && (
                <div className="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
                    <p className="mb-6 text-sm font-medium text-slate-700 dark:text-slate-300">Shifts by status</p>
                    {/* Hand-rolled bars, not a chart library: 5 categories, values already
                        directly labeled (nothing gated behind hover), reusing the same
                        reserved status color language as StatusBadge elsewhere in the app.
                        A full breakdown table already exists on the Reports page. */}
                    <div className="flex items-end gap-6">
                        {SHIFT_STATUS_ORDER.map((status) => {
                            const count = byStatus[status] ?? 0;
                            const heightPx = Math.max(Math.round((count / maxCount) * 140), 2);
                            return (
                                <div key={status} className="flex flex-col items-center gap-2">
                                    <span className="text-sm font-medium text-slate-900 dark:text-white">{count}</span>
                                    <div
                                        className={`w-6 rounded-t ${STATUS_BAR_FILL[status]}`}
                                        style={{ height: `${heightPx}px` }}
                                    />
                                    <span className="text-xs text-slate-400 dark:text-slate-500">{STATUS_LABELS[status]}</span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
