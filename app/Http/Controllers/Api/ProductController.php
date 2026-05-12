<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\BaseIndexRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function index(BaseIndexRequest $request): ProductCollection
    {
        $paginator = $this->productRepository->paginate($request->toIndexData());

        return new ProductCollection($paginator);
    }

    public function show(int $id): ProductResource
    {
        return new ProductResource($this->productRepository->findOrFail($id));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());
        $product->load('category');

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $product = $this->productRepository->findOrFail($id);

        return new ProductResource(
            $this->productRepository->update($product, $request->validated())
        );
    }

    public function destroy(int $id): Response
    {
        $this->productRepository->delete($id);

        return response()->noContent();
    }
}
