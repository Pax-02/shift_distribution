import type { Assignment, RuleBreakdownEntry } from './assignment';

// Mirrors AllocationResultResource (WorkerAllocationScore::toArray()).
export interface AllocationResult {
    worker_id: string;
    total_score: number;
    breakdown: RuleBreakdownEntry[];
}

// Mirrors RunAllocationRequest.
export interface RunAllocationPayload {
    shift_id: string;
}

// Response shape of POST /allocation/run (AllocationController::run) —
// not a plain Resource collection, so typed separately here.
export interface RunAllocationResponse {
    shift_id: string;
    proposed_assignment: Assignment | null;
    ranked_workers: AllocationResult[];
}
