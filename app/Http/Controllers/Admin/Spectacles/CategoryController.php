<?php

namespace App\Http\Controllers\Admin\Spectacles;

use App\Http\Controllers\AdminController;
use App\Http\Requests\Categories\MassDestroyCategoryRequest;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use App\Services\Spectacles\CategoryService;

class CategoryController extends AdminController
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $repository;

    /**
     * @var CategoryService
     */
    private CategoryService $service;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepository $repository
     * @param CategoryService    $service
     */
    public function __construct(CategoryRepository $repository, CategoryService $service)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * @param Request $request
     *
     * @return Application|Factory|View|mixed
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getDatatablesData();
        }

        return view('admin.categories.index');
    }

    /**
     * @return View
     */
    public function create() : View
    {
        return view('admin.categories.create');
    }

    /**
     * @param StoreCategoryRequest $request
     *
     * @return RedirectResponse
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = $this->service->createCategory($request);

        return redirect()->route('admin.categories.show', $category->id);
    }

    /**
     * @param Category $category
     *
     * @return Application|Factory|View
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * @param Category $category
     *
     * @return Application|Factory|View
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * @param UpdateCategoryRequest $request
     * @param Category              $category
     *
     * @return RedirectResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->service->updateDictionary($request, $category);

        return redirect()->route('admin.categories.show', $category->id);
    }

    /**
     * @param Category $category
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy(Category $category) : RedirectResponse
    {
        $category->delete();

        return back();
    }

    /**
     * @param MassDestroyCategoryRequest $request
     *
     * @return Response
     */
    public function massDestroy(MassDestroyCategoryRequest $request) : Response
    {
        $this->repository->deleteIds($request->ids);

        return response()->noContent();
    }

}
