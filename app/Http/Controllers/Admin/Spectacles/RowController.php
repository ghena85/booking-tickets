<?php

namespace App\Http\Controllers\Admin\Spectacles;

use App\Http\Controllers\AdminController;
use App\Models\Col;
use App\Models\ColorSchema;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use App\Models\Row;
use Illuminate\Http\RedirectResponse;
use App\Models\Color;

class RowController extends AdminController
{

    /**
     * @param Row $row
     *
     * @return Application|Factory|View
     */
    public function show(Row $row)
    {
        return view('admin.rows.show', compact('row'));
    }

    /**
     * @param Row $row
     *
     * @return Application|Factory|View
     */
    public function edit(Row $row)
    {
        $colors = Color::query()->pluck('name', 'id')->toArray();

        return view('admin.rows.edit', compact('row', 'colors'));
    }

    /**
     * @param Request $request
     * @param Row  $row
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Row $row)
    {
        $row->update($request->all());
//        dd($row->toArray());

        // Update prices for cols
        if($request->color_id > 0) {
            $dataCol = [
                'schema_id' => $row->schema_id,// sala mica
                'row_id' => $row->id,// id from rows table
            ];
            $colList = Col::where($dataCol)->get();
            if($colList) {
                // get color schema id
                $colorSchema = ColorSchema::where('color_id',$row->color_id)->where('schema_id',$row->schema_id)->first();
                if($colorSchema) {
                    $color = Color::find($row->color_id);
                    $dataUpdate = [
                       'color' => $color->name,
                       'color_schema_id' => $colorSchema->id,
                    ];
                    Col::where($dataCol)->update($dataUpdate);
                }
            }
        }

        return redirect()->route('admin.rows.show', $row->id);
    }

}
