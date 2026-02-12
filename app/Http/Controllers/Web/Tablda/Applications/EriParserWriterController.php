<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Vanguard\AppsModules\EriParserWriter\EriParserWriterModule;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\BladeVariablesService;

class EriParserWriterController extends Controller implements AppControllerInterface
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
     * @return Factory|View
     */
    public function get(Request $request, CorrespApp $correspApp)
    {
        $table_id = (int)$request->table_id;
        $row_id = (int)$request->row_id;
        $link_id = (int)$request->link_id;

        if ($table_id && $row_id && $link_id) {
            $table = (new TableRepository())->getTable($table_id);
            $link = (new TableFieldLinkRepository())->getLink($link_id);
            $parts = (new EriParserWriterModule($table, $link))->getParts($row_id);
        } else {
            $message = 'Not found "table_id", "row_id" or "link_id"!';
        }

        $lightweight = $correspApp->open_as_popup;
        return view('tablda.applications.eri-parser-writer-settings', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'no_settings' => 1,
                'page_title' => $correspApp->name,
                'page_code' => $correspApp->code,
                'embed' => $lightweight,
                'message' => $message ?? '',
                'table_id' => $table_id,
                'link_id' => $link_id,
                'row_id' => $row_id,
                'parts' => json_encode($parts ?? []),
            ]
        ));
    }

    /**
     * @param Request $request
     */
    public function post(Request $request)
    {
        $table_id = (int)$request->table_id;
        $row_id = (int)$request->row_id;
        $link_id = (int)$request->link_id;

        if ($table_id && $row_id && $link_id) {

            try {
                $table = (new TableRepository())->getTable($table_id);
                $link = (new TableFieldLinkRepository())->getLink($link_id);
                switch ($request->page_code) {
                    case 'eri_parser':
                        $message = (new EriParserWriterModule($table, $link))->parse($row_id, $request->active_parts);
                        break;
                    case 'eri_writer':
                        $message = (new EriParserWriterModule($table, $link))->export($row_id, $request->active_parts);
                        break;
                    default:
                        $message = 'Incorrect app!';
                        break;
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }

        } else {
            $message = 'Not found "table_id", "row_id" or "link_id"!';
        }

        return $message;
    }

    /**
     * @param DirectCallInput $input
     * @return DirectCallOut
     */
    public function direct_call(DirectCallInput $input)
    {
        return (new DirectCallOut());
    }

}
