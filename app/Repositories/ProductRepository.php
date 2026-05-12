<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Data\IndexData;
use App\Data\ProductData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(IndexData $data, ?bool $withTrashed = false): LengthAwarePaginator
    {
        return Product::query()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->with('category')
            ->orderByRaw('deleted_at is null desc')
            ->orderByDesc('id')
            ->paginate(perPage: $data->per_page, page: $data->page);
    }

    public function findOrFail(int $id, bool $withTrashed = false): Product
    {
        $query = Product::query()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->with('category');

        return $query->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(ProductData $data): ProductData
    {
        $product = Product::query()
            ->with('category')
            ->findOrFail($data->id);

        $product->fill([
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'category_id' => $data->category_id,
        ]);
        $product->save();

        return ProductData::fromModel($product->fresh(['category']));
    }

    public function softDelete(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $product->delete();
    }

    public function forceDelete(int $id): void
    {
        $product = Product::query()->withTrashed()->findOrFail($id);
        $product->forceDelete();
    }

    public function restore(int $id): ProductData
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return ProductData::fromModel($product->fresh(['category']));
    }
}
