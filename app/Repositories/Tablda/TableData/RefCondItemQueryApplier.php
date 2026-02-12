<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Vanguard\Classes\MselConvert;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\Table\Table;
use Vanguard\Modules\DdlShow\DdlShowModule;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class RefCondItemQueryApplier
{
    protected $cond_query;
    protected $ref_item;
    protected $_ref_table;
    protected $_table;
    protected $_ref_field;
    protected $_field;
    protected $field_is_show;
    protected $ref_is_show;

    protected $compared_operator;
    protected $logic_operator;
    protected $table_id;
    protected $com_header_applier;
    protected $ref_db_field_full;
    protected $db_field_full;
    protected $subquery_data = [];

    /**
     * @param Builder $cond_query
     * @param TableRefConditionItem $ref_item
     * @param string $lo_operator : 'AND' | 'OR'
     */
    public function __construct(Builder $cond_query, TableRefConditionItem $ref_item, string $lo_operator = '')
    {
        $this->cond_query = $cond_query;
        $theSame = $ref_item->_ref_condition->_table->id == $ref_item->_ref_condition->_ref_table->id;
        $reverse = $cond_query->getQuery()->from != $ref_item->_ref_condition->_table->db_name
            && $cond_query->getQuery()->from == $ref_item->_ref_condition->_ref_table->db_name;

        $ref_item->loadMissing('_ref_condition', '_compared_field', '_field');
        $ref_item->_ref_condition->loadMissing('_ref_table', '_table');

        if ($reverse) {
            $this->_ref_table = $ref_item->_ref_condition->_table;
            $this->_table = $ref_item->_ref_condition->_ref_table;
            $this->_ref_field = $ref_item->_field;
            $this->_field = $ref_item->_compared_field;
            $this->field_is_show = $ref_item->compared_part == 'show';
            $this->ref_is_show = $ref_item->field_part == 'show';
        } else {
            $this->_ref_table = $ref_item->_ref_condition->_ref_table;
            $this->_table = $ref_item->_ref_condition->_table;
            $this->_ref_field = $ref_item->_compared_field;
            $this->_field = $ref_item->_field;
            $this->field_is_show = $ref_item->field_part == 'show';
            $this->ref_is_show = $ref_item->compared_part == 'show';
        }
        $this->ref_item = $ref_item;

        $this->logic_operator = $lo_operator ?: $ref_item->logic_operator;
        $this->compared_operator = $ref_item->compared_operator ?: '=';
        $this->table_id = $this->_table ? $this->_table->id : null;

        $com_db_field = $this->applyColumn('ref', 'id');
        $this->ref_db_field_full = SqlFieldHelper::getSqlFld($this->_ref_table, $com_db_field);

        $fld = $this->applyColumn('field', 'id');
        $this->db_field_full = SqlFieldHelper::getSqlFld($this->_table, $fld);

        $needed_fld = $theSame && $ref_item->item_type == 'S2V' ? $this->_ref_field : $this->_field;
        $fld_string = $theSame && $ref_item->item_type == 'S2V' ? $this->ref_db_field_full : $this->db_field_full;
        $this->com_header_applier = new FieldTypeApplier('', $fld_string);
        $this->com_header_applier->setTableField($needed_fld);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setSubqueryData(array $data): self
    {
        $this->subquery_data = $data;
        return $this;
    }

    /**
     * @param string $type
     * @param string $default
     * @return string
     */
    protected function applyColumn(string $type, string $default): string
    {
        if ($type == 'ref') {
            return $this->_ref_field
                ? ($this->ref_is_show ? DdlShowModule::syscol($this->_ref_field) : $this->_ref_field->field)
                : $default;
        } else {
            return $this->_field
                ? ($this->field_is_show ? DdlShowModule::syscol($this->_field) : $this->_field->field)
                : $default;
        }
    }

    /**
     * @param array|null $present_row
     * @return Builder
     * @throws \Exception
     */
    public function applyToQuery(array $present_row = null)
    {
        if (!$this->correctRefItem()) {
            return $this->cond_query;
        }

        if ($this->ref_item->item_type == 'P2S') {
            if ($present_row) {
                $val = $this->getRowVal($present_row);
                $this->applyDirect($this->cond_query, $val ?: false);
            } else {
                $this->applySubQuery($this->cond_query, $this->_ref_table);
            }
        } elseif ($this->ref_item->item_type == 'S2V') {
            $this->applyDirect($this->cond_query, $this->ref_item->compared_value);
        } else {
            throw new \Exception('TableRefConditionItem::incorrect item_type');
        }
        return $this->cond_query;
    }

    /**
     * @param array $present_row
     * @return mixed
     */
    protected function getRowVal(array $present_row)
    {
        $fld = $this->applyColumn('field', '');
        $val = !empty($present_row[$fld]) ? HelperService::sanitizeNull($present_row[$fld]) : null;
        $reffld = $this->applyColumn('ref', '');
        $refVal = !empty($present_row[$reffld]) ? HelperService::sanitizeNull($present_row[$reffld]) : null;
        return $refVal //NOTE: first $refVal is needed for correct RCs related on another tables via "ID".
            ?: ($fld != 'id' ? $val : null); //NOTE: fix for incorrect mirroring via self ID.
    }

    /**
     * @return bool
     */
    protected function correctRefItem()
    {
        return $this->ref_item->_compared_field
            && (
                ($this->ref_item->item_type == 'P2S' && $this->ref_item->_field)
                ||
                ($this->ref_item->item_type == 'S2V') //$ref_item->compared_value can be null
            );
    }

    /**
     * @param Builder $cond_query
     * @param $val
     */
    protected function applyDirect(Builder $cond_query, $val)
    {
        if ($val === false) {
            $cond_query->whereRaw('false');
        }
        if (!is_null($val)) {
            $val = trim($val);
        }

        $arr_of_val = MselConvert::getArr($val);
        if (in_array('{$user}', $arr_of_val)) {
            $this->applyCurrentUser($cond_query);
        } elseif (in_array('{$group}', $arr_of_val)) {
            $this->applyCurrentUserAndGroup($cond_query);

        } elseif (in_array('{{Yesterday}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->subDay()->format('Y-m-d'));
        } elseif (in_array('{{Today}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->format('Y-m-d'));
        } elseif (in_array('{{Tomorrow}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->addDay()->format('Y-m-d'));

        } elseif (in_array('{{LastWeek}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->subWeek()->weekOfYear);
        } elseif (in_array('{{ThisWeek}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->weekOfYear);
        } elseif (in_array('{{NextWeek}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->addWeek()->weekOfYear);

        } elseif (in_array('{{LastMonth}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->subMonth()->month);
        } elseif (in_array('{{ThisMonth}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->month);
        } elseif (in_array('{{NextMonth}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->addMonth()->month);

        } elseif (in_array('{{LastYear}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->year-1);
        } elseif (in_array('{{ThisYear}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->year);                                    
        } elseif (in_array('{{NextYear}}', $arr_of_val)) {
            $this->applyDate($cond_query, Carbon::now()->year+1);
        } else {
            $this->applyValue($cond_query, $val);
        }
    }

    /**
     * @param Builder $cond_query
     * @param $val
     */
    protected function applyValue(Builder $cond_query, $val)
    {
        $func = $this->logic_operator == 'OR' ? 'orWhere' : 'where';

        $that = $this;
        $cond_query->{$func}(function ($inner) use ($that, $val) {

            $arr_of_val = MselConvert::getArr($val);
            foreach ($arr_of_val as $el) {

                $inner->orWhere(function ($sub) use ($that, $el) {
                    if (in_array($that->compared_operator, ['>','<','>=','<=']) && (!$el || is_numeric($el))) {
                        $el = floatval($el);
                    }
                    $that->com_header_applier->where($sub, $that->compared_operator, $el);
                });

                if (MselectData::isMSEL($that->com_header_applier->input_type)) {
                    $inner->orWhere(function ($sub) use ($that, $el) {
                        $that->com_header_applier->where($sub, 'Like', '%"' . $el . '"%');
                    });
                }
            }

        });
    }

    /**
     * @param Builder $cond_query
     */
    protected function applyCurrentUser(Builder $cond_query)
    {
        $this->applyValue($cond_query, auth()->id());
    }

    /**
     * @param Builder $cond_query
     * @param string $date
     * @return void
     */
    protected function applyDate(Builder $cond_query, string $date)
    {
        $this->applyValue($cond_query, $date);
    }

    /**
     * @param Builder $cond_query
     */
    protected function applyCurrentUserAndGroup(Builder $cond_query)
    {
        $func = $this->logic_operator == 'OR' ? 'orWhere' : 'where';
        $auth = app()->make(AuthUserSingleton::class);

        $that = $this;
        $cond_query->{$func}(function ($inner) use ($that, $auth) {

            $all_ids = $auth->getUserIdAndUnderscoreGroups(true, $that->table_id);

            $inner->orWhere(function ($sub) use ($that, $all_ids) {
                $that->com_header_applier->whereIn($sub, $all_ids);
            });

            if (MselectData::isMSEL($that->com_header_applier->input_type)) {
                foreach ($all_ids as $id) {
                    $inner->orWhere(function ($sub) use ($that, $id) {
                        $that->com_header_applier->where($sub, 'Like', '%"'.$id.'"%');
                    });
                }
            }

        });
    }

    /**
     * @param Builder $cond_query
     * @param Table $_table
     */
    protected function applySubQuery(Builder $cond_query, Table $_table)
    {
        if ($this->_table->id == $_table->id) {
            $this->com_header_applier->where($cond_query, $this->compared_operator, DB::raw($this->db_field_full));
            return;
        }

        $sqlSub = new TableDataQuery($_table, true);
        $sqlSub->noSysRules();
        if ($this->subquery_data) {
            $sqlSub->testViewAndApplyWhereClauses($this->subquery_data, $this->_table->user_id);
        }
        $sqlSub = $sqlSub->getQuery()->select($this->ref_db_field_full);
        $this->com_header_applier->where($sqlSub, $this->compared_operator, DB::raw($this->db_field_full));

        $func = $this->logic_operator == 'OR' ? 'orWhereIn' : 'whereIn';
        $cond_query->{$func}($this->db_field_full, $sqlSub);
    }
}