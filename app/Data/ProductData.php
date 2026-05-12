<?php

namespace App\Data;

use App\Models\Product;
use App\Support\ApiDateTime;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $price,
        public int $category_id,
        public ?CategoryData $category = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            price: (string) $product->price,
            category_id: (int) $product->category_id,
            category: $product->relationLoaded('category') && $product->category
                ? CategoryData::fromModel($product->category)
                : null,
            created_at: ApiDateTime::format($product->created_at),
            updated_at: ApiDateTime::format($product->updated_at),
        );
    }
}
