<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Services\Tablda\TableAiService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableAiController extends Controller
{
    use CanEditAddon;

    /**
     * @var TableAiService
     */
    protected $aiService;

    /**
     * TableTwilioAddonController constructor.
     */
    public function __construct()
    {
        $this->aiService = new TableAiService();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddon($table, 'ai');
        $this->aiService->insert($table, $request->fields);
        $this->aiService->loadForTable($table, auth()->id());
        return $table->_table_ais;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $ai = $this->aiService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $ai->_table;
        $this->canEditAddonItem($table, $ai->_ai_rights());
        $this->aiService->update($table, $request->model_id, $request->fields);
        $this->aiService->loadForTable($table, auth()->id());
        return $table->_table_ais;
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $ai = $this->aiService->get($request->model_id);
        $user = auth()->check() ? auth()->user() : new User();
        $table = $ai->_table;
        $this->canEditAddonItem($table, $ai->_ai_rights());
        $this->aiService->delete($request->model_id);
        $this->aiService->loadForTable($table, auth()->id());
        return $table->_table_ais;
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableAiRight
     * @throws AuthorizationException
     */
    public function toggleAiRight(Request $request)
    {
        $ai = $this->aiService->get($request->ai_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $ai->_table]);
        return $this->aiService->toggleAiRight($ai, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delAiRight(Request $request)
    {
        $ai = $this->aiService->get($request->ai_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $ai->_table]);
        return $this->aiService->deleteAiRight($ai, $request->permission_id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function getAiMessage(Request $request)
    {
        $ai = $this->aiService->get($request->model_id);
        $table = $ai->_table;
        $this->canEditAddonItem($table, $ai->_ai_rights());
        return $this->aiService->getAiMessage($ai, $request->offset);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function sendAiMessage(Request $request)
    {
        $ai = $this->aiService->get($request->model_id);
        $table = $ai->_table;
        $this->canEditAddonItem($table, $ai->_ai_rights());
        return $this->aiService->sendAiMessage($ai, $request->message, $request->request_params ?: []);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function removeAiMessage(Request $request)
    {
        $ai = $this->aiService->get($request->model_id);
        $table = $ai->_table;
        $this->canEditAddonItem($table, $ai->_ai_rights());
        return ['removed' => $this->aiService->removeAiMessage($ai, $request->direct_id)];
    }
}
