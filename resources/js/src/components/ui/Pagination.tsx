import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { PaginatedResponse } from '../../types';

interface PaginationProps {
    meta: PaginatedResponse<unknown>['meta'];
    onPageChange: (page: number) => void;
}

// Split out of DataTable so Reports-style pages can reuse pagination
// controls without the full table chrome.
export function Pagination({ meta, onPageChange }: PaginationProps) {
    if (meta.last_page <= 1) {
        return null;
    }

    return (
        <div className="flex items-center justify-between border-t border-slate-100 px-4 py-3 dark:border-white/10">
            <p className="text-sm text-slate-500 dark:text-slate-400">
                Showing {meta.from ?? 0}–{meta.to ?? 0} of {meta.total}
            </p>
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    onClick={() => onPageChange(meta.current_page - 1)}
                    disabled={meta.current_page <= 1}
                    className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                    aria-label="Previous page"
                >
                    <ChevronLeft className="h-4 w-4" />
                </button>
                <span className="text-sm text-slate-600 dark:text-slate-300">
                    Page {meta.current_page} of {meta.last_page}
                </span>
                <button
                    type="button"
                    onClick={() => onPageChange(meta.current_page + 1)}
                    disabled={meta.current_page >= meta.last_page}
                    className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                    aria-label="Next page"
                >
                    <ChevronRight className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
