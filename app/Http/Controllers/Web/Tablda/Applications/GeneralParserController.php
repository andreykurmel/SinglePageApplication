<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Vanguard\AppsModules\AiExtractMultipleParser\AiExtractMultipleParserModule;
use Vanguard\AppsModules\DaLoadingOcrParser\DaLoadingOcrParserModule;
use Vanguard\AppsModules\MtoDalPdfParser\MtoDalPdfParserModule;
use Vanguard\AppsModules\MtoGeometryParser\MtoGeometryParserModule;
use Vanguard\AppsModules\SmartAutoselectParser\SmartAutoselectParserModule;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\BladeVariablesService;

class GeneralParserController extends Controller implements AppControllerInterface
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

            try {
                $table = (new TableRepository())->getTable($table_id);
                $link = (new TableFieldLinkRepository())->getLink($link_id);
                switch ($correspApp->app_path) {
                    case '/da-loading/ocr-parser':
                        $message = (new DaLoadingOcrParserModule($table, $link))->parse($row_id);
                        break;
                    case '/mto-dal/pdf-parser':
                        $message = (new MtoDalPdfParserModule($table, $link))->parse($row_id);
                        break;
                    case '/mto-dal/geometry-parser':
                        $message = (new MtoGeometryParserModule($table, $link))->parse($row_id);
                        break;
                    case '/ai/extract-multiple':
                        $message = (new AiExtractMultipleParserModule($table, $link))->parse($row_id);
                        break;
                    case '/smart/autoselect':
                        $message = (new SmartAutoselectParserModule($table, $link))->parse($row_id);
                        break;
                    default:
                        $message = 'Incorrect path!';
                        break;
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }

        } else {
            $message = 'Not found "table_id", "row_id" or "link_id"!';
        }

        $lightweight = $correspApp->open_as_popup;
        return view('tablda.applications.simple-messager', array_merge(
            $this->bladeVariablesService->getVariables(null, 0, $lightweight),
            [
                'embed' => $lightweight,
                'messages' => [$message],
                'page_title' => $correspApp->name,
                'no_settings' => 1,
            ]
        ));
    }

    /**
     * @param Request $request
     */
    public function post(Request $request)
    {
        //
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
