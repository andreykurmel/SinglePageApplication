<?php

namespace Vanguard\AppsModules\StimWid;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vanguard\AppsModules\StimWid\Data\DataReceiver;
use Vanguard\AppsModules\StimWid\Data\UserPermisQuery;
use Vanguard\AppsModules\StimWid\Rts\RtsInterface;
use Vanguard\AppsModules\StimWid\Rts\RtsRotate;
use Vanguard\AppsModules\StimWid\Rts\RtsScale;
use Vanguard\AppsModules\StimWid\Rts\RtsTranslate;
use Vanguard\Http\Controllers\Web\Tablda\TableDataController;
use Vanguard\Http\Requests\Tablda\TableData\GetTableDataRequest;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class Loader3D
{
    protected $stimRepo;
    protected $tbRepo;

    protected $app_table;
    protected $master_model;
    protected $excluded_colors;
    protected $front_filters;

    protected $usergroup;
    protected $model;
    protected $curtab;

    /**
     * Loader3D constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->stimRepo = new StimSettingsRepository();
        $this->tbRepo = new TableRepository();

        $this->app_table = $request->app_table;
        $this->master_model = $request->master_model ?: [];
        $this->excluded_colors = $request->excluded_colors ?: [];
        $this->front_filters = $request->front_filters ?: [];

        $this->usergroup = $request->usergroup ?: ' ';
        $this->model = $request->model ?: ' ';
        $this->curtab = $request->curtab ?: ' ';
    }

    /**
     * @return array|null|object
     */
    public function WidSettings()
    {
        $g_setts = $this->get_wid_sett();
        if (!$g_setts) {
            if (auth()->id()) {
                $this->insert_wid_sett();
                $g_setts = $this->get_wid_sett();
            } else {
                $g_setts = [];
            }

            /*$owner_setts = $this->get_wid_sett();

            if ($g_setts && $owner_setts) {
                $owner_setts = (new HelperService())->delSystemFields($owner_setts);
                unset($owner_setts['usergroup']);
                $g_setts = array_merge($g_setts, $owner_setts);
            }*/
        }
        return $g_setts;
    }

    /**
     * @param int|null $user_id
     * @return array|null
     */
    protected function get_wid_sett(int $user_id = null)
    {
        $tbtb = $this->stimRepo->findInheritTb('', 'wid_sett', '3d');
        $ws = (new Model3dService( $tbtb, false ))
            ->findModelAndUser($user_id ?: $this->usergroup, $this->model, $this->curtab)
            ->first();
        if ($ws) {
            $ws['_app_tb'] = $tbtb;
        }
        return $ws;
    }

    /**
     * @return mixed
     */
    protected function insert_wid_sett()
    {
        $sett_tb = $this->stimRepo->findInheritTb('', 'wid_sett', '3d');
        $table_meta = DataReceiver::meta_table($sett_tb);
        $sett_tb_info = DataReceiver::app_table_and_fields($sett_tb);

        $data = [];
        foreach (['usergroup', 'model', 'curtab'] as $appfld) {
            $fld_obj = $sett_tb_info['_app_fields']->where('app_field', '=', $appfld)->first();
            $data[ $fld_obj->data_field ] = $appfld == 'usergroup' ? auth()->id() : $this->{$appfld};
            //prevent 'null'
            if (!$data[ $fld_obj->data_field ]) {
                $data[ $fld_obj->data_field ] = ' ';
            }
        }
        return (new TableDataService())->insertRow($table_meta, $data, auth()->id());
    }

    /**
     * @return array
     */
    public function Structure()
    {
        return [
            'params' => $this->findGeomStructure($this->app_table, $this->master_model, $this->excluded_colors),
            'colors' => $this->findGeoColors($this->app_table, $this->master_model),
        ];
    }

    /**
     * @return array
     */
    public function MA()
    {
        return [
            'params' => $this->findMaStructure($this->app_table, $this->master_model),
            'eqs' => $this->findMaEquipments($this->app_table, $this->master_model, $this->excluded_colors),
            'colors' => $this->findMaColors($this->app_table, $this->master_model),
            'libs' => $this->findMaLibs($this->app_table, $this->master_model),
        ];
    }

    /**
     * @param string $ma_table
     * @param array $ma_model
     * @param array $excluded_colors
     * @return array
     */
    protected function findMaEquipments(string $ma_table, array $ma_model, array $excluded_colors = [])
    {
        $lcs_tb = $this->stimRepo->findInheritTb($ma_table, 'lcs', '3d');
        $eqs_tb = $this->stimRepo->findMasterTb('3d:eqs');

        $ma_maps = (DataReceiver::app_table_and_fields($ma_table))['_app_maps'];
        $ma_tablda = $this->stimRepo->convertReceiverToTablda($ma_maps, $ma_model);
        $lcs_rece = (new Model3dService($lcs_tb))->queryFindModel($ma_tablda, $this->front_filters);
        $lcs = $lcs_rece->get();

        $equps = array_unique( array_pluck($lcs, 'equipment') );
        $eqs_rece = (new Model3dService($eqs_tb))->queryReceiver();
        $found_eq = $eqs_rece->whereIn('model', $equps)->get();
        $found_eq = collect($found_eq);

        //Exclude some LCs
        $results = [];
        foreach ($lcs as $lc) {
            if (!in_array($lc['status'], $excluded_colors)) {
                $arr = [
                    'lc' => $lc,
                    'eq' => $found_eq->where('model', $lc['equipment'])->first(),
                ];
                //origin elev
                $origin = floatval($ma_model['origin_elev'] ?? 0);
                $gctr = floatval($arr['lc']['elev_gctr'] ?? 0);
                //reverse offsets
                $arr['lc']['elev_offset'] = $origin && $gctr ? $gctr-$origin : ($origin ? -1 : 0);
                $arr['lc']['_app_tb'] = $lcs_tb;
                $arr['lc']['_ma_gctr'] = $origin;
                //apply
                $results[] = $arr;
            }
        }
        //-------

        $tablda_lc = $this->tbRepo->getTableByDB($lcs_rece->getModelTable());
        $tablda_eq = $this->tbRepo->getTableByDB($eqs_rece->getModelTable());

        return [
            'collection' => $results,
            'all_statuses' => array_pluck($lcs, 'status'),
            '_lcs_tb' => $tablda_lc,
            '_eqs_tb' => $tablda_eq,
        ];
    }

    /**
     * @param string $ma_table
     * @param array $ma_model
     * @return array
     */
    protected function findMaLibs(string $ma_table, array $ma_model)
    {
        $loading_tb = $this->stimRepo->findMasterTb('3d:loading');
        $tech_tb = $this->stimRepo->findInheritTb($loading_tb, 'tech_list', '2d');
        $status_tb = $this->stimRepo->findInheritTb($loading_tb, 'eqpt_colors', '2d');
        $pos_to_mbr_tb = $this->stimRepo->findInheritTb($loading_tb, 'pos_to_mbr', '3d', true);

        $ma_maps = (DataReceiver::app_table_and_fields($ma_table))['_app_maps'];
        $ma_tablda = $this->stimRepo->convertReceiverToTablda($ma_maps, $ma_model);
        $loading = (new Model3dService( $loading_tb ))->queryFindMaster((string)$ma_model['usergroup'], (string)$ma_model['loading'])->first();

        if (!$loading) {
            return null;
        }

        $loa_maps = (DataReceiver::app_table_and_fields($loading_tb))['_app_maps'];
        $loa_tablda = $this->stimRepo->convertReceiverToTablda($loa_maps, $loading);

        $rec = new Request();
        $rec->app_table = $loading_tb;
        $rec->master_model = $loa_tablda;
        $data2d = new Data2D($rec);

        $tablda_postombr = $this->tbRepo->getTableByDB($pos_to_mbr_tb->data_table);
        $pos_to_mbr_tb->id = $tablda_postombr->id;

        return [
            'eqpt_lib' => $data2d->equipmentsFind($loading_tb, $loa_tablda, 'eqpt_lib'),
            'tech_lib' => $data2d->findData($loading_tb, $loa_tablda, 'tech_list', ['technology']),
            'status_lib' => $data2d->findEqptColors($loading_tb, $loa_tablda),
            'secpos_lib' => $this->findSecPoss($loading_tb, $loa_tablda),
            'popup_tables' => $data2d->getArrayOfInherits(),
            'loa_tablda' => $loa_tablda,
            'ma_tablda' => $ma_tablda,
            'ma_3d' => $ma_model,
            '$pos_to_mbr_tb' => $pos_to_mbr_tb,
            'pos_to_mbrs' => $this->findPosToMbr($loading_tb, $loa_tablda),
        ];
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @return array
     */
    public function findSecPoss(string $app_tb, array $tablda_model)
    {
        $colors_tb = $this->stimRepo->findInheritTb($app_tb, 'secpos', '3d');
        try {
            return (new Model3dService( $colors_tb ))->queryFindModel($tablda_model)->get();
        } catch (\Exception $e) {
            return [ '_error' => $e->getMessage() ];
        }
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @return array
     */
    public function findPosToMbr(string $app_tb, array $tablda_model)
    {
        $pos_to_mbr_tb = $this->stimRepo->findInheritTb($app_tb, 'pos_to_mbr', '3d');
        try {
            return (new Model3dService( $pos_to_mbr_tb ))->queryFindModel($tablda_model)->get();
        } catch (\Exception $e) {
            return [ '_error' => $e->getMessage() ];
        }
    }

    /**
     * @param string $geom_table
     * @param array $geo_model
     * @param array $excluded_colors
     * @return array
     */
    protected function findGeomStructure(string $geom_table, array $geo_model, array $excluded_colors = [])
    {
        $geometry_maps = (DataReceiver::app_table_and_fields($geom_table))['_app_maps'];
        $geom_tablda = $this->stimRepo->convertReceiverToTablda($geometry_maps, $geo_model);

        //app tbs
        $mat_tb = $this->stimRepo->findInheritTb($geom_table, 'materials', '3d');
        $node_tb = $this->stimRepo->findInheritTb($geom_table, 'nodes', '3d');
        $sec_tb = $this->stimRepo->findInheritTb($geom_table, 'sections', '3d');
        $mem_tb = $this->stimRepo->findInheritTb($geom_table, 'members', '3d');

        //receivers
        $mat_rece = (new Model3dService( $mat_tb ))->queryFindModel($geom_tablda, $this->front_filters);
        $node_rece = (new Model3dService( $node_tb ))->queryFindModel($geom_tablda, $this->front_filters);
        $sect_rece = (new Model3dService( $sec_tb ))->queryFindModel($geom_tablda, $this->front_filters);
        $memb_rece = (new Model3dService( $mem_tb ))->queryFindModel($geom_tablda, $this->front_filters);

        $avail_members = [];
        if ($geo_model) {
            //rows
            $materials = $mat_rece->get();
            $nodes = $node_rece->get();
            $sections = $sect_rece->get();
            $members = $memb_rece->get();

            //Exclude some Materials and Members
            $exclude_materials = [];
            foreach ($materials as $mat) {
                if (in_array($mat['color_gr'], $excluded_colors)) {
                    $exclude_materials[] = $mat['name'];
                }
            }

            foreach ($members as $member) {
                if (!in_array($member['Mat'], $exclude_materials)) {
                    $avail_members[] = $member;
                }
            }
            //-------
        } else {
            $materials = [];
            $nodes = [];
            $sections = [];
            $members = [];
        }

        //tablda tables
        $tablda_mat = $this->tbRepo->getTableByDB($mat_rece->getModelTable());
        $tablda_node = $this->tbRepo->getTableByDB($node_rece->getModelTable());
        $tablda_sect = $this->tbRepo->getTableByDB($sect_rece->getModelTable());
        $tablda_memb = $this->tbRepo->getTableByDB($memb_rece->getModelTable());

        return [
            'found_model' => $geo_model,
            'materials' => $materials,
            'nodes' => $nodes,
            'sections' => $sections,
            'members' => $avail_members,
            '_materials_tb' => $tablda_mat,
            '_nodes_tb' => $tablda_node,
            '_sections_tb' => $tablda_sect,
            '_members_tb' => $tablda_memb,
        ];
    }

    /**
     * @param string $ma_table
     * @param array $ma_model
     * @return array
     */
    protected function findMaStructure(string $ma_table, array $ma_model)
    {
        $ma_maps = (DataReceiver::app_table_and_fields($ma_table))['_app_maps'];
        $ma_tablda = $this->stimRepo->convertReceiverToTablda($ma_maps, $ma_model);

        $geom_tb = $this->stimRepo->findMasterTb('3d:geom');
        if ($geom_tb) {
            $geom_row = (new Model3dService($geom_tb))->queryFindMaster((string)$ma_model['usergroup'], (string)$ma_model['structure'], $this->front_filters)->first();
        }

        $structure = $this->findGeomStructure($geom_tb, $geom_row ?? []);
        $structure['found_model'] = $ma_model;
        return $structure;
    }

    /**
     * @param string $ma_table
     * @param array $ma_model
     * @return array of arrays
     */
    protected function findMaColors(string $ma_table, array $ma_model)
    {
        $colors_tb = $this->stimRepo->findInheritTb($ma_table, 'colors_eq', '3d');

        $ma_maps = (DataReceiver::app_table_and_fields($ma_table))['_app_maps'];
        $ma_tablda = $this->stimRepo->convertReceiverToTablda($ma_maps, $ma_model);

        $geom_tb = $this->stimRepo->findMasterTb('3d:geom');
        if ($geom_tb) {
            $geom_row = (new Model3dService($geom_tb))->queryFindMaster((string)$ma_model['usergroup'], (string)$ma_model['model'], $this->front_filters)->first();
        }

        try {
            return [
                'ma' => (new Model3dService( $colors_tb ))->queryFindModel($ma_tablda, $this->front_filters)->get(),
                'geom' => $this->findGeoColors($geom_tb, $geom_row ?? []),
            ];
        } catch (\Exception $e) {
            return [ '_error' => $e->getMessage() ];
        }
    }

    /**
     * @param string $geo_table
     * @param array $geo_model
     * @return array
     */
    protected function findGeoColors(string $geo_table, array $geo_model)
    {
        $geo_colors_tb = $this->stimRepo->findInheritTb($geo_table, 'colors_mem', '3d');

        $geo_maps = (DataReceiver::app_table_and_fields($geo_table))['_app_maps'];
        $geo_tablda = $this->stimRepo->convertReceiverToTablda($geo_maps, $geo_model);

        try {
            return (new Model3dService( $geo_colors_tb ))->queryFindModel($geo_tablda, $this->front_filters)->get();
        } catch (\Exception $e) {
            return [ '_error' => $e->getMessage() ];
        }
    }
}