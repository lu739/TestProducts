<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Data\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function index(ProductIndexRequest $request): ProductCollection
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

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = $this->productRepository->create(ProductData::from($validated));

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $validated = $request->validated();
        $validated['id'] = $id;

        $updated = $this->productRepository->update(ProductData::from($validated));

        return new ProductResource($updated);
    }

    public function destroy(int $id): Response
    {
        $this->productRepository->softDelete($id);

        return response()->noContent();
    }

    public function forceDestroy(int $id): Response
    {
        $this->productRepository->forceDelete($id);

        return response()->noContent();
    }

    public function restore(int $id): ProductResource
    {
        $productData = $this->productRepository->restore($id);

        return new ProductResource($productData);
    }
}
