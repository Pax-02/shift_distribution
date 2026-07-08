<?php

namespace App\Http\Controllers\Api;

use App\Events\AssignmentCancelled;
use App\Events\AssignmentConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function confirm(Assignment $assignment): AssignmentResource|JsonResponse
    {
        $this->authorize('confirm', $assignment);

        // The partial unique index (assignments_one_confirmed_per_shift)
        // enforces this at the DB level too; checking here gives a clean
        // 409 instead of a raw constraint-violation 500.
        if ($assignment->shift->confirmedAssignment()->exists()) {
            return response()->json(['message' => 'This shift already has a confirmed assignment.'], 409);
        }

        $assignment->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $assignment->shift->update(['status' => 'confirmed']);

        event(new AssignmentConfirmed($assignment));

        return new AssignmentResource($assignment->load(['shift', 'worker']));
    }

    public function cancel(Request $request, Assignment $assignment): AssignmentResource
    {
        $this->authorize('cancel', $assignment);

        $assignment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->string('reason')->value() ?: null,
        ]);

        event(new AssignmentCancelled($assignment));

        return new AssignmentResource($assignment->load(['shift', 'worker']));
    }
}
