<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Data\IndexData;
use App\Data\ProductData;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private Gate $gate,
    ) {}

    public function paginate(IndexData $data, ?bool $withTrashed = false): LengthAwarePaginator
    {
        return Product::query()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->filter($data->filter)
            ->search($data->search)
            ->with('category')
            ->orderByRaw('deleted_at is null desc')
            ->orderByDesc('id')
            ->paginate(perPage: $data->per_page, page: $data->page);
    }

    public function findOrFail(int $id, bool $withTrashed = false): ProductData
    {
        return ProductData::fromModel(Product::query()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->with('category')->findOrFail($id)
        );
    }

    /**
     * @throws AuthorizationException
     */
    public function create(ProductData $data): ProductData
    {
        $this->gate->authorize('action', Product::class);

        $product = Product::query()->create([
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'category_id' => $data->category_id,
        ]);

        return ProductData::fromModel($product->load('category'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ProductData $data): ProductData
    {
        $this->gate->authorize('action', Product::class);

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

    /**
     * @throws AuthorizationException
     */
    public function softDelete(int $id): void
    {
        $this->gate->authorize('action', Product::class);

        $product = Product::query()->findOrFail($id);
        $product->delete();
    }

    /**
     * @throws AuthorizationException
     */
    public function forceDelete(int $id): void
    {
        $this->gate->authorize('action', Product::class);

        $product = Product::query()->withTrashed()->findOrFail($id);
        $product->forceDelete();
    }

    /**
     * @throws AuthorizationException
     */
    public function restore(int $id): ProductData
    {
        $this->gate->authorize('action', Product::class);

        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return ProductData::fromModel($product->fresh(['category']));
    }
}
