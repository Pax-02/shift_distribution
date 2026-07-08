<?php

namespace App\Http\Controllers\Api;

use App\Events\AssignmentCreated;
use App\Events\ShiftAllocated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Allocation\RunAllocationRequest;
use App\Http\Resources\AllocationResultResource;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Shift;
use App\Services\Allocation\AllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AllocationController extends Controller
{
    /**
     * Scores every eligible worker against the shift and proposes the
     * top-ranked one — a "proposed" Assignment a dispatcher can later
     * confirm or decline. The full ranked list (with score breakdown) is
     * returned too, so a dispatcher can see who else was considered.
     */
    public function run(RunAllocationRequest $request, AllocationService $service): JsonResponse
    {
        $shift = Shift::findOrFail($request->validated('shift_id'));
        $ranked = $service->allocate($shift);

        event(new ShiftAllocated($shift, $ranked));

        $topCandidate = $ranked->first();
        $assignment = null;

        if ($topCandidate !== null) {
            $assignment = Assignment::create([
                'shift_id' => $shift->id,
                'worker_id' => $topCandidate->workerId,
                'status' => 'proposed',
                'allocation_score' => $topCandidate->totalScore,
                'score_breakdown' => $topCandidate->breakdown,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]);

            $shift->update(['status' => 'allocated']);

            event(new AssignmentCreated($assignment));
        }

        return response()->json([
            'shift_id' => $shift->id,
            'proposed_assignment' => $assignment ? new AssignmentResource($assignment->load(['shift', 'worker'])) : null,
            'ranked_workers' => AllocationResultResource::collection($ranked),
        ]);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAllocationHistory');

        $assignments = Assignment::query()
            ->with(['shift', 'worker'])
            ->when($request->filled('shift_id'), fn ($q) => $q->where('shift_id', $request->string('shift_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereHas('shift', fn ($sq) => $sq->where('shift_date', '>=', $request->date('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereHas('shift', fn ($sq) => $sq->where('shift_date', '<=', $request->date('to'))))
            ->orderByDesc('created_at')
            ->paginate();

        return AssignmentResource::collection($assignments);
    }
}
