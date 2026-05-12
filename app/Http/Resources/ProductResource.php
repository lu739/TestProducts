<?php

namespace App\Http\Resources;

use App\Data\ProductData;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product|ProductData */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;

        return $resource instanceof ProductData
            ? $resource->toArray()
            : ProductData::fromModel($resource)->toArray();
    }
}
