<?php

namespace App\Contracts\Repositories;

use App\Data\IndexData;
use App\Data\ProductData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(IndexData $data, ?bool $withTrashed = false): LengthAwarePaginator;

    public function findOrFail(int $id, bool $withTrashed = false): ProductData;

    public function create(ProductData $data): ProductData;

    public function update(ProductData $data): ProductData;

    public function softDelete(int $id): void;

    public function forceDelete(int $id): void;

    public function restore(int $id): ProductData;
}
