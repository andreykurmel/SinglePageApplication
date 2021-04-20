<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Database\Eloquent\Builder;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class FieldTypeApplier
{
    protected $type = '';
    protected $field = '';
    protected $helper;
    protected $is_number_type = false;
    protected $zero_is_null = false;

    /**
     * FieldTypeApplier constructor.
     * @param string $type
     * @param string $field
     */
    function __construct(string $type, string $field)
    {
        $this->type = $type;
        $this->field = $field;
        $this->helper = new HelperService();
        $this->is_number_type = $this->helper->isNumberType($this->type);
        $this->zero_is_null = $this->helper->zeroIsNull($this->type);
    }

    /**
     * @param Builder $query
     * @param string $compare
     * @param $val
     * @return Builder
     */
    public function where(Builder $query, string $compare, $val) : Builder
    {
        $present_val = $this->zero_is_null ? !!$val : strlen($val);

        //prepare
        $compare = strtolower($compare);
        $compare = $compare == 'include' ? 'like' : $compare;

        if ($present_val) {
            //where OR null for '!='
            //where AND null for '=','>','<','like'
            $and = in_array($compare, ['!=']) ? 'or' : 'and';

            //Is Null for '!='
            //Not Null for '=','>','<','like'
            $not = in_array($compare, ['=','>','<','like']);
        } else {
            //where OR null for '=','like'
            //where AND null for '!=','>','<'
            $and = in_array($compare, ['=','like']) ? 'or' : 'and';

            //Is Null for '=','like'
            //Not Null for '!=','>','<'
            $not = in_array($compare, ['!=','>','<']);
        }

        //apply
        $query->where($this->field, $compare, $val, $and);
        $query->whereNull($this->field, $and, $not);

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