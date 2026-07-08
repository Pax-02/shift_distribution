import clsx from 'clsx';
import { Plus, SquarePen, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../auth/AuthContext';
import { Button } from '../../components/ui/Button';
import { DataTable, type Column } from '../../components/ui/DataTable';
import { Pagination } from '../../components/ui/Pagination';
import { useDeleteWorkerMutation, useWorkersQuery } from '../../hooks/useWorkers';
import type { Worker } from '../../types/worker';

export function WorkersListPage() {
    const { hasPermission } = useAuth();
    const canManage = hasPermission('workers.manage');
    const [page, setPage] = useState(1);
    const { data, isLoading } = useWorkersQuery({ page });
    const deleteWorkerMutation = useDeleteWorkerMutation();
    const navigate = useNavigate();

    async function handleDelete(worker: Worker) {
        if (!window.confirm(`Delete ${worker.first_name} ${worker.last_name}? This cannot be undone.`)) {
            return;
        }
        await deleteWorkerMutation.mutateAsync(worker.id);
    }

    const columns: Column<Worker>[] = [
        {
            key: 'name',
            header: 'Name',
            render: (worker) => (
                <div>
                    <p className="font-medium text-slate-900 dark:text-white">
                        {worker.first_name} {worker.last_name}
                    </p>
                    <p className="text-xs text-slate-400 dark:text-slate-500">{worker.email ?? '—'}</p>
                </div>
            ),
        },
        { key: 'phone', header: 'Phone', render: (worker) => worker.phone ?? '—' },
        {
            key: 'hours',
            header: 'Weekly hours',
            render: (worker) =>
                `${worker.contracted_weekly_hours}${worker.max_weekly_hours ? ` / ${worker.max_weekly_hours}` : ''}`,
        },
        { key: 'standby', header: 'Standby', render: (worker) => (worker.standby ? 'Yes' : 'No') },
        {
            key: 'status',
            header: 'Status',
            render: (worker) => (
                <span
                    className={clsx(
                        'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                        worker.is_active
                            ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400'
                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    )}
                >
                    {worker.is_active ? 'Active' : 'Inactive'}
                </span>
            ),
        },
    ];

    if (canManage) {
        columns.push({
            key: 'actions',
            header: '',
            className: 'text-right',
            render: (worker) => (
                <div className="flex justify-end gap-1">
                    <Link
                        to={`/workers/${worker.id}/edit`}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        aria-label={`Edit ${worker.first_name}`}
                    >
                        <SquarePen className="h-4 w-4" />
                    </Link>
                    <button
                        type="button"
                        onClick={() => void handleDelete(worker)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:text-slate-500 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                        aria-label={`Delete ${worker.first_name}`}
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
                    <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Workers</h1>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Support workers eligible for shift allocation.
                    </p>
                </div>
                {canManage && (
                    <Button onClick={() => navigate('/workers/new')}>
                        <Plus className="h-4 w-4" />
                        Add worker
                    </Button>
                )}
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <DataTable
                    isLoading={isLoading}
                    rows={data?.data ?? []}
                    rowKey={(worker) => worker.id}
                    emptyMessage="No workers yet."
                    columns={columns}
                />
                {data && <Pagination meta={data.meta} onPageChange={setPage} />}
            </div>
        </div>
    );
}
