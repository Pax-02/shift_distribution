<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\StoreWorkerRequest;
use App\Http\Requests\Worker\UpdateWorkerRequest;
use App\Http\Resources\WorkerResource;
use App\Models\Worker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Worker::class);

        return WorkerResource::collection(Worker::query()->paginate());
    }

    public function store(StoreWorkerRequest $request): WorkerResource
    {
        return new WorkerResource(Worker::create($request->validated()));
    }

    public function show(Worker $worker): WorkerResource
    {
        $this->authorize('view', $worker);

        return new WorkerResource($worker);
    }

    public function update(UpdateWorkerRequest $request, Worker $worker): WorkerResource
    {
        $worker->update($request->validated());

        return new WorkerResource($worker);
    }

    public function destroy(Worker $worker): JsonResponse
    {
        $this->authorize('delete', $worker);
        $worker->delete();

        return response()->json(status: 204);
    }
}
