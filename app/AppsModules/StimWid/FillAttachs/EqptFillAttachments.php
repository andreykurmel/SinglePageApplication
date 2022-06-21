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
use Vanguard\Classes\MselConvert;
use Vanguard\Http\Controllers\Web\Tablda\TableDataController;
use Vanguard\Http\Requests\Tablda\TableData\GetTableDataRequest;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;

class EqptFillAttachments
{
    protected $target_model_fld = '';
    protected $eqpt_model_fld = '';
    protected $fields_for_copy = [
        'attach_num_locs','attach_from_1','attach_value_1',
        'attach_from_2','attach_value_2','attach_from_3','attach_value_3'
    ];
    protected $target_tb = [];
    protected $eqpt_tb = [];

    protected $app_table_string = '';
    protected $req_params = [];

    /**
     * Target Table must have:
     * CorrespFields with 'app_field':
     * 'equipment' -> Model Name
     * 'attach_from_1','attach_value_1' -> will be copied
     *
     * Also the same settings must have 'EQPT_ATTACHMENTS' table.
     *
     * @param string $app_table
     * @param array $req_params
     */
    public function __construct(string $app_table, array $req_params)
    {
        $this->app_table_string = $app_table;
        $this->req_params = $req_params;
    }

    /**
     * @return string
     */
    public function do_action()
    {
        if (!$this->app_table_string || !$this->req_params) {
            return 'Incorrect request';
        }

        $err = $this->set_fields();
        if ($err) {
            return $err;
        }

        $table = (new TableRepository())->getTableByDB( $this->target_tb['data_table'] );
        if ($table) {
            $sql = new TableDataQuery($table);
            $sql->applyWhereClause($this->req_params, auth()->id());
            $sql = $sql->getQuery();

            $len = $sql->count();
            $chunk = 100;
            $repo = new TableDataRepository();

            try {
                for ($i = 0; $i*$chunk < $len; $i++) {
                    $rows = (clone $sql)->offset($i * $chunk)->limit($chunk)->get();
                    foreach ($rows as $row) {
                        $fields = $this->calc_row( $row->toArray() );
                        $repo->quickUpdate($table, $row->id, $fields);
                    }
                }
            } catch (\Exception $e) {
                return 'Row calculation error';
            }
        } else {
            return 'Table not found';
        }
        return '';
    }

    /**
     * @return string
     */
    protected function set_fields()
    {
        $stimRepo = new StimSettingsRepository();
        $this->target_tb = DataReceiver::app_table_and_fields($this->app_table_string);
        $this->eqpt_tb = DataReceiver::app_table_and_fields('EQPT_ATTACHMENTS');

        $this->target_model_fld = $this->target_tb['_app_fields']->where('app_field', '=', 'equipment')->first();
        $this->target_model_fld = $this->target_model_fld ? $this->target_model_fld->data_field : '';

        $this->eqpt_model_fld = $this->eqpt_tb['_app_fields']->where('app_field', '=', 'equipment')->first();
        $this->eqpt_model_fld = $this->eqpt_model_fld ? $this->eqpt_model_fld->data_field : '';

        if (!$this->target_model_fld || !$this->eqpt_model_fld) {
            return 'Incorrect settings in Correspondence/Fields.tbl (EQPT_ATTACHMENTS)';
        }
        return '';
    }

    /**
     * @param string $like_str
     * @return array
     */
    protected function find_eqpt(string $like_str)
    {
        return DataReceiver::mapped_query('EQPT_ATTACHMENTS')
            ->where('equipment', '=', $like_str)
            ->first();
    }

    /**
     * @param array $row
     * @return array
     */
    protected function calc_row(array $row)
    {
        $eqpt = $this->find_eqpt($row[$this->target_model_fld]);
        if ($eqpt && is_array($eqpt)) {
            foreach ($this->fields_for_copy as $appfld) {
                $datafld = $this->target_tb['_app_fields']->where('app_field', '=', $appfld)->first();
                $datafld = $datafld ? $datafld->data_field : '';
                if ($eqpt[$appfld]) {
                    $row[$datafld] = $eqpt[$appfld];
                }
            }
        }
        return $row;
    }

}