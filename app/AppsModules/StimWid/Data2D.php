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
use Vanguard\Services\Tablda\TableDataService;

class Data2D
{
    protected $stimRepo;
    protected $dataRepo;

    protected $app_table;
    protected $master_model;
    protected $just_filters;

    /**
     * Loader3D constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->stimRepo = new StimSettingsRepository();
        $this->dataRepo = new TableDataRepository();

        $this->app_table = $request->app_table;
        $this->master_model = $request->master_model ?: [];
        $this->just_filters = !!$request->just_filters;
    }

    /**
     * @return array
     */
    public function Configurator()
    {
        $g_setts = $this->getG_Settings();
        if (!$g_setts) {
            if (auth()->id()) {
                $this->insertG_Settings();
                $g_setts = $this->getG_Settings();
            } else {
                $g_setts = (object)[];
            }
        }

        if ($this->just_filters) {
            return [
                'data_filters' => $this->getEqptFilters($this->app_table, 'filters'),
            ];
        } else {
            return [
                'eqpt_lib' => $this->equipmentsFind($this->app_table, $this->master_model, 'eqpt_lib'),
                'line_lib' => $this->libLinesFind($this->app_table, $this->master_model, 'line_lib'),
                'sectors' => $this->findData($this->app_table, $this->master_model, 'sectors', ['sector', 'pos_num', 'pos_widths']),
                'pos' => $this->posFind($this->app_table, $this->master_model),
                'data_eqpt' => $this->equipmentsFind($this->app_table, $this->master_model, 'data_eqpt'),
                'data_conn' => $this->dataLinesFind($this->app_table, $this->master_model, 'data_conn'),
                'data_filters' => $this->getEqptFilters($this->app_table, 'filters'),
                'colors_eq' => $this->findEqptColors($this->app_table, $this->master_model),
                'tech_list' => $this->findData($this->app_table, $this->master_model, 'tech_list', ['technology']),
                'g_settings' => $g_setts,
                'popup_tables' => $this->getArrayOfInherits(),
            ];
        }
    }

    /**
     * @param string $app_tb
     * @param string $inherit_type
     * @return array
     */
    protected function getEqptFilters(string $app_tb, string $inherit_type): array
    {
        $inherit_tb = $this->stimRepo->findInheritTb($app_tb, $inherit_type, '2d');
        $table_meta = DataReceiver::meta_table($inherit_tb);
        return $this->dataRepo->getFilters($table_meta->id, [], auth()->id());
    }

    /**
     * @return array
     */
    public function getArrayOfInherits()
    {
        return [
            'eqptdata_2d' => strtolower( $this->findInherit($this->app_table, 'data_eqpt') ),
            'linedata_2d' => strtolower( $this->findInherit($this->app_table, 'data_conn') ),
            'eqptlib_2d' => strtolower( $this->findInherit($this->app_table, 'eqpt_lib') ),
            'linelib_2d' => strtolower( $this->findInherit($this->app_table, 'line_lib') ),
            'eqptsett_2d' => strtolower( $this->findInherit($this->app_table, 'eqpt_sett') ),
            'pos_2d' => strtolower( $this->findInherit($this->app_table, 'pos') ),
            'status_2d' => strtolower( $this->findInherit($this->app_table, 'eqpt_colors') ),
            'tech_2d' => strtolower( $this->findInherit($this->app_table, 'tech_list') ),
            'sectors_2d' => strtolower( $this->findInherit($this->app_table, 'sectors') ),
            'filters_2d' => strtolower( $this->findInherit($this->app_table, 'filters') ),
        ];
    }

