import clsx from 'clsx';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { useState } from 'react';
import { useAuth } from '../../auth/AuthContext';
import { ApiError } from '../../api/client';
import { Button } from '../../components/ui/Button';
import { DataTable, type Column } from '../../components/ui/DataTable';
import { fieldInputClasses } from '../../components/ui/FormField';
import { Pagination } from '../../components/ui/Pagination';
import { StatusBadge } from '../../components/ui/StatusBadge';
import {
    useAllocationHistoryQuery,
    useCancelAssignmentMutation,
    useConfirmAssignmentMutation,
    useRunAllocationMutation,
} from '../../hooks/useAllocation';
import { useShiftsQuery } from '../../hooks/useShifts';
import { useWorkersQuery } from '../../hooks/useWorkers';
import type { AllocationResult, RunAllocationResponse } from '../../types/allocation';
import type { Assignment, RuleBreakdownEntry } from '../../types/assignment';

// Rule FQCNs (e.g. "App\Rules\Allocation\AvailabilityRule") are more
// readable trimmed to just the class name.
function shortRuleName(rule: string): string {
    return rule.split('\\').pop() ?? rule;
}

function RuleBreakdown({ entries }: { entries: RuleBreakdownEntry[] }) {
    return (
        <ul className="mt-2 space-y-1 border-t border-slate-100 pt-2 text-xs dark:border-white/10">
            {entries.map((entry) => (
                <li key={entry.rule} className="flex items-start justify-between gap-3 text-slate-500 dark:text-slate-400">
                    <span>
                        <span className="font-medium text-slate-700 dark:text-slate-300">{shortRuleName(entry.rule)}</span>
                        {' — '}
                        {entry.explanation}
                    </span>
                    <span className="shrink-0 font-mono">
                        {entry.type === 'hard' ? (entry.passed ? 'pass' : 'fail') : entry.weighted_score.toFixed(2)}
                    </span>
                </li>
            ))}
        </ul>
    );
}

