<?php

namespace App\Http\Controllers\Api;

use App\Events\ShiftCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShiftController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Shift::class);

        // Same when()-filter pattern as AllocationController::history.
        $shifts = Shift::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($q) => $q->where('shift_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('shift_date', '<=', $request->date('to')))
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->paginate();

        return ShiftResource::collection($shifts);
    }

    public function store(StoreShiftRequest $request): ShiftResource
    {
        $shift = Shift::create($request->validated() + ['created_by' => $request->user()->id]);

        event(new ShiftCreated($shift));

        return new ShiftResource($shift);
    }

    public function show(Shift $shift): ShiftResource
    {
        $this->authorize('view', $shift);

        return new ShiftResource($shift);
    }

    public function update(UpdateShiftRequest $request, Shift $shift): ShiftResource
    {
        $shift->update($request->validated());

        return new ShiftResource($shift);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $this->authorize('delete', $shift);
        $shift->delete();

        return response()->json(status: 204);
    }
}