    /**
     * @return array|null
     */
    protected function getG_Settings()
    {
        $g_sett_select = [
            'background', 'top_elev', 'bot_elev', 'pd_pos_he', 'pd_sector_he', 'pd_rest_he', 'pd_bot_he',
            'g_pos_he', 'g_sector_he', 'g_rest_he', 'g_bot_he', 'elev_by', 'show_eqpt_size', 'show_eqpt_model',
            'show_eqpt_tech', 'show_eqpt_id', 'show_line_model', 'shared_sectors',
            'show_eqpt_size__font', 'show_eqpt_size__size', 'show_eqpt_size__color',
            'show_eqpt_model__font', 'show_eqpt_model__size', 'show_eqpt_model__color',
            'show_eqpt_tech__font', 'show_eqpt_tech__size', 'show_eqpt_tech__color',
            'show_eqpt_id__font', 'show_eqpt_id__size', 'show_eqpt_id__color',
            'show_line_model__font', 'show_line_model__size', 'show_line_model__color',
            'show_eqpt_tooltip', 'air_base_names', 'use_independent_controls', 'full_reflection'
        ];
        $g_setts = $this->findData($this->app_table, $this->master_model, 'g_settings', $g_sett_select);
        $g_setts = collect($g_setts)->first();
        if ($g_setts) {
            $g_setts['_app_tb'] = $this->stimRepo->findInheritTb('', 'g_settings', '2d');
        }
        return $g_setts;
    }

