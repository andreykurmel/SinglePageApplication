<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\File;
use Vanguard\Models\Table\TableField;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\TabldaApps\Risa3dParser;

class Risa3dParserController extends Controller implements AppControllerInterface
{

    private $bladeVariablesService;

    public function __construct(BladeVariablesService $bladeVariablesService)
    {
        $this->bladeVariablesService = $bladeVariablesService;
        $this->bladeVariablesService->is_app_route = 1;
    }

    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $column = TableField::where('id', $request->file_col)->first();
        $table = $column ? $column->_table : null;

        $file_present = File::where('table_id', $table ? $table->id : null)
            ->where('table_field_id', $column ? $column->id : null)
            ->where('row_id', (int)$request->row_id)
            ->count();

        $lightweight = $correspApp->open_as_popup;

        return view('tablda.applications.risa3d', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'usergroup' => $request->usergroup,
                'mg_name' => $request->mg_name,
                'file_col' => $column ? $column->id : 0,
                'row_id' => (int)$request->row_id,
                'table_id' => $table ? $table->id : 0,
                'file_present' => $file_present,
                'embed' => $lightweight,
            ]
        ));
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function post(Request $request)
    {
        \Validator::make($request->all(), [
            'usergroup' => 'required|string',
            'mg_name' => 'required|string',
            'table_id' => 'required|integer|exists:tables,id',
            'row_id' => 'required|integer',
            'column_id' => 'required|integer|exists:table_fields,id',
        ])->validate();

        $file_content = null;
        $file = File::where('table_id', $request->table_id)
            ->where('table_field_id', $request->column_id)
            ->where('row_id', $request->row_id)
            ->first();

        if ($file) {
            $path = storage_path('app/public/') . $file->filepath . $file->filename;
            $file_content = file_get_contents($path);
        }

        if (!$file_content) {
            throw new \Exception('File not found!');
        }

        try {
            $result = (new Risa3dParser())->parse(
                $request->usergroup, $request->mg_name, $file_content
            );
        } catch (\Exception $e) {
            $result = [$e->getMessage()];
        }
        return $result;
    }

    public function direct_call(DirectCallInput $input)
    {
        //
    }


}
