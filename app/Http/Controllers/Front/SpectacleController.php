<?php

namespace App\Http\Controllers\Front;

use \Illuminate\Contracts\View\View;
use \Illuminate\Contracts\View\Factory;
use \Illuminate\Contracts\Foundation\Application;
use App\Repositories\CategoryRepository;
use App\Http\Requests\Front\Spectacles\IndexSpectacleRequest;
use App\Services\Spectacles\SpectacleService;
use App\Helpers\CollectionHelper;
use App\Http\Controllers\FrontController;

class SpectacleController extends FrontController
{

    /**
     * @param IndexSpectacleRequest $request
     * @param SpectacleService   $service
     * @param CategoryRepository    $categoryRepository
     *
     * @return Application|Factory|View
     */
    public function index(IndexSpectacleRequest $request, SpectacleService $service, CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->getCollectionToIndex();
        $spectacles = CollectionHelper::paginate($service->getCategoryList($request->category_id), $this->pageLimit)
            ->appends([
                'category_id' => $request->category_id
            ]);;

        return view('front.spectacles.index', compact('spectacles', 'categories'));
    }


    /**
     * @param Spectacle     $spectacle
     * @param SchemaService $schemaService
     *
     * @return Application|Factory|View
     */
    public function show(Spectacle $spectacle, SchemaService $schemaService)
    {
        $schema = $spectacle->schema;

        $rows = $schemaService->generateRowsData($schema, $spectacle);
        $cartTotals = (new CartController())->getGroupTotals();
        $total = \Cart::getTotal();

        return view('front.spectacles.show', compact('spectacle', 'rows', 'cartTotals', 'total'));
    }
}
