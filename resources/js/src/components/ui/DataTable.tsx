import clsx from 'clsx';
import type { ReactNode } from 'react';

export interface Column<T> {
    key: string;
    header: string;
    render: (row: T) => ReactNode;
    className?: string;
}

interface DataTableProps<T> {
    columns: Column<T>[];
    rows: T[];
    rowKey: (row: T) => string;
    isLoading?: boolean;
    emptyMessage?: string;
}

export function DataTable<T>({ columns, rows, rowKey, isLoading, emptyMessage = 'No results.' }: DataTableProps<T>) {
    return (
        <table className="w-full text-left text-sm">
            <thead>
                <tr className="border-b border-slate-100 dark:border-white/10">
                    {columns.map((column) => (
                        <th
                            key={column.key}
                            className="px-4 py-3 text-xs font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                        >
                            {column.header}
                        </th>
                    ))}
                </tr>
            </thead>
            <tbody>
                {isLoading ? (
                    <tr>
                        <td colSpan={columns.length} className="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                            Loading…
                        </td>
                    </tr>
                ) : rows.length === 0 ? (
                    <tr>
                        <td colSpan={columns.length} className="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                            {emptyMessage}
                        </td>
                    </tr>
                ) : (
                    rows.map((row) => (
                        <tr
                            key={rowKey(row)}
                            className="border-b border-slate-50 last:border-0 hover:bg-slate-50 dark:border-white/5 dark:hover:bg-slate-800/50"
                        >
                            {columns.map((column) => (
                                <td key={column.key} className={clsx('px-4 py-3 text-slate-700 dark:text-slate-200', column.className)}>
                                    {column.render(row)}
                                </td>
                            ))}
                        </tr>
                    ))
                )}
            </tbody>
        </table>
    );
}
