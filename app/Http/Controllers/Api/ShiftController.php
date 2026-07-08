<?php

namespace App\Http\Controllers\Api;

use App\Events\ShiftCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShiftController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Shift::class);

        return ShiftResource::collection(
            Shift::query()->orderBy('shift_date')->orderBy('start_time')->paginate()
        );
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
