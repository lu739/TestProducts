<?php

namespace App\Contracts\Repositories;

use App\Data\IndexData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(IndexData $data): LengthAwarePaginator;

    public function findOrFail(int $id): Product;

    /**
     * @param  array{name: string, description: ?string, price: string|float, category_id: int}  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
