<?php

namespace Vanguard\Repositories\Tablda;

use Ramsey\Uuid\Uuid;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableView;
use Vanguard\Models\Table\TableViewFiltering;
use Vanguard\Models\Table\TableViewRight;
use Vanguard\Modules\QRGenerator;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Services\Tablda\HelperService;

class TableViewRepository
{
    protected $service;

    /**
     * TableViewRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Table View.
     *
     * @param $table_view_id
     * @return \Vanguard\Models\Table\TableView
     */
    public function getView($table_view_id) {
        return TableView::where('id', '=', $table_view_id)->first();
    }

    /**
     * Get Table View By Hash.
     *
     * @param $hash
     * @return mixed
     */
    public function getByHash($hash) {
        return TableView::where('hash', '=', $hash)->first();
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getByCustomUrl($param) {
        return TableView::where('custom_path', '=', $param)->whereHas('_user', function ($q) {
            $q->where('subdomain', '=', $this->service->cur_subdomain);
        })->first();
    }

    /**
     * Get by Table name and Users link.
     *
     * @param array $param : [
     *      0 => table_id,
     *      1 => table_name,
     *      2 => user_link
     * ]
     * @return \Vanguard\Models\Table\TableView
     */
    public function getByTbIdNameAndAddress($param) {
        return TableView::whereHas('_table', function ($t) use ($param) {
                $t->where('id', $param[0]);
            })
            ->where('user_link', $param[2])
            ->first();
    }

    /**
     * Get Table View By Name.
     *
     * @param $table_id
     * @param $user_id
     * @param $name
     * @return TableView
     */
    public function getByName($table_id, $user_id, $name) {
        return TableView::where('table_id', '=', $table_id)
            ->where('user_id', '=', $user_id)
            ->where('name', '=', $name)
            ->first();
    }

    /**
     * @param $link
     * @param $id
     * @return int
     */
    public function checkAddress($link, $id = 0) {
        return TableView::where('id', '!=', $id)
            ->where('custom_path', $link)
            ->count();
    }

    /**
     * @param int $table_id
     * @param TableView|null $sysVi
     * @return string
     */
    public function getVisitingUrl(int $table_id, TableView $sysVi = null)
    {
        $sys = $table_id ? ($sysVi ?: $this->getSys($table_id)) : null;
        return $sys ? '/mrv/'.$sys->hash : '';
    }

    /**
     * @param int $table_id
     * @return mixed
     */
    public function getSys(int $table_id)
    {
        return TableView::where('is_system', '=', 1)->where('table_id', '=', $table_id)->first();
    }

    /**
     * @param Table $table
     */
    public function addSys(Table $table) {
        if (!TableView::where('is_system', '=', 1)->where('table_id', '=', $table->id)->count()) {

            $viPermis = (new TablePermissionRepository())->getSysPermission($table->id, 1);
            if (!$viPermis) {
                return;
            }

            $this->insertView($table, [
                'name' => $this->service->getTableViewSysName(),
                'is_active' => 1,
                'is_system' => 1,
                'access_permission_id' => $viPermis->id,
                'parts_avail' => HelperService::viewPartsDef(),
                'table_id' => $table->id,
                'user_id' => $table->user_id,
            ]);
        }
    }

    /**
     * Insert Table View.
     *
     * @param array $data - example:
     * [
     *  +table_id: int,
     *  +user_id: int,
     *  +name: string
     *  -user_link: string
     *  -access_permission_id: int
     *  -row_group_id: int
     *  -col_group_id: int
     *  +data: //json encoded request data to get selected row.
     * ]
     * @return \Vanguard\Models\Table\TableView
     */
    public function insertView(Table $table, Array $data) {
        //$data['name'] = preg_replace('/[^\w\d_\s]/i', '', $data['name']);
        $data['hash'] = Uuid::uuid4();
        $data['user_id'] = $data['user_id'] ?? null;
        $data['side_top'] = $data['side_top'] ?? 'na';
        $data['side_left_menu'] = $data['side_left_menu'] ?? 'na';
        $data['side_left_filter'] = $data['side_left_filter'] ?? 'show';
        $data['side_right'] = $data['side_right'] ?? 'na';
        $data['srv_fltrs_ontop_pos'] = $data['srv_fltrs_ontop_pos'] ?? 'start';
        $data['is_active'] = 1;
        if (empty($data['col_group_id'])) {
            $visitor_columns = (new TableColGroupRepository())->getSys($table->id);
            $data['col_group_id'] = $visitor_columns->id;
        }
        $data['qr_mrv_link'] = (new QRGenerator())->forMRV($data['hash'])->asPNG();
        $view = TableView::create( array_merge(
            $this->service->delSystemFields($data),
            $this->service->getModified(),
            $this->service->getCreated()
        ) );
        $view->_view_rights = [];
        $view->_filtering = [];
        return $view;
    }

    /**
     * Update Table View.
     *
     * @param array $data - example:
     * [
     *  +table_id: int,
     *  +user_id: int,
     *  +view_name: string
     *  +data: //json encoded request data to get selected row.
     *  -access_permission_id: int
     * ]
     * @param $view_id
     * @return \Vanguard\Models\Table\TableView
     */
    public function updateView($view_id, Array $data) {
        /*if (!empty($data['name'])) {
            $data['name'] = preg_replace('/[^\w\d_\s]/i', '', $data['name']);
        }*/
        $data['side_top'] = $data['side_top'] ?? 'na';
        $data['side_left_menu'] = $data['side_left_menu'] ?? 'na';
        $data['side_left_filter'] = $data['side_left_filter'] ?? 'na';
        $data['side_right'] = $data['side_right'] ?? 'na';
        $data = array_merge(
            $this->service->delSystemFields($data),
            $this->service->getModified(),
            $this->service->getCreated()
        );

        $old = TableView::where('id', '=', $view_id)->first();
        $data['name'] = $data['name'] ?? $old->name;
        $data['mrv_qr_with_name'] = $data['mrv_qr_with_name'] ?? $old->mrv_qr_with_name;

        if ($old->name != $data['name'] || $old->mrv_qr_with_name != $data['mrv_qr_with_name']) {
            $label = $data['mrv_qr_with_name'] ? $data['name'] : '';
            $data['qr_mrv_link'] = (new QRGenerator())->forMRV($old->hash, $label)->asPNG();
        }
        TableView::where('id', '=', $view_id)->update($data);

        return TableView::where('id', '=', $view_id)->first();
    }

    /**
     * Delete Table View.
     *
     * @param int $table_view_id
     * @return mixed
     */
    public function deleteView($table_view_id) {
        return TableView::where('id', $table_view_id)
            ->delete();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insertFiltering(array $data) {
        $data['active'] = 1;
        $data['input_only'] = 1;
        return TableViewFiltering::create( $this->service->delSystemFields($data) );
    }

    /**
     * @param $view_id
     * @param array $data
     * @return mixed
     */
    public function updateFiltering($view_id, array $data) {
        return TableViewFiltering::where('id', '=', $view_id)->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Table View.
     *
     * @param int $table_view_id
     * @return mixed
     */
    public function deleteFiltering($table_view_id) {
        return TableViewFiltering::where('id', '=', $table_view_id)
            ->delete();
    }

    /**
     * Insert Table View Right.
     *
     * @param array $data - example:
     * [
     *  +table_view_id: int,
     *  +user_id: int,
     * ]
     * @return \Vanguard\Models\Table\TableViewRight
     */
    public function insertRight(Array $data) {
        return TableViewRight::create( array_merge(
            $this->service->delSystemFields($data),
            $this->service->getModified(),
            $this->service->getCreated())
        );
    }

    /**
     * Delete Table View Right.
     *
     * @param int $table_view_right_id
     * @return mixed
     */
    public function deleteRight($table_view_right_id) {
        return TableViewRight::where('id', $table_view_right_id)
            ->delete();
    }

    /**
     * Sync columns vit TableViews if Table was changed.
     *
     * @param int $table_id
     * @param array $datas :
     * [
     *  [
     *      +status: string,
     *      +field: string,
     *      +name: string,
     *      +f_type: string,
     *      +f_size: float,
     *      +f_required: int(0|1),
     *  ],
     *  ...
     * ]
     */
    public function syncTableViews($table_id, $datas) {
        $deleted_fields = [];
        foreach ($datas as $data) {
            if ($data['status'] === 'del') {
                $deleted_fields[] = $data['field'];
            }
        }
        if (!$deleted_fields) {
            return;
        }

        $tb_views = TableView::where('table_id', '=', $table_id)->get();
        foreach ($tb_views as $i => $view) {
            $data = json_decode($view->data, 1);

            $data['search_columns'] = collect($data['search_columns'] ?? [])->filter(function ($elem) use ($deleted_fields) {
                return !in_array($elem, $deleted_fields);
            });
            $data['applied_filters'] = collect($data['applied_filters'] ?? [])->filter(function ($elem) use ($deleted_fields) {
                return !in_array($elem['field'], $deleted_fields);
            });

            $view->data = json_encode($data);
            $view->save();
        }
    }
}