function RankedWorkerRow({ result, name }: { result: AllocationResult; name: string }) {
    const [expanded, setExpanded] = useState(false);
    return (
        <div className="border-b border-slate-100 px-4 py-3 last:border-0 dark:border-white/5">
            <button
                type="button"
                onClick={() => setExpanded((value) => !value)}
                className="flex w-full items-center justify-between text-left"
            >
                <span className="text-sm font-medium text-slate-900 dark:text-white">{name}</span>
                <span className="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    Score {result.total_score.toFixed(2)}
                    {expanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                </span>
            </button>
            {expanded && <RuleBreakdown entries={result.breakdown} />}
        </div>
    );
}

export function AllocationPage() {
    const { hasPermission } = useAuth();
    const canManageAssignments = hasPermission('shifts.manage');

    const [shiftId, setShiftId] = useState('');
    const [result, setResult] = useState<RunAllocationResponse | null>(null);
    const [runError, setRunError] = useState<string | null>(null);
    const [historyPage, setHistoryPage] = useState(1);

    const { data: pendingShiftsData } = useShiftsQuery({ status: 'pending' });
    const pendingShifts = pendingShiftsData?.data ?? [];
    const { data: workersData } = useWorkersQuery({});
    const workerNameById = new Map(
        (workersData?.data ?? []).map((worker) => [worker.id, `${worker.first_name} ${worker.last_name}`]),
    );
    const { data: historyData, isLoading: historyLoading } = useAllocationHistoryQuery({ page: historyPage });

    const runMutation = useRunAllocationMutation();
    const confirmMutation = useConfirmAssignmentMutation();
    const cancelMutation = useCancelAssignmentMutation();

    async function handleRun() {
        setRunError(null);
        setResult(null);
        try {
            const response = await runMutation.mutateAsync(shiftId);
            setResult(response);
        } catch (error) {
            setRunError(error instanceof ApiError ? error.message : 'Allocation failed. Please try again.');
        }
    }

    async function handleConfirm(assignmentId: string) {
        await confirmMutation.mutateAsync(assignmentId);
        if (result?.proposed_assignment?.id === assignmentId) {
            setResult(null);
        }
    }

    async function handleCancel(assignmentId: string) {
        const reason = window.prompt('Reason for cancelling (optional):') ?? undefined;
        await cancelMutation.mutateAsync({ id: assignmentId, reason });
        if (result?.proposed_assignment?.id === assignmentId) {
            setResult(null);
        }
    }

    const historyColumns: Column<Assignment>[] = [
        {
            key: 'worker',
            header: 'Worker',
            render: (assignment) => (assignment.worker ? `${assignment.worker.first_name} ${assignment.worker.last_name}` : '—'),
        },
        {
            key: 'shift',
            header: 'Shift',
            render: (assignment) =>
                assignment.shift ? (
                    <div>
                        <p>{assignment.shift.shift_date}</p>
                        <p className="text-xs text-slate-400 dark:text-slate-500">
                            {assignment.shift.start_time.slice(0, 5)}–{assignment.shift.end_time.slice(0, 5)}
                        </p>
                    </div>
                ) : (
                    '—'
                ),
        },
        { key: 'score', header: 'Score', render: (assignment) => Number(assignment.allocation_score).toFixed(2) },
        { key: 'status', header: 'Status', render: (assignment) => <StatusBadge status={assignment.status} /> },
    ];

    if (canManageAssignments) {
        historyColumns.push({
            key: 'actions',
            header: '',
            className: 'text-right',
            render: (assignment) =>
                assignment.status === 'proposed' ? (
                    <div className="flex justify-end gap-2">
                        <Button variant="secondary" onClick={() => void handleConfirm(assignment.id)}>
                            Confirm
                        </Button>
                        <Button variant="danger" onClick={() => void handleCancel(assignment.id)}>
                            Cancel
                        </Button>
                    </div>
                ) : null,
        });
    }

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-2xl font-semibold text-slate-900 dark:text-white">Allocation</h1>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Score eligible workers against a pending shift and propose the best fit.
                </p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
                <div className="flex flex-wrap items-end gap-4">
                    <div className="min-w-64 flex-1">
                        <label htmlFor="shift_id" className="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Pending shift
                        </label>
                        <select
                            id="shift_id"
                            className={fieldInputClasses}
                            value={shiftId}
                            onChange={(event) => setShiftId(event.target.value)}
                        >
                            <option value="" disabled>
                                {pendingShifts.length === 0 ? 'No pending shifts' : 'Select a shift…'}
                            </option>
                            {pendingShifts.map((shift) => (
                                <option key={shift.id} value={shift.id}>
                                    {shift.shift_date} {shift.start_time.slice(0, 5)}–{shift.end_time.slice(0, 5)} —{' '}
                                    {shift.pickup_address_text ?? 'pickup TBD'} → {shift.destination_address_text ?? 'destination TBD'}
                                </option>
                            ))}
                        </select>
                    </div>
                    <Button onClick={() => void handleRun()} disabled={!shiftId || runMutation.isPending}>
                        {runMutation.isPending ? 'Running…' : 'Run allocation'}
                    </Button>
                </div>

                {runError && <p className="mt-4 text-sm text-red-600 dark:text-red-400">{runError}</p>}

                {result && (
                    <div className="mt-6 space-y-6">
                        {result.proposed_assignment ? (
                            <div className="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/20 dark:bg-blue-500/5">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-900 dark:text-white">
                                            Proposed: {result.proposed_assignment.worker?.first_name}{' '}
                                            {result.proposed_assignment.worker?.last_name}
                                        </p>
                                        <p className="text-xs text-slate-500 dark:text-slate-400">
                                            Score {Number(result.proposed_assignment.allocation_score).toFixed(2)}
                                        </p>
                                    </div>
                                    {canManageAssignments && (
                                        <div className="flex gap-2">
                                            <Button variant="secondary" onClick={() => void handleConfirm(result.proposed_assignment!.id)}>
                                                Confirm
                                            </Button>
                                            <Button variant="danger" onClick={() => void handleCancel(result.proposed_assignment!.id)}>
                                                Cancel
                                            </Button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <p className="text-sm text-slate-500 dark:text-slate-400">No eligible workers were found for this shift.</p>
                        )}

                        {result.ranked_workers.length > 0 && (
                            <div>
                                <p className="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Ranked candidates ({result.ranked_workers.length})
                                </p>
                                <div className={clsx('rounded-xl border border-slate-200 dark:border-white/10')}>
                                    {result.ranked_workers.map((candidate) => (
                                        <RankedWorkerRow
                                            key={candidate.worker_id}
                                            result={candidate}
                                            name={workerNameById.get(candidate.worker_id) ?? candidate.worker_id}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>

            <div>
                <h2 className="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Recent allocations</h2>
                <div className="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                    <DataTable
                        isLoading={historyLoading}
                        rows={historyData?.data ?? []}
                        rowKey={(assignment) => assignment.id}
                        emptyMessage="No allocation history yet."
                        columns={historyColumns}
                    />
                    {historyData && <Pagination meta={historyData.meta} onPageChange={setHistoryPage} />}
                </div>
            </div>
        </div>
    );
}
