<?php

namespace App\Contracts\Repositories;

use App\Data\IndexData;
use App\Data\ProductData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(IndexData $data, ?bool $withTrashed = false): LengthAwarePaginator;

    public function findOrFail(int $id, bool $withTrashed = false): Product;

    /**
     * @param  array{name: string, description: ?string, price: string|float, category_id: int}  $data
     */
    public function create(array $data): Product;

    public function update(ProductData $data): ProductData;

    public function softDelete(int $id): void;

    public function forceDelete(int $id): void;

    public function restore(int $id): ProductData;
}
