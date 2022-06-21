<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Vanguard\Classes\MselConvert;
use Vanguard\Models\DataSetPermissions\TableRefConditionItem;
use Vanguard\Models\Table\Table;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class RefCondItemQueryApplier
{
    protected $ref_item;
    protected $_ref_table;
    protected $_table;
    protected $_ref_field;
    protected $_field;

    protected $compared_operator;
    protected $logic_operator;
    protected $input_type;
    protected $table_id;
    protected $com_header_applier;
    protected $compared_db_field;
    protected $table_db_field;
    protected $subquery_data;

    /**
     * @param TableRefConditionItem $ref_item
     */
    public function __construct(TableRefConditionItem $ref_item)
    {
        $ref_item->loadMissing('_ref_condition', '_compared_field', '_field');
        $ref_item->_ref_condition->loadMissing('_ref_table', '_table');

        if ($ref_item->_ref_condition->__reverse) {
            $this->subquery_data = is_array($ref_item->_ref_condition->__reverse) ? $ref_item->_ref_condition->__reverse : [];
            $this->_ref_table = $ref_item->_ref_condition->_table;
            $this->_table = $ref_item->_ref_condition->_ref_table;
            $this->_ref_field = $ref_item->_field;
            $this->_field = $ref_item->_compared_field;
        } else {
            $this->_ref_table = $ref_item->_ref_condition->_ref_table;
            $this->_table = $ref_item->_ref_condition->_table;
            $this->_ref_field = $ref_item->_compared_field;
            $this->_field = $ref_item->_field;
        }
        $this->ref_item = $ref_item;

        $this->logic_operator = $ref_item->logic_operator;
        $this->compared_operator = $ref_item->compared_operator == 'Include' ? 'Like' : ($ref_item->compared_operator ?: '=');
        $this->input_type = $this->_ref_field ? $this->_ref_field->input_type : 'Input';
        $this->table_id = $this->_table ? $this->_table->id : null;

        $com_db_field = $this->_ref_field ? $this->_ref_field->field : 'id';
        $this->compared_db_field = SqlFieldHelper::getSqlFld($this->_ref_table, $com_db_field);
        $fld = $this->_field ? $this->_field->field : 'id';
        $this->table_db_field = SqlFieldHelper::getSqlFld($this->_table, $fld);

        $f_type = $this->_ref_field ? $this->_ref_field->f_type : '';
        $this->com_header_applier = new FieldTypeApplier($f_type, $this->compared_db_field);
    }

    /**
     * @param Builder $cond_query
     * @param array|null $present_row
     * @return Builder
     * @throws \Exception
     */
    public function applyToQuery(Builder $cond_query, array $present_row = null)
    {
        if (!$this->correctRefItem()) {
            return $cond_query;
        }

        if ($this->ref_item->item_type == 'P2S') {
            if ($present_row) {
                $fld = $this->_field ? $this->_field->field : null;
                $val = !empty($present_row[$fld]) ? HelperService::sanitizeNull($present_row[$fld]) : null;
                $this->applyDirect($cond_query, $val ?: false);
            } else {
                $this->applySubQuery($cond_query, $this->_ref_table);
            }
        } elseif ($this->ref_item->item_type == 'S2V') {
            $this->applyDirect($cond_query, $this->ref_item->compared_value);
        } else {
            throw new \Exception('TableRefConditionItem::incorrect item_type');
        }
        return $cond_query;
    }

    /**
     * @return bool
     */
    protected function correctRefItem()
    {
        return $this->_ref_field
            && (
                ($this->ref_item->item_type == 'P2S' && $this->_field)
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
            $val = $this->compared_operator == 'Like' ? '%' . trim($val) . '%' : trim($val);
        }

        $arr_of_val = MselConvert::getArr($val);
        if (array_first($arr_of_val) == '{$user}') {
            $this->applyCurrentUser($cond_query);
        } elseif ($val == '{$group}') {
            $this->applyCurrentUserAndGroup($cond_query);
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
                    if (in_array($that->compared_operator, ['>','<'])) {
                        $el = floatval($el);
                    }
                    $that->com_header_applier->where($sub, $that->compared_operator, $el);
                });

                if (MselectData::isMSEL($that->input_type)) {
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

            if (MselectData::isMSEL($that->input_type)) {
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
        $sqlSub = new TableDataQuery($_table, true);
        $sqlSub->noSysRules();
        if ($this->subquery_data) {
            $sqlSub->testViewAndApplyWhereClauses($this->subquery_data, $this->_table->user_id);
        }
        $sqlSub = $sqlSub->getQuery()->select($this->compared_db_field);
        $this->com_header_applier->where($sqlSub, $this->compared_operator, DB::raw($this->table_db_field));

        $func = $this->logic_operator == 'OR' ? 'orWhereIn' : 'whereIn';
        $cond_query->{$func}($this->table_db_field, $sqlSub);
    }
}