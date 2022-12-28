<?php

namespace App\Http\Controllers\Admin\Spectacles;

use App\Http\Controllers\AdminController;
use App\Models\Spectacle;
use App\Repositories\SpectacleRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use App\Traits\MediaUploadingTrait;
use App\Http\Requests\Spectacles\StoreSpectacleRequest;
use App\Http\Requests\Spectacles\UpdateSpectacleRequest;
use App\Http\Requests\Spectacles\MassDestroySpectacleRequest;
use App\Repositories\CategoryRepository;
use App\Services\Spectacles\SpectacleService;
use App\Models\Schema;
use App\Services\SchemaService;
use Illuminate\Support\Facades\App;
use App\Repositories\VarRepository;

class SpectacleController extends AdminController
{

    use MediaUploadingTrait;

    /**
     * @var SpectacleRepository
     */
    private SpectacleRepository $repository;

    /**
     * @var SpectacleService
     */
    private SpectacleService $service;

    /**
     * SpectacleController constructor.
     *
     * @param SpectacleRepository $repository
     * @param SpectacleService    $service
     */
    public function __construct(SpectacleRepository $repository, SpectacleService $service)
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

        return view('admin.spectacles.index');
    }

    /**
     * @param Spectacle          $spectacle
     * @param CategoryRepository $categoryRepository
     *
     * @return View
     */
    public function create(Spectacle $spectacle, CategoryRepository $categoryRepository) : View
    {
        $categories = $categoryRepository->getListForSelect();
        $schemas = Schema::query()->pluck('name', 'id');

        return view('admin.spectacles.create', compact('spectacle', 'categories', 'schemas'));
    }

    /**
     * @param StoreSpectacleRequest $request
     *
     * @return RedirectResponse
     */
    public function store(StoreSpectacleRequest $request)
    {
        $spectacle = $this->service->createSpectacle($request);

        return redirect()->route('admin.spectacles.show', $spectacle->id);
    }

    /**
     * @param Spectacle $spectacle
     *
     * @return Application|Factory|View
     */
    public function show(Spectacle $spectacle)
    {
        $spectacle->load('categories', 'schema');

        return view('admin.spectacles.show', compact('spectacle'));
    }

    /**
     * @param Spectacle          $spectacle
     * @param CategoryRepository $categoryRepository
     *
     * @return Application|Factory|View
     */
    public function edit(Spectacle $spectacle, CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->getListForSelect();
        $categoryIds = $this->repository->getRelatedCategoryIds($spectacle);
        $schemas = Schema::query()->pluck('name', 'id');

        return view('admin.spectacles.edit', compact('spectacle', 'categories', 'categoryIds', 'schemas'));
    }

    /**
     * @param UpdateSpectacleRequest $request
     * @param Spectacle              $spectacle
     *
     * @return RedirectResponse
     */
    public function update(UpdateSpectacleRequest $request, Spectacle $spectacle)
    {
        $spectacle = $this->service->updateSpectacle($request, $spectacle);

        return redirect()->route('admin.spectacles.show', $spectacle->id);
    }

    /**
     * @param Spectacle $spectacle
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy(Spectacle $spectacle) : RedirectResponse
    {
        $spectacle->delete();

        return back();
    }

    /**
     * @param MassDestroySpectacleRequest $request
     *
     * @return Response
     */
    public function massDestroy(MassDestroySpectacleRequest $request) : Response
    {
        $this->repository->deleteIds($request->ids);

        return response()->noContent();
    }

    /**
     * @param Request       $request
     * @param               $spectacleId
     * @param SchemaService $schemaService
     * @param VarRepository $varRepository
     *
     * @return Application|Factory|View
     */
    public function places(Request $request, $spectacleId, SchemaService $schemaService, VarRepository $varRepository)
    {
        /** @var Spectacle $spectacle */
        $spectacle = Spectacle::query()->find($spectacleId);
        $spectacle->load('schema', 'orders');



        $rows = $schemaService->generateRowsData($spectacle->schema, $spectacle);
        dd($rows);

        $vars = [];
        foreach ($varRepository->getAllVars() as $key => $var) {
            $key = str_replace('_' . App::getLocale(), '', $key);
            $vars[$key] = $var;
        }

        return view('admin.schemas.places', compact('spectacle', 'rows', 'vars'));
    }

}
