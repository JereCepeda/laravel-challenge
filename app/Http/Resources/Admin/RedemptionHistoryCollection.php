<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\JsonResponse;

class RedemptionHistoryCollection extends ResourceCollection
{
    public $collects = RedemptionHistoryResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        $isEmpty = $this->collection->isEmpty();
        
        return [
            'message' => $isEmpty 
                ? 'No redemption history found' 
                : 'Redemption history retrieved successfully',
            'data' => $this->collection->toArray(),
            'meta' => $this->getMeta()
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     * Override para evitar el wrapping automático de Laravel
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json(
            $this->toArray($request)
        );
    }

    /**
     * Get metadata
     */
    private function getMeta(): array
    {
        if (method_exists($this->resource, 'total')) {
            return [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'has_more_pages' => $this->resource->hasMorePages(),
            ];
        }

        return [
            'total' => $this->collection->count()
        ];
    }
}