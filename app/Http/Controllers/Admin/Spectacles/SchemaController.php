<?php

namespace App\Http\Controllers\Admin\Spectacles;

use App\Http\Controllers\AdminController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\LabelHelper;
use App\Models\Schema;
use Illuminate\Http\RedirectResponse;
use App\Models\ColorSchema;

class SchemaController extends AdminController
{

    /**
     * @param Request $request
     *
     * @return Application|Factory|View|mixed
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Schema::query()->get())
                ->addColumn('placeholder', '&nbsp;')
                ->editColumn('id', fn ($row) => $row->id)
                ->editColumn('name', fn ($row) => $row->name)
                ->editColumn('active', fn ($row) => LabelHelper::boolLabel($row->active))
                ->editColumn('created_at', fn ($row) => $row->created_at)
                ->addColumn('actions', function ($row) {
                    $entityName = 'schemas';
                    return view("admin.partials.datatables-actions-show-read-schema", compact(
                        'entityName',
                        'row'
                    ));
                })
                ->rawColumns(['actions', 'placeholder', 'active'])
                ->make(true);
        }

        return view('admin.schemas.index');
    }

    /**
     * @param Schema $schema
     * http://satiricus.lara/admin/schemas/60061
     * @return Application|Factory|View
     */
    public function show(Schema $schema)
    {
        $schema->load('colors');
//        dd($schema->rows->toArray());

        return view('admin.schemas.show', compact('schema'));
    }

    /**
     * @param Schema $schema
     *
     * @return Application|Factory|View
     */
    public function schema(Schema $schema)
    {
        $schema->load('colors');

        return view('admin.schemas.raw-places', compact('schema'));
    }

    /**
     * @param Schema $schema
     *
     * @return Application|Factory|View
     */
    public function edit(Schema $schema)
    {
        return view('admin.schemas.edit', compact('schema'));
    }

    /**
     * @param Request $request
     * @param Schema  $schema
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Schema $schema)
    {
        $schema->update($request->all());

        return redirect()->route('admin.schemas.show', $schema->id);
    }

    /**
     * @param Request $request
     * @param         $pivotColorSchemaId
     *
     * @return Application|Factory|View
     */
    public function editPrice(Request $request, $pivotColorSchemaId)
    {
        $colorPivot = ColorSchema::query()->findOrFail($pivotColorSchemaId);
        $schemaId = $request->schema_id;

        return view('admin.schemas.edit-price', compact('colorPivot', 'schemaId'));
    }

    /**
     * @param Request $request
     * @param         $pivotColorSchemaId
     *
     * @return RedirectResponse
     */
    public function updatePrice(Request $request, $pivotColorSchemaId)
    {
        $colorPivot = ColorSchema::query()->findOrFail($pivotColorSchemaId);
        $colorPivot->price = $request->price;
        $colorPivot->save();

        return redirect()->route('admin.schemas.show', $request->schema_id);
    }

}
