import clsx from 'clsx';
import type { AssignmentStatus, ShiftStatus } from '../../types/enums';

export type Status = ShiftStatus | AssignmentStatus;

const STATUS_STYLES: Record<Status, string> = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    allocated: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    confirmed: 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400',
    completed: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    proposed: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    declined: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
};

export const STATUS_LABELS: Record<Status, string> = {
    pending: 'Pending',
    allocated: 'Allocated',
    confirmed: 'Confirmed',
    completed: 'Completed',
    cancelled: 'Cancelled',
    proposed: 'Proposed',
    declined: 'Declined',
};

// Solid fill variant of the same status hues, for bar/chart marks —
// STATUS_STYLES above is pale pill backgrounds (badge use), not usable as
// a solid mark fill. Same reserved status color language, different job.
export const STATUS_BAR_FILL: Record<Status, string> = {
    pending: 'bg-amber-400 dark:bg-amber-500',
    allocated: 'bg-blue-400 dark:bg-blue-500',
    confirmed: 'bg-green-400 dark:bg-green-500',
    completed: 'bg-slate-400 dark:bg-slate-500',
    cancelled: 'bg-red-400 dark:bg-red-500',
    proposed: 'bg-amber-400 dark:bg-amber-500',
    declined: 'bg-red-400 dark:bg-red-500',
};

export function StatusBadge({ status }: { status: Status }) {
    return (
        <span className={clsx('inline-flex rounded-full px-2 py-0.5 text-xs font-medium', STATUS_STYLES[status])}>
            {STATUS_LABELS[status]}
        </span>
    );
}
