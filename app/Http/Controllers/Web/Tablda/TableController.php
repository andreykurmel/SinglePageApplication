<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\AddMessageTableRequest;
use Vanguard\Http\Requests\Tablda\DeleteMessageTableRequest;
use Vanguard\Http\Requests\Tablda\Table\FavoriteToggleRequest;
use Vanguard\Http\Requests\Tablda\GetLinkRequest;
use Vanguard\Http\Requests\Tablda\GetTableRequest;
use Vanguard\Http\Requests\Tablda\MoveNodeRequest;
use Vanguard\Http\Requests\Tablda\PostLinkRequest;
use Vanguard\Http\Requests\Tablda\Table\GetFilterUrlRequest;
use Vanguard\Http\Requests\Tablda\Table\RenameSharedRequest;
use Vanguard\Http\Requests\Tablda\Table\UpdateUserNoteRequest;
use Vanguard\Http\Requests\Tablda\TableData\GetTableDataRequest;
use Vanguard\Http\Requests\Tablda\TransferTableRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\FolderService;
use Vanguard\Services\Tablda\ImportService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;

class TableController extends Controller
{
    private $tables;
    private $fieldRepository;
    private $folderService;
    private $importService;
    private $tableDataService;

    /**
     * TableController constructor.
     * @param TableService $tables
     * @param TableFieldRepository $fieldRepository
     * @param FolderService $folderService
     * @param ImportService $importService
     * @param TableDataService $tableDataService
     */
    public function __construct(
        TableService $tables,
        TableFieldRepository $fieldRepository,
        FolderService $folderService,
        ImportService $importService,
        TableDataService $tableDataService
    )
    {
        $this->tables = $tables;
        $this->fieldRepository = $fieldRepository;
        $this->folderService = $folderService;
        $this->importService = $importService;
        $this->tableDataService = $tableDataService;
    }

    /**
     * Update table info.
     *
     * @param GetTableRequest $request
     * @return mixed
     */
    public function getViews(GetTableRequest $request) {
        $table = $this->tables->getTable($request->table_id);
        $table->_is_owner = $table->user_id == auth()->id();

        $views = $table->_views()->where('user_id', auth()->id());
        if (!$table->_is_owner) {
            //get only 'shared' tableViews for regular User.
            $views->orWhereHas('_table_permissions', function ($tp) {
                $tp->applyIsActiveForUserOrPermission();
            });
        }

        $cur_settings = $table->_cur_settings()->select('initial_view_id')->first();

        return [
            'views' => $views->get(),
            'settings' => $cur_settings ?: ['initial_view_id' => -1],
            'meta' => $table->getAttributes(),
            'theme' => $table->_theme
        ];
    }

    /**
     * Get meta settings.
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function settingsMeta()
    {
        return $this->tables->getSystemHeaders( auth()->id() );
    }

    /**
     * Get Correspondence Tables.
     *
     * @return mixed
     */
    public function getCorrespondenceTables()
    {
        return $this->tables->getCorrespondenceTables();
    }

    /**
     * Get Correspondence Not Used Fields.
     *
     * @return mixed
     */
    public function getCorrespondenceUsedFields(Request $request)
    {
        return ['rows' => $this->tables->getCorrespondenceUsedFields($request->row)];
    }

    /**
     * Update table info.
     *
     * @param GetTableRequest $request
     * @return mixed
     */
    public function saveStatuse(GetTableRequest $request) {
        $this->tables->saveStatuse($request->table_id, auth()->id(), $request->status_data);
        return 'saved';
    }

    /**
     * Update table info.
     *
     * @param GetTableRequest $request
     * @return mixed
     */
    public function update(GetTableRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        if (auth()->user()->can('isOwner', [TableData::class, $table])) {
            $data = $request->except('table_id');
        } else {
            $data = $request->only('initial_view_id','notes');
        }

        $resp = $this->tables->updateTable($request->table_id, $data, auth()->id());
        return response($resp, (empty($resp['error']) ? 200 : 500));
    }

    /**
     * Link table to folder.
     *
     * @param PostLinkRequest $request
     * @return array
     */
    public function createLink(PostLinkRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        return $this->tables->createLink($request->table_id, $request->folder_id, $request->type, $request->structure, $request->path, auth()->id());
    }

    /**
     * Delete table link from folder
     *
     * @param GetLinkRequest $request
     * @return array|bool|null
     */
    public function deleteLink(GetLinkRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        return $this->tables->deleteLink($request->link_id);
    }

    /**
     * Transfer table from the user`s folders tree to another user.
     *
     * @param TransferTableRequest $request
     * @return array|bool|null
     */
    public function transfer(TransferTableRequest $request) {
        $table = $this->tables->getTable($request->id);

        $this->authorize('isOwner', [TableData::class, $table]);

        return ['status' => $this->tables->transferTable($table, $request->new_user_id)];
    }

    /**
     * Copy table from the user`s folders tree to another user.
     *
     * @param TransferTableRequest $request
     * @return array|bool|null
     */
    public function copy(TransferTableRequest $request) {
        $table = $this->tables->getTable($request->id);

        $this->authorize('isOwner', [TableData::class, $table]);

        if ($request->new_name) {
            $table->name = $request->new_name;
        }
        return $this->importService->saveCopyTable($table, $request->new_user_id, $request->table_with, !!$request->overwrite);
    }