    /**
     * @return mixed
     */
    protected function insertG_Settings()
    {
        $sett_tb = $this->stimRepo->findInheritTb('', 'g_settings', '2d');
        $table_meta = DataReceiver::meta_table($sett_tb);
        $sett_tb_info = DataReceiver::app_table_and_fields($sett_tb);
        $links = DataReceiver::get_link_fields($sett_tb_info['_app_fields']);

        $data = [];
        foreach ($links as $link) {
            $data[ $link->data_field ] = $this->master_model[ $link->link_field_db ];
        }
        return (new TableDataService())->insertRow($table_meta, $data, auth()->id());
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @return array
     */
    public function findEqptColors(string $app_tb, array $tablda_model)
    {
        $colors_tb = $this->stimRepo->findInheritTb($app_tb, 'eqpt_colors', '2d');
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
    protected function posFind(string $app_tb, array $tablda_model)
    {
        $data = $this->findData($app_tb, $tablda_model, 'pos', ['name']);
        return collect($data)
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @param string $inherit_type
     * @param array $filters
     * @return array|array[]
     */
    public function equipmentsFind(string $app_tb, array $tablda_model, string $inherit_type, array $filters = [])
    {
        $select = ['equipment','location','sector','pos','status','elev_pd','elev_g','elev_rad',
            'qty','pos_left','label_side','label_dir','technology'];
        $data_eqpts = $this->findData($app_tb, $tablda_model, $inherit_type, $select, $filters['eqpt'] ?? []);
        $models = array_pluck($data_eqpts, 'equipment');

        $eqpt_tb = $this->stimRepo->findInheritTb($app_tb, 'equipment', '2d');
        $info_eqpts = $eqpt_tb
            ? (new Model3dService( $eqpt_tb ))->queryFindModel($tablda_model)->whereIn('model', $models)->get()
            : [];

        $elib_tb = $this->stimRepo->findInheritTb($app_tb, 'eqpt_lib', '2d');
        $info_libs = $elib_tb
            ? (new Model3dService( $elib_tb ))->queryFindModel($tablda_model)->whereIn('equipment', $models)->get()
            : [];

        if ($info_eqpts && $info_libs) {
            $info_eqpts = collect($info_eqpts);
            $info_libs = collect($info_libs);
            foreach ($data_eqpts as &$d_eqpt) {
                //equipment
                $found = $info_eqpts->where('model', '=', $d_eqpt['equipment'])->first();
                $d_eqpt['_model_id'] = $found['_id'];
                $d_eqpt['dx'] = floatval($found['dx']);
                $d_eqpt['dy'] = floatval($found['dy']);
                $d_eqpt['dz'] = floatval($found['dz']);
                $d_eqpt['port_top'] = intval($found['port_top']);
                $d_eqpt['port_bot'] = intval($found['port_bot']);
                $d_eqpt['port_left'] = intval($found['port_left']);
                $d_eqpt['port_right'] = intval($found['port_right']);
                $d_eqpt['color'] = $found['color'];
                //eqpt_lib
                $found_lib = $info_libs->where('equipment', '=', $d_eqpt['equipment'])->first();
                $d_eqpt['_eqptlib_id'] = $found_lib['_id'];
                $d_eqpt['model_id'] = $found_lib['model_id'];
                $d_eqpt['scale_x'] = floatval($found_lib['scale_x']);
                $d_eqpt['scale_y'] = floatval($found_lib['scale_y']);
                $d_eqpt['scale_z'] = floatval($found_lib['scale_z']);
                $d_eqpt['ports_qty_top'] = intval($found_lib['ports_qty_top']);
                $d_eqpt['ports_qty_bot'] = intval($found_lib['ports_qty_bot']);
                $d_eqpt['ports_qty_left'] = intval($found_lib['ports_qty_left']);
                $d_eqpt['ports_qty_right'] = intval($found_lib['ports_qty_right']);
            }
        }
        return $data_eqpts;
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @param string $inherit_type
     * @return array
     */
    protected function dataLinesFind(string $app_tb, array $tablda_model, string $inherit_type)
    {
        $select = ['line','from_eqpt_id','to_eqpt_id', 'pos_top', 'pos_left', 'pos_top_back', 'pos_left_back', 'qty', 'status',
            'from_port_pos', 'from_port_idx', 'to_port_pos', 'to_port_idx', 'control_points', 'control_points_back',
            'caption_style', 'caption_sect', 'caption_orient', ];
        $data_lines = $this->findData($app_tb, $tablda_model, $inherit_type, $select);
        $models = array_pluck($data_lines, 'line');

        $info_lines = $this->libLinesFind($this->app_table, $this->master_model, 'line_lib');

        if ($info_lines) {
            $info_lines = collect($info_lines);

            foreach ($data_lines as &$d_line) {
                $found = $info_lines->where('title', '=', $d_line['line'])->first();
                $d_line['title'] = $found['title'] ?? '';
                $d_line['gui_name'] = $found['gui_name'] ?? '';
                $d_line['diameter'] = intval($found['diameter']) ?? 0;
                //inherits
                $d_line['_linelib_id'] = $found['_id'] ?? null;
                $d_line['_feedline_id'] = $found['_feedline_id'] ?? null;
                $d_line['f_diameter'] = $found['f_diameter'] ?? 0;
            }
        }
        return $data_lines;
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @param string $inherit_type
     * @return array
     */
    protected function libLinesFind(string $app_tb, array $tablda_model, string $inherit_type)
    {
        $select = ['title','diameter','gui_name'];
        $data_lines = $this->findData($app_tb, $tablda_model, $inherit_type, $select);
        $models = array_pluck($data_lines, 'title');

        $feedline_tb = $this->stimRepo->findPopupTb('feedline');
        $feedline_rows = $feedline_tb
            ? (new Model3dService( $feedline_tb ))->queryReceiver()->whereIn('model', $models)->get()
            : [];

        if ($feedline_rows) {
            $feedline_rows = collect($feedline_rows);
            foreach ($data_lines as &$d_line) {
                $found = $feedline_rows->where('model', '=', $d_line['title'])->first();
                $d_line['_feedline_id'] = $found['_id'] ?? null;
                $d_line['f_diameter'] = $found['width_diameter'] ?? 0;

                $d_line['_linelib_id'] = $d_line['_id'] ?? null;
            }
        }
        return $data_lines;
    }

    /**
     * @param string $app_tb
     * @param array $tablda_model
     * @param string $inherit_type
     * @param array $selects
     * @param array $front_filters
     * @return array|array[]
     */
    public function findData(string $app_tb, array $tablda_model, string $inherit_type, array $selects = [], array $front_filters = [])
    {
        $selects[] = '_id';

        $inherit_tb = $this->stimRepo->findInheritTb($app_tb, $inherit_type, '2d');

        $rows = $inherit_tb
            ? (new Model3dService( $inherit_tb, false ))
                ->queryFindModel($tablda_model, $front_filters)
                ->get()
            : [];

        return array_map(function ($el) use ($selects) {
            //select only columns in 'selects'
            return array_filter($el, function ($key) use ($selects) {
                return in_array($key, $selects);
            }, ARRAY_FILTER_USE_KEY);
        }, $rows);
    }

    /**
     * @param string $app_tb
     * @param string $inherit_type
     * @return string
     */
    protected function findInherit(string $app_tb, string $inherit_type)
    {
        return $this->stimRepo->findInheritTb($app_tb, $inherit_type, '2d');
    }
}