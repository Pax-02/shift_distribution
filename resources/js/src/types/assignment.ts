import type { AssignmentStatus } from './enums';
import type { Shift } from './shift';
import type { Worker } from './worker';

// One entry in AssignmentResource.score_breakdown / AllocationResultResource.breakdown.
export type RuleBreakdownEntry =
    | {
          rule: string;
          type: 'hard';
          passed: boolean;
          explanation: string;
      }
    | {
          rule: string;
          type: 'soft';
          weight: number;
          raw_score: number;
          weighted_score: number;
          explanation: string;
      };

// Mirrors app/Http/Resources/AssignmentResource.php. `shift`/`worker` are
// only present when the controller eager-loads those relations.
export interface Assignment {
    id: string;
    shift?: Shift;
    worker?: Worker;
    status: AssignmentStatus;
    allocation_score: string;
    score_breakdown: RuleBreakdownEntry[] | null;
    hours: string | null;
    assigned_at: string | null;
    confirmed_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    created_at: string;
}
