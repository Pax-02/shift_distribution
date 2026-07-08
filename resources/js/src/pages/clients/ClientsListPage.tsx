import clsx from 'clsx';
import { Plus, SquarePen, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../auth/AuthContext';
import { Button } from '../../components/ui/Button';
import { DataTable, type Column } from '../../components/ui/DataTable';
import { Pagination } from '../../components/ui/Pagination';
import { useClientsQuery, useDeleteClientMutation } from '../../hooks/useClients';
import type { Client } from '../../types/client';

export function ClientsListPage() {
    const { hasPermission } = useAuth();
    const canManage = hasPermission('clients.manage');
    const [page, setPage] = useState(1);
    const { data, isLoading } = useClientsQuery({ page });
    const deleteClientMutation = useDeleteClientMutation();
    const navigate = useNavigate();

    async function handleDelete(client: Client) {
        if (!window.confirm(`Delete ${client.name}? This cannot be undone.`)) {
            return;
        }
        await deleteClientMutation.mutateAsync(client.id);
    }

    const columns: Column<Client>[] = [
        {
            key: 'name',
            header: 'Name',
            render: (client) => <p className="font-medium text-slate-900 dark:text-white">{client.name}</p>,
        },
        {
            key: 'contact',
            header: 'Contact',
            render: (client) => (
                <div>
                    <p>{client.contact_name ?? '—'}</p>
                    <p className="text-xs text-slate-400 dark:text-slate-500">{client.contact_email ?? client.contact_phone ?? ''}</p>
                </div>
            ),
        },
        {
            key: 'addresses',
            header: 'Addresses',
            // Read-only: the API has no endpoint to create/edit client
            // addresses yet, only to eager-load the existing ones.
            render: (client) => (client.addresses?.length ? `${client.addresses.length}` : '—'),
        },
        {
            key: 'status',
            header: 'Status',
            render: (client) => (
                <span
                    className={clsx(
                        'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                        client.is_active
                            ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400'
                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    )}
                >
                    {client.is_active ? 'Active' : 'Inactive'}
                </span>
            ),
        },
    ];

    if (canManage) {
        columns.push({
            key: 'actions',
            header: '',
            className: 'text-right',
            render: (client) => (
                <div className="flex justify-end gap-1">
                    <Link
                        to={`/clients/${client.id}/edit`}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        aria-label={`Edit ${client.name}`}
                    >
                        <SquarePen className="h-4 w-4" />
                    </Link>
                    <button
                        type="button"
                        onClick={() => void handleDelete(client)}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:text-slate-500 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                        aria-label={`Delete ${client.name}`}
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
                    <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Clients</h1>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">Organizations that request shifts.</p>
                </div>
                {canManage && (
                    <Button onClick={() => navigate('/clients/new')}>
                        <Plus className="h-4 w-4" />
                        Add client
                    </Button>
                )}
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <DataTable
                    isLoading={isLoading}
                    rows={data?.data ?? []}
                    rowKey={(client) => client.id}
                    emptyMessage="No clients yet."
                    columns={columns}
                />
                {data && <Pagination meta={data.meta} onPageChange={setPage} />}
            </div>
        </div>
    );
}
