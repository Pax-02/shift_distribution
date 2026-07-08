import { Plus, SquarePen, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../auth/AuthContext';
import { Button } from '../../components/ui/Button';
import { DataTable, type Column } from '../../components/ui/DataTable';
import { fieldInputClasses } from '../../components/ui/FormField';
import { Pagination } from '../../components/ui/Pagination';
import { StatusBadge } from '../../components/ui/StatusBadge';
import { useClientsQuery } from '../../hooks/useClients';
import { useDeleteShiftMutation, useShiftsQuery } from '../../hooks/useShifts';
import type { ShiftStatus } from '../../types/enums';
import type { Shift } from '../../types/shift';

const STATUS_OPTIONS: { value: ShiftStatus | ''; label: string }[] = [
    { value: '', label: 'All statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'allocated', label: 'Allocated' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

export function ShiftsListPage() {
    const { hasPermission } = useAuth();
    const canManage = hasPermission('shifts.manage');
    const [page, setPage] = useState(1);
    const [status, setStatus] = useState<ShiftStatus | ''>('');
    const { data, isLoading } = useShiftsQuery({ page, status: status || undefined });
    // Shifts only carry client_id, not a client name — cross-reference
    // against the clients list rather than showing raw UUIDs. Only page 1
    // of clients is fetched, same known limitation as the shift form's
    // client picker (fine at this seed-data scale, not built to survive a
    // client list bigger than one page).
    const { data: clientsData } = useClientsQuery({});
    const clientNameById = new Map((clientsData?.data ?? []).map((client) => [client.id, client.name]));
    const deleteShiftMutation = useDeleteShiftMutation();
    const navigate = useNavigate();

    async function handleDelete(shift: Shift) {
        if (!window.confirm(`Delete this shift on ${shift.shift_date}? This cannot be undone.`)) {
            return;
        }
        await deleteShiftMutation.mutateAsync(shift.id);
    }

    const columns: Column<Shift>[] = [
        {
            key: 'when',
            header: 'Date & time',
            render: (shift) => (
                <div>
                    <p className="font-medium text-slate-900 dark:text-white">{shift.shift_date}</p>
                    <p className="text-xs text-slate-400 dark:text-slate-500">
                        {shift.start_time}–{shift.end_time}
                    </p>
                </div>
            ),
        },
        {
            key: 'client',
            header: 'Client',
            render: (shift) => clientNameById.get(shift.client_id) ?? shift.client_id,
        },
        {
            key: 'route',
            header: 'Route',
            render: (shift) => (
                <p className="max-w-xs truncate" title={`${shift.pickup_address_text ?? '—'} → ${shift.destination_address_text ?? '—'}`}>
                    {shift.pickup_address_text ?? '—'} → {shift.destination_address_text ?? '—'}
                </p>
            ),
        },
        {
            key: 'gender',
            header: 'Gender req.',
            render: (shift) => (shift.gender_requirement && shift.gender_requirement !== 'any' ? shift.gender_requirement : 'Any'),
        },
        { key: 'status', header: 'Status', render: (shift) => <StatusBadge status={shift.status} /> },
    ];

    if (canManage) {
        columns.push({
            key: 'actions',
            header: '',
            className: 'text-right',
            render: (shift) => (
                <div className="flex justify-end gap-1">
                    <Link
                        to={`/shifts/${shift.id}/edit`}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        aria-label={`Edit shift on ${shift.shift_date}`}
                    >
                        <SquarePen className="h-4 w-4" />
                    </Link>
                    <button
                        type="button"
                        onClick={() => void handleDelete(shift)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:text-slate-500 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                        aria-label={`Delete shift on ${shift.shift_date}`}
                    >
                        <Trash2 className="h-4 w-4" />
                    </button>
                </div>
            ),
        });
    }

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Shifts</h1>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">Transportation shifts requested by clients.</p>
                </div>
                {canManage && (
                    <Button onClick={() => navigate('/shifts/new')}>
                        <Plus className="h-4 w-4" />
                        Add shift
                    </Button>
                )}
            </div>

            <div className="mb-4">
                <select
                    value={status}
                    onChange={(event) => {
                        setStatus(event.target.value as ShiftStatus | '');
                        setPage(1);
                    }}
                    className={`${fieldInputClasses} max-w-xs`}
                >
                    {STATUS_OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <DataTable
                    isLoading={isLoading}
                    rows={data?.data ?? []}
                    rowKey={(shift) => shift.id}
                    emptyMessage="No shifts yet."
                    columns={columns}
                />
                {data && <Pagination meta={data.meta} onPageChange={setPage} />}
            </div>
        </div>
    );
}
