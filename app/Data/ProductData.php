<?php

namespace App\Data;

use App\Models\Product;
use DateTimeInterface;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    private const string API_DATETIME_FORMAT = 'd M Y H:i:s';

    public function __construct(
        public ?int $id,
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
            created_at: self::formatForApi($product->created_at),
            updated_at: self::formatForApi($product->updated_at),
            deleted_at: self::formatForApi($product->deleted_at),
        );
    }

    private static function formatForApi(?DateTimeInterface $value): ?string
    {
        return $value?->format(self::API_DATETIME_FORMAT);
    }
}
