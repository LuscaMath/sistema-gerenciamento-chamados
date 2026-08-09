<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function getAll(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    public function deactivate(Category $category): Category
    {
        $category->update([
            'is_active' => false,
        ]);

        return $category->refresh();
    }

    public function activate(Category $category): Category
    {
        $category->update([
            'is_active' => true,
        ]);

        return $category->refresh();
    }
}