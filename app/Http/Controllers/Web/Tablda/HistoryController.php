<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\History\HistoryFieldGetRequest;
use Vanguard\Http\Requests\Tablda\History\DeleteHistoryRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\HistoryRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class HistoryController extends Controller
{
    private $historyRepository;
    private $tableService;

    /**
     * HistoryController constructor.
     *
     * @param TableService $tableService
     */
    public function __construct(TableService $tableService)
    {
        $this->historyRepository = new HistoryRepository();
        $this->tableService = $tableService;
    }

    /**
     * Get History for selected Field.
     *
     * @param HistoryFieldGetRequest $request
     * @return mixed
     */
    public function get(HistoryFieldGetRequest $request) {
        $user = auth()->check() ? auth()->user() : new User();
        $header = (new TableFieldRepository())->getField($request->table_field_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $header ? $header->_table  :null]);

        return [
            'current' => $this->historyRepository->getCurrentHistory($table, $header, $request->row_id),
            'history' => $this->historyRepository->getFieldHistory($request->table_field_id, $request->row_id)
        ];
    }

    /**
     * Delete history record for selected column.
     *
     * @param DeleteHistoryRequest $request
     * @return array
     */
    public function delete(DeleteHistoryRequest $request) {
        $history = $this->historyRepository->getHistory($request->history_id);
        $this->authorize('update', [TableData::class, $history ? $history->_table : null]);

        return [ 'status' => $this->historyRepository->delete($history) ];
    }
}
