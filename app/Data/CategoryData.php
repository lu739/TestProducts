<?php

namespace App\Data;

use App\Models\Category;
use DateTimeInterface;
use Spatie\LaravelData\Data;

class CategoryData extends Data
{
    private const string API_DATETIME_FORMAT = 'd M Y H:i:s';

    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            created_at: self::formatForApi($category->created_at),
            updated_at: self::formatForApi($category->updated_at),
        );
    }

    private static function formatForApi(?DateTimeInterface $value): ?string
    {
        return $value?->format(self::API_DATETIME_FORMAT);
    }
}
