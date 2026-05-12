<?php

namespace App\Data;

use App\Models\Category;
use App\Support\ApiDateTime;
use Spatie\LaravelData\Data;

class CategoryData extends Data
{
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
            created_at: ApiDateTime::format($category->created_at),
            updated_at: ApiDateTime::format($category->updated_at),
        );
    }
}
