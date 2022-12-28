<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Spectacle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryRepository extends Model
{

    /**
     * @return Collection
     */
    public function getListForSelect() : Collection
    {
        return Category::query()
            ->get()
            ->groupBy('id', true)
            ->map(function (Collection $items) {
                return $items->shift()->name;
            });
    }

    /**
     * @return Collection
     */
    public function getCollectionToIndex() : Collection
    {
        return Category::query()->orderBy('order','asc')->get();
    }

    /**
     * @param int $categoryId
     *
     * @return Collection
     */
    public function getSpectacles(int $categoryId) : Collection
    {
        return Category::query()
            ->find($categoryId)
            ->spectacles
            ->sortBy('start_at')
            ->filter(function (Spectacle $spectacle) {
                return $spectacle->active;
            });
    }


    /**
     * @param int $categoryId
     *
     * @return Collection
     */
    public function getEvents(int $categoryId) : Collection
    {
        return Category::query()
            ->find($categoryId)
            ->spectacles
            ->sortBy('start_at')
            ->filter(function (Spectacle $spectacle) {
                return $spectacle->active;
            });
    }

    /**
     * @param array $data
     *
     * @return Category
     */
    public function saveCategory(array $data) : Category
    {
        $category = new Category($data);
        $category->setTranslations('name', $data['name']);
        $category->save();

        return $category->refresh();
    }

    /**
     * @param array    $data
     * @param Category $category
     *
     * @return Category
     */
    public function updateData(array $data, Category $category) : Category
    {
        $category->setTranslations('name', $data['name']);
        $category->update($data);

        return $category->refresh();
    }

    /**
     * @param array $ids
     */
    public function deleteIds(array $ids) : void
    {
        Category::query()
            ->whereIn('id', $ids)
            ->delete();
    }

}
