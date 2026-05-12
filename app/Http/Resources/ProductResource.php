<?php

namespace App\Http\Resources;

use App\Data\ProductData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ProductData::fromModel($this->resource)->toArray();
    }
}
