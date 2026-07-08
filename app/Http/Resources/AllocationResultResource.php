<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps a Services\Allocation\WorkerAllocationScore (not an Eloquent model —
 * JsonResource works fine against any object with the accessed properties).
 */
class AllocationResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
