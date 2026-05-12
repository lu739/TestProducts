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


    public function index(BaseIndexRequest $request): ProductCollection
    {
        $withTrashed = $request->user('sanctum') !== null;

        $paginator = $this->productRepository->paginate(
            $request->toIndexData(),
            $withTrashed,
        );

        return new ProductCollection($paginator);
    }


    public function show(Request $request, int $id): ProductResource
    {
        $withTrashed = $request->user('sanctum') !== null;
        $product = $this->productRepository->findOrFail($id, $withTrashed);

        return new ProductResource($product);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('action', Product::class);
        $validated = $request->validated();

        $product = $this->productRepository->create(ProductData::from($validated));

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $this->authorize('action', Product::class);

        $validated = $request->validated();
        $validated['id'] = $id;
        $updated = $this->productRepository->update(ProductData::from($validated));

        return new ProductResource($updated);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(int $id): Response
    {
        $this->authorize('action', Product::class);

        $this->productRepository->softDelete($id);

        return response()->noContent();
    }

    /**
     * @throws AuthorizationException
     */
    public function forceDestroy(int $id): Response
    {
        $this->authorize('action', Product::class);

        $this->productRepository->forceDelete($id);

        return response()->noContent();
    }

    /**
     * @throws AuthorizationException
     */
    public function restore(int $id): ProductResource
    {
        $this->authorize('action', Product::class);

        $productData = $this->productRepository->restore($id);

        return new ProductResource($productData);
    }
}
