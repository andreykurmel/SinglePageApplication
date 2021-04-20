<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\AppsModules\StimWid\StimAppViewRepository;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Request;
use Vanguard\Http\Requests\Tablda\TableView\DeleteTableViewRequest;
use Vanguard\Http\Requests\Tablda\TableView\DeleteViewRightRequest;
use Vanguard\Http\Requests\Tablda\TableView\PassTableViewRequest;
use Vanguard\Http\Requests\Tablda\TableView\PostTableViewRequest;
use Vanguard\Http\Requests\Tablda\TableView\PostViewRightRequest;
use Vanguard\Http\Requests\Tablda\TableView\PutTableViewRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableView;
use Vanguard\Models\Table\TableViewRight;
use Vanguard\Policies\TableViewPolicy;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\User;
use Illuminate\Http\Request as LRequest;

class TableViewController extends Controller
{
    private $tableViewRepository;
    private $tableRepository;
    private $folderRepository;

    /**
     * TableViewController constructor.
     *
     * @param TableViewRepository $tableViewRepository
     */
    public function __construct(
        TableViewRepository $tableViewRepository,
        TableRepository $tableRepository,
        FolderRepository $folderRepository
    ) {
        $this->tableViewRepository = $tableViewRepository;
        $this->tableRepository = $tableRepository;
        $this->folderRepository = $folderRepository;
    }

    /**
     * Insert Table View.
     *
     * @param PostTableViewRequest $request
     * @return TableView
     */
    public function insert(PostTableViewRequest $request) {
        $table = $this->tableRepository->getTable($request->table_id);
        $this->authorize('load', [TableData::class, $table]);
        $ul = $request->fields['user_link'] ?? '';

        if ($ul && $this->tableViewRepository->checkAddress($table->id, $ul)) {
            return response('Address taken! Enter a different one.', 400);
        } else {
            return $this->tableViewRepository->insertView( $table, array_merge($request->fields, ['table_id' => $table->id, 'user_id' => auth()->id()]) );
        }
    }

    /**
     * Update Table View.
     *
     * @param PutTableViewRequest $request
     * @return TableView
     */
    public function update(PutTableViewRequest $request) {
        $table = $this->tableRepository->getTable($request->table_id);
        $this->authorize('load', [TableData::class, $table]);
        return $this->tableViewRepository->updateView( $request->view_id, array_merge($request->fields, ['table_id' => $table->id, 'user_id' => auth()->id()]) );
    }

    /**
     * Delete Table View.
     *
     * @param DeleteTableViewRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(DeleteTableViewRequest $request) {
        $view = $this->tableViewRepository->getView($request->table_view_id);

        $this->authorize('isOwner', [TableView::class, $view]);

        return ['status' => $this->tableViewRepository->deleteView($request->table_view_id)];
    }

    /**
     * Insert Table View Right.
     *
     * @param PostViewRightRequest $request
     * @return TableViewRight
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertRight(PostViewRightRequest $request) {
        $view = $this->tableViewRepository->getView($request->table_view_id);

        $this->authorize('isOwner', [TableView::class, $view]);

        return $this->tableViewRepository->insertRight([
            'table_view_id' => $request->table_view_id,
            'table_permission_id' => $request->table_permission_id
        ]);
    }

    /**
     * Delete Table View Right.
     *
     * @param DeleteViewRightRequest $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteRight(DeleteViewRightRequest $request) {
        $view = $this->tableViewRepository->getView($request->table_view_id);

        $this->authorize('isOwner', [TableView::class, $view]);

        return ['status' => $this->tableViewRepository->deleteRight($request->table_view_right_id)];
    }

    /**
     * @param LRequest $request
     * @return mixed
     */
    public function insertFiltering(LRequest $request) {
        $view = $this->tableViewRepository->getView($request->table_view_id);

        $this->authorize('isOwner', [TableView::class, $view]);

        return $this->tableViewRepository->insertFiltering( array_merge($request->fields, ['table_view_id' => $view->id]) );
    }

    /**
     * @param LRequest $request
     * @return mixed
     */
    public function updateFiltering(LRequest $request) {
        $view = $this->tableViewRepository->getView($request->table_view_id);

        $this->authorize('isOwner', [TableView::class, $view]);

        return $this->tableViewRepository->updateFiltering(
            $request->table_view_filtering_id,
            array_merge($request->fields, ['table_view_id' => $view->id])
        );
    }

    /**
     * @param LRequest $request
     * @return array
     */
    public function deleteFiltering(LRequest $request) {
        $view = $this->tableViewRepository->getView($request->table_view_id);

        $this->authorize('isOwner', [TableView::class, $view]);

        return ['status' => $this->tableViewRepository->deleteFiltering($request->table_view_filtering_id)];
    }

    /**
     * Check pass for Table View (or Folder View)
     *
     * @param PassTableViewRequest $request
     * @return mixed
     */
    public function lockPass(PassTableViewRequest $request) {
        $tb_view = $this->tableViewRepository->getByHash($request->tb_view_hash);
        $fld_view = $this->folderRepository->getByHash($request->fld_view_hash);
        $app_view = (new StimAppViewRepository())->getByHash($request->app_view_hash);

        return [
            'status' => ($tb_view && $tb_view->lock_pass == $request->pass)
                ||
                ($fld_view && $fld_view->lock_pass == $request->pass)
                ||
                ($app_view && $app_view->lock_pass == $request->pass)
        ];
    }
}
