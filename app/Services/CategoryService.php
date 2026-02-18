<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Get all categories.
     */
    public function getAllCategories(): Collection
    {
        return Category::withCount(['userVerseCategories as classifications_count'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get category by slug.
     */
    public function getBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return Category::create($data);
    }

    /**
     * Update a category.
     */
    public function update(Category $category, array $data): Category
    {
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Get popular categories by classification count.
     */
    public function getPopularCategories(int $limit = 10): Collection
    {
        return Category::withCount('userVerseCategories as classifications_count')
            ->orderBy('classifications_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
