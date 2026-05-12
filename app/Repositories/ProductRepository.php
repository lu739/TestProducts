<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Data\IndexData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(IndexData $data): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->orderBy('id')
            ->paginate(perPage: $data->per_page, page: $data->page);
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()
            ->with('category')
            ->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->fill($data);
        $product->save();

        return $product->fresh(['category']);
    }

    public function delete(int $id): void
    {
        $this->findOrFail($id)->delete();
    }
}
