<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class FieldTypeApplier
{
    public $input_type = 'Input';

    protected $reverse;
    protected $type = '';
    protected $field = '';
    protected $helper;
    protected $is_number_type = false;
    protected $zero_is_null = false;

    /**
     * @param string $type
     * @param string $field
     * @param bool $reverse
     */
    public function __construct(string $type, string $field, bool $reverse = false)
    {
        $this->reverse = $reverse;
        $this->type = $type;
        $this->field = $field;
        $this->helper = new HelperService();
        $this->is_number_type = $this->helper->isNumberType($this->type);
        $this->zero_is_null = $this->helper->zeroIsNull($this->type);
    }

    public function setTableField(TableField $field = null)
    {
        $this->type = $field && $field->f_type ? $field->f_type : 'String';
        $this->input_type = $field && $field->input_type ? $field->input_type : 'Input';
    }

    /**
     * @param Builder $query
     * @param string $compare
     * @param $val
     * @return Builder
     */
    public function where(Builder $query, string $compare, $val) : Builder
    {
        if (in_array(strtolower($compare), ['isempty','notempty'])) {
            $val = null;
        }

        $present_val = $this->zero_is_null ? !!$val : strlen($val);
        $compare = $this->prepareCompare($compare);

        if ($present_val) {
            //where OR null for '!='
            //where AND null for '=','>','<','>=','<=','like'
            $and = in_array($compare, ['!=']) ? 'or' : 'and';

            //Is Null for '!='
            //Not Null for '=','>','<','>=','<=','like'
            $not = in_array($compare, ['=','>','>=','<','<=','like']);
        } else {
            //where OR null for '=','like'
            //where AND null for '!=','>','<','>=','<='
            $and = in_array($compare, ['=','like']) ? 'or' : 'and';

            //Is Null for '=','like'
            //Not Null for '!=','>','<','>=','<=',
            $not = in_array($compare, ['!=','>','>=','<','<=']);
        }

        return $this->whereApply($query, $compare, $val, $and, $not);
    }

    /**
     * @param string $compare
     * @return string
     */
    protected function prepareCompare(string $compare): string
    {
        $compare = strtolower($compare);
        switch ($compare) {
            case 'include': $compare = 'like'; break;
            case 'notinclude': $compare = 'not like'; break;
            case 'isempty': $compare = '='; break;
            case 'notempty': $compare = '!='; break;
        }

        if ($this->reverse) {
            switch ($compare) {
                case '=': $compare = '!='; break;
                case '!=': $compare = '='; break;
                case '>': $compare = '<='; break;
                case '>=': $compare = '<'; break;
                case '<': $compare = '>='; break;
                case '<=': $compare = '>'; break;
                case 'like': $compare = 'not like'; break;
                case 'not like': $compare = 'like'; break;
            }
        }

        return $compare;
    }

    /**
     * @param Builder $query
     * @param string $compare
     * @param $val
     * @param $and
     * @param $not
     * @return Builder
     */
    protected function whereApply(Builder $query, string $compare, $val, $and, $not) : Builder
    {
        $target = $this->is_number_type ? ($val ?: 0) : ($val ?: '');
        if (in_array($compare, ['like','not like']) && !preg_match('/%/i', $val)) {
            $target = '%' . $target . '%';
        }

        $query->where($this->field, $compare, $target, $and);
        if (($compare == '=' && !$val) || ($compare == '!=' && $val)) {
            $query->whereNull($this->field, $and, $not);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param array $values
     * @param bool $not
     * @return Builder
     */
    public function whereIn(Builder $query, array $values, bool $not = false) : Builder
    {
        if (!count($values)) {
            $query->whereRaw('false');
            return $query;
        }

        if ($this->reverse) {
            $not = !$not;
        }

        $zeros = array_filter($values, function ($item) { return !$item && strlen($item); });
        $empty = array_filter($values, function ($item) { return !strlen($item); });

        $not_empty = array_filter($values);
        if ($zeros) {
            $not_empty[] = '0';
        }

        if ($empty) {
            $query->whereNull($this->field, 'or', $not);
            if (!$this->is_number_type) {
                $not_empty[] = (string)$this->helper->getDefaultOnType($this->type);
            }
        }

        if ($not_empty) {
            $query->whereIn($this->field, $not_empty, 'or', $not);
        }

        return $query;
    }
}