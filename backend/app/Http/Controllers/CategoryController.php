<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $service
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Category::class);

        return CategoryResource::collection(
            $this->service->getAll()
        );
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $this->authorize('create', Category::class);

        $category = $this->service->create(
            $request->validated()
        );

        return new CategoryResource($category);
    }

    public function show(Category $category): CategoryResource
    {
        $this->authorize('view', $category);

        return new CategoryResource($category);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): CategoryResource {
        $this->authorize('update', $category);

        $category = $this->service->update(
            $category,
            $request->validated()
        );

        return new CategoryResource($category);
    }

    public function deactivate(Category $category): CategoryResource
    {
        $this->authorize('deactivate', $category);

        $category = $this->service->deactivate($category);

        return new CategoryResource($category);
    }

    public function activate(Category $category): CategoryResource
    {
        $this->authorize('activate', $category);

        $category = $this->service->activate($category);

        return new CategoryResource($category);
    }
}