    /**
     * Copy table from the user`s folders tree to another user.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|bool|null
     */
    public function copyEmbed(\Illuminate\Http\Request $request) {
        $hash = [];
        
        if (preg_match('/\/embed_folder\//i', $request->embed_code)) {
            $is_folder = true;
            preg_match('/embed_folder\/([\w\d-]+)/i', $request->embed_code, $hash);
        } else {
            $is_folder = false;
            preg_match('/embed\/([\w\d-]+)/i', $request->embed_code, $hash);
        }
        
        $hash = $hash[1] ?? '';
        $new_user_id = auth()->id();
        $res = [];

        if ($is_folder) {
            $arr = $this->folderService->getFolderByViewHash($hash);
            if ($arr['folder']) {
                $folder = $arr['folder'];
                $this->folderService->getSubTree($folder, $arr['avail_tables']);
                $jstree_folder = (array)$folder->_sub_tree[0];
                $res = $this->importService->saveCopyFolderWithSubs($jstree_folder, $new_user_id);
            }
            $name = $arr['folder']->name ?? '';
        } else {
            $table = $this->tables->getTableByViewHash($hash);
            $res = $this->importService->saveCopyTable($table, $new_user_id);
            $name = $res['new_table']->name ?? '';
        }
        
        if (!empty($res['error'])) {
            return $res;
        }

        return ['href' => '/data/TRANSFERRED/' . $name];
    }

    /**
     * Add Message to Table from the user to another user.
     *
     * @param AddMessageTableRequest $request
     * @return array|bool|null
     */
    public function addMessage(AddMessageTableRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        $this->authorize('get', [TableData::class, $table]);

        return $this->tables->addMessage($request->table_id, auth()->id(), $request->to_user_id, $request->to_user_group_id, $request->message);
    }

    /**
     * Delete Message from Table.
     *
     * @param DeleteMessageTableRequest $request
     * @return array|bool|null
     */
    public function deleteMessage(DeleteMessageTableRequest $request) {
        $msg = $this->tables->getMessage($request->msg_id);
        $table = $msg->_table;

        $this->authorize('get', [TableData::class, $table]);

        return ['status' => $this->tables->deleteMessage($msg->id)];
    }

    /**
     * Move table or link to another folder.
     *
     * @param MoveNodeRequest $request
     * @return array|bool|null
     */
    public function move(MoveNodeRequest $request) {
        $table = $this->tables->getTable($request->id);

        $this->authorize('get', [TableData::class, $table]);

        return ['path' => $this->tables->moveTable($table, auth()->user(), $request->link_id, $request->folder_id, $request->position)];
    }

    /**
     * Toggle table in favorite for user.
     *
     * @param FavoriteToggleRequest $request
     * @return array|bool|null
     */
    public function favorite(FavoriteToggleRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        $this->authorize('get', [TableData::class, $table]);

        return $this->tables->favoriteToggle($request->table_id, auth()->id(), (boolean)$request->favorite);
    }

    /**
     * Update table note for user.
     *
     * @param UpdateUserNoteRequest $request
     * @return mixed
     */
    public function updateUserNote(UpdateUserNoteRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        $this->authorize('get', [TableData::class, $table]);

        return ['status' => $this->tables->updateUserNote($request->table_id, auth()->id(), $request->notes)];
    }

    /**
     * Get url for open table with applied filter.
     *
     * @param GetFilterUrlRequest $request
     * @return mixed
     */
    public function getFilterUrl(GetFilterUrlRequest $request) {
        $table = $this->tables->getTable($request->table_id);

        $this->authorize('get', [TableData::class, $table]);

        $table_field = $this->fieldRepository->getField($request->field_id);

        return $this->tables->getTablePath($table) . '?' . $table_field->field . '=' . $request->value;
    }

    /**
     * Get list of available tables
     *
     * @return mixed
     */
    public function getAvailableTables() {
        return $this->tables->getAvailableTables(auth()->id());
    }

    /**
     * Create alias for shared table.
     *
     * @param RenameSharedRequest $request
     * @return mixed
     */
    public function renameShared(RenameSharedRequest $request) {
        return $this->tables->renameSharedTable($request->table_id, auth()->id(), $request->name);
    }

    /**
     * Create alias for shared table.
     *
     * @param GetTableRequest $request
     * @return array
     */
    public function versionHash(GetTableRequest $request) {
        if ($request->table_id) {
            $table = $this->tables->getTable($request->table_id);
            /*$list_hashes = $request->row_list_ids ? $this->tableDataService->getRowsHashes($table, $request->row_list_ids) : [];
            $fav_hashes = $request->row_fav_ids ? $this->tableDataService->getRowsHashes($table, $request->row_fav_ids) : [];*/
            return [
                'version_hash' => $table->version_hash,
                'num_rows' => $table->num_rows,
                /*'list_hashes' => $list_hashes,
                'fav_hashes' => $fav_hashes,*/
            ];
        } else {
            $tables = $this->tables->getTables(array_keys($request->id_object));
            return [
                'version_hashes' => $tables->pluck('version_hash'),
            ];
        }
    }
}
