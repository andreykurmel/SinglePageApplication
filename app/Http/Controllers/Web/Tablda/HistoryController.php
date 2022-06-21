<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\History\DeleteHistoryRequest;
use Vanguard\Http\Requests\Tablda\History\HistoryFieldGetRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\HistoryRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class HistoryController extends Controller
{
    private $historyRepository;

    /**
     * HistoryController constructor.
     *
     */
    public function __construct()
    {
        $this->historyRepository = new HistoryRepository();
    }

    /**
     * Get History for selected Field.
     *
     * @param HistoryFieldGetRequest $request
     * @return mixed
     */
    public function get(HistoryFieldGetRequest $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $header = (new TableFieldRepository())->getField($request->table_field_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $header ? $header->_table : null]);

        return [
            'current' => $this->historyRepository->getCurrentHistory($header->_table, $header, $request->row_id),
            'history' => $this->historyRepository->getFieldHistory($request->table_field_id, $request->row_id)
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function getAll(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = (new TableService())->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return [
            'histories' => $this->historyRepository->getAll($table, $request->row_id),
            'count' => $this->historyRepository->getCount($table, $request->row_id),
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function postComment(Request $request)
    {
        $user = auth()->check() ? auth()->user() : new User();
        $table = (new TableService())->getTable($request->table_id);
        $this->authorizeForUser($user, 'get', [TableData::class, $table]);

        return [
            'comment' => $this->historyRepository->insertComment($table, $request->row_id, $request->from_user_id, $request->to_user_id, $request->comment)
        ];
    }

    /**
     * Delete history record for selected column.
     *
     * @param DeleteHistoryRequest $request
     * @return array
     */
    public function delete(DeleteHistoryRequest $request)
    {
        $history = $this->historyRepository->getHistory($request->history_id);
        $this->authorize('update', [TableData::class, $history ? $history->_table : null]);

        return ['status' => $this->historyRepository->delete($history)];
    }
}
