<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\BaseIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
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

        $paginator = $this->productRepository->paginate($request->toIndexData());

        return new ProductCollection($paginator);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(int $id): ProductResource
    {
        $product = $this->productRepository->findOrFail($id);
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

        return new ProductResource(
            $this->productRepository->update($product, $request->validated())
        );
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(int $id): Response
    {
        $product = $this->productRepository->findOrFail($id);
        $this->authorize('delete', $product);
        $this->productRepository->delete($product);

        return response()->noContent();
    }
}
