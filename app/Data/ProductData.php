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
        public ?string $deleted_at = null,
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
            deleted_at: ApiDateTime::format($product->deleted_at),
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromProductForUpdate(Product $product, array $validated): self
    {
        $description = array_key_exists('description', $validated)
            ? $validated['description']
            : $product->description;

        return new self(
            id: (int) $product->id,
            name: array_key_exists('name', $validated) ? (string) $validated['name'] : $product->name,
            description: $description !== null ? (string) $description : null,
            price: array_key_exists('price', $validated) ? (string) $validated['price'] : (string) $product->price,
            category_id: array_key_exists('category_id', $validated) ? (int) $validated['category_id'] : (int) $product->category_id,
            category: $product->relationLoaded('category') && $product->category
                ? CategoryData::fromModel($product->category)
                : null,
            created_at: ApiDateTime::format($product->created_at),
            updated_at: ApiDateTime::format($product->updated_at),
            deleted_at: ApiDateTime::format($product->deleted_at),
        );
    }
}
