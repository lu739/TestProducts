<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Data\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BaseIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function index(BaseIndexRequest $request): ProductCollection
    {
        $this->authorize('viewAny', Product::class);

        $withTrashed = $request->user('sanctum') !== null;

        $paginator = $this->productRepository->paginate(
            $request->toIndexData(),
            $withTrashed,
        );

        return new ProductCollection($paginator);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Request $request, int $id): ProductResource
    {
        $withTrashed = $request->user('sanctum') !== null;
        $product = $this->productRepository->findOrFail($id, $withTrashed);
        $this->authorize('view', $product);

        return new ProductResource($product);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productRepository->create($request->validated());
        $product->load('category');

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $product = $this->productRepository->findOrFail($id);
        $this->authorize('update', $product);

        $productData = ProductData::fromProductForUpdate($product, $request->validated());
        $updated = $this->productRepository->update($productData);

        return new ProductResource($updated);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(int $id): Response
    {
        $product = $this->productRepository->findOrFail($id, true);
        $this->authorize('delete', $product);
        if ($product->trashed()) {
            return response()->noContent();
        }
        $this->productRepository->softDelete($id);

        return response()->noContent();
    }

    /**
     * @throws AuthorizationException
     */
    public function forceDestroy(int $id): Response
    {
        $product = $this->productRepository->findOrFail($id, true);
        $this->authorize('forceDelete', $product);
        $this->productRepository->forceDelete($id);

        return response()->noContent();
    }

    /**
     * @throws AuthorizationException
     */
    public function restore(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id, true);
        $this->authorize('restore', $product);

        if (! $product->trashed()) {
            return response()->json([
                'message' => 'Товар не скрыт, восстановление не требуется.',
            ], 422);
        }

        $productData = $this->productRepository->restore($id);

        return new ProductResource($productData);
    }
}
