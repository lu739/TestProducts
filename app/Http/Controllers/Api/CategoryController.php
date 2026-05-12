<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function index(): CategoryCollection
    {
        return new CategoryCollection($this->categoryRepository->all());
    }
}
