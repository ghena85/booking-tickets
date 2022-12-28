<?php

namespace App\Services\Spectacles;

use App\Helpers\DatatablesHelper;
use App\Helpers\LabelHelper;
use App\Http\Requests\Spectacles\StoreSpectacleRequest;
use App\Http\Requests\Spectacles\UpdateSpectacleRequest;
use App\Models\Spectacle;
use App\Repositories\SpectacleRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\MediaHelper;
use App\Helpers\ImageHelper;
use App\Repositories\CategoryRepository;
use \Illuminate\Support\Collection;

class SpectacleService
{
    /**
     * @var SpectacleRepository
     */
    public SpectacleRepository $repository;

    /**
     * DictionaryService constructor.
     *
     * @param SpectacleRepository $repository
     */
    public function __construct(SpectacleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getDatatablesData()
    {
        $collection = $this->repository->getCollectionToIndex();

        return Datatables::of($collection)
            ->addColumn('placeholder', '&nbsp;')
            ->editColumn('id', fn ($row) => $row->id)
            ->editColumn('name', fn ($row) => $row->name)
            ->editColumn('una', fn ($row) => LabelHelper::unaSpectacleLabel($row))
            ->editColumn('author', fn ($row) => $row->author)
            ->editColumn('producer', fn ($row) => $row->producer)
            ->editColumn('slug', fn ($row) => $row->slug)
            ->editColumn('min_age', fn ($row) => $row->min_age)
            ->editColumn('duration', fn ($row) => $row->duration)
            ->editColumn('active', fn ($row) => LabelHelper::boolLabel($row->active))
            ->editColumn('is_premiera', fn ($row) => LabelHelper::boolLabel($row->is_premiera))
            ->editColumn('created_at', fn ($row) => $row->created_at)
            ->addColumn('image', fn ($row) => ImageHelper::fullImageAdmin($row->image_grid))
            ->addColumn('actions', fn ($row) => DatatablesHelper::renderActionsRow($row, 'spectacles'))
            ->rawColumns(['actions', 'placeholder', 'active','una', 'is_premiera', 'image', 'schema'])
            ->make(true);
    }

    /**
     * @param int|null $categoryId
     *
     * @return Collection
     */
    public function getCategoryList(? int $categoryId)
    {
        if ($categoryId) {
            return app(CategoryRepository::class)->getSpectacles($categoryId);
        }

        return $this->repository->getActiveSpectacles();
    }

    /**
     * @param StoreSpectacleRequest $request
     *
     * @return Spectacle
     */
    public function createSpectacle(StoreSpectacleRequest $request) : Spectacle
    {
        $inputData = $request->all();
        $spectacle = $this->repository->saveSpectacle($inputData);

        $this->handleMediaFiles($request, $spectacle);
        $this->handleRelationships($spectacle, $request);

        return $spectacle;
    }

    /**
     * @param UpdateSpectacleRequest $request
     * @param Spectacle              $spectacle
     *
     * @return Spectacle
     */
    public function updateSpectacle(UpdateSpectacleRequest $request, Spectacle $spectacle) : Spectacle
    {
        $this->handleMediaFiles($request, $spectacle);
        $this->handleRelationships($spectacle, $request);

        return $this->repository->updateData($request->validated(), $spectacle);
    }

    /**
     * @param StoreSpectacleRequest|UpdateSpectacleRequest   $request
     * @param Spectacle $spectacle
     */
    private function handleMediaFiles($request, Spectacle $spectacle) : void
    {
        MediaHelper::handleMedia($spectacle, 'image_grid', $request->image_grid);
        MediaHelper::handleMedia($spectacle, 'image_detail', $request->image_detail);
        MediaHelper::handleMediaCollect($spectacle, 'image_gallery', $request->image_gallery);
    }

    /**
     * @param Spectacle $spectacle
     * @param StoreSpectacleRequest|UpdateSpectacleRequest $request
     */
    private function handleRelationships(Spectacle $spectacle, $request) : void
    {
        $spectacle->categories()->sync($request->category_ids);
    }

}
