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
import { useClientsQuery } from '../../hooks/useClients';
import { useCreateShiftMessageMutation } from '../../hooks/useShiftMessages';
import { useShiftsQuery } from '../../hooks/useShifts';
import { useWorkersQuery } from '../../hooks/useWorkers';
import type { AllocationResult, RunAllocationResponse } from '../../types/allocation';
import type { Assignment, RuleBreakdownEntry } from '../../types/assignment';
import type { ShiftMessage } from '../../types/shiftMessage';

// MockAIParser's client extraction only matches consecutive capitalized
// words with no punctuation ("for Volkman Ltd"), so a Faker-generated name
// with a comma ("Wilderman, Weimann and Kohler") won't round-trip — pick a
// real client name that actually looks parseable for the example text,
// falling back to the first client rather than guaranteeing a match.
const PARSEABLE_NAME = /^[A-Z][a-zA-Z'-]+(\s+[A-Z][a-zA-Z'-]+){0,2}$/;

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
    // Same permission as creating a shift directly (StoreShiftMessageRequest
    // authorizes off `create Shift`, since ingesting a message is just
    // another entry point that can end in one) — reused for both the
    // message-transform card and the confirm/cancel actions below.
    const canManageAssignments = hasPermission('shifts.manage');

    const [shiftId, setShiftId] = useState('');
    const [result, setResult] = useState<RunAllocationResponse | null>(null);
    const [runError, setRunError] = useState<string | null>(null);
    const [historyPage, setHistoryPage] = useState(1);
    const [messageText, setMessageText] = useState('');
    const [messageResult, setMessageResult] = useState<ShiftMessage | null>(null);
    const [messageError, setMessageError] = useState<string | null>(null);

    const { data: pendingShiftsData } = useShiftsQuery({ status: 'pending' });
    const pendingShifts = pendingShiftsData?.data ?? [];
    const { data: workersData } = useWorkersQuery({});
    const workerNameById = new Map(
        (workersData?.data ?? []).map((worker) => [worker.id, `${worker.first_name} ${worker.last_name}`]),
    );
    const { data: clientsData } = useClientsQuery({});
    const exampleClientName =
        clientsData?.data.find((client) => PARSEABLE_NAME.test(client.name))?.name ?? clientsData?.data[0]?.name ?? 'Acme Care';
    const { data: historyData, isLoading: historyLoading } = useAllocationHistoryQuery({ page: historyPage });

    const runMutation = useRunAllocationMutation();
    const confirmMutation = useConfirmAssignmentMutation();
    const cancelMutation = useCancelAssignmentMutation();
    const messageMutation = useCreateShiftMessageMutation();

    async function handleTransform() {
        setMessageError(null);
        setMessageResult(null);
        try {
            const response = await messageMutation.mutateAsync({ raw_message: messageText, channel: 'manual' });
            setMessageResult(response.data);
            if (response.data.shift_id) {
                setShiftId(response.data.shift_id);
            }
        } catch (error) {
            setMessageError(error instanceof ApiError ? error.message : 'Could not process this message. Please try again.');
        }
    }

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

            {canManageAssignments && (
                <div className="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
                    <p className="text-sm font-medium text-slate-700 dark:text-slate-300">New shift request</p>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Paste an inbound message and the parser will turn it into a shift.
                    </p>
                    <textarea
                        rows={3}
                        className={`${fieldInputClasses} mt-4`}
                        placeholder={`Transport needed for ${exampleClientName} on 20/08/2026, pickup: 12 Main Street, dropoff: City Hospital, 09:00 to 11:00`}
                        value={messageText}
                        onChange={(event) => setMessageText(event.target.value)}
                    />
                    <div className="mt-3 flex justify-end">
                        <Button onClick={() => void handleTransform()} disabled={!messageText.trim() || messageMutation.isPending}>
                            {messageMutation.isPending ? 'Transforming…' : 'Transform message'}
                        </Button>
                    </div>

                    {messageError && <p className="mt-4 text-sm text-red-600 dark:text-red-400">{messageError}</p>}

                    {messageResult?.parse_status === 'failed' && (
                        <p className="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/5 dark:text-red-400">
                            Couldn't parse this message: {messageResult.parse_error}
                        </p>
                    )}

                    {messageResult?.parse_status === 'validated' && messageResult.parsed_payload && (
                        <div
                            className={clsx(
                                'mt-4 rounded-xl border p-4 text-sm',
                                messageResult.shift_id
                                    ? 'border-green-200 bg-green-50 dark:border-green-500/20 dark:bg-green-500/5'
                                    : 'border-amber-200 bg-amber-50 dark:border-amber-500/20 dark:bg-amber-500/5',
                            )}
                        >
                            <p className="font-medium text-slate-900 dark:text-white">
                                {messageResult.shift_id
                                    ? `Shift created for ${messageResult.parsed_payload.client}`
                                    : `Parsed, but no client named "${messageResult.parsed_payload.client}" was found.`}
                            </p>
                            <p className="mt-1 text-slate-600 dark:text-slate-300">
                                {messageResult.parsed_payload.date} · {messageResult.parsed_payload.start_time}–
                                {messageResult.parsed_payload.end_time} · {messageResult.parsed_payload.pickup} →{' '}
                                {messageResult.parsed_payload.destination}
                            </p>
                            {messageResult.shift_id && (
                                <p className="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    Selected below — ready to run allocation.
                                </p>
                            )}
                        </div>
                    )}
                </div>
            )}

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
