<?php

namespace Vanguard\Classes;

use Vanguard\Models\DataSetPermissions\TableRefCondition;

class RefConditionHelper
{
    /**
     * @param TableRefCondition $refCondition
     * @param array $parentRow
     * @return array
     */
    public static function getLinkParams(TableRefCondition $refCondition, array $parentRow): array
    {
        $linkParams = [];
        foreach ($refCondition->_items as $item) {
            if ($item->item_type === 'P2S' && $item->_field && $item->_compared_field) {
                $linkParams[$item->_compared_field->field] = $parentRow[$item->_field->field];
            }
            if ($item->item_type === 'S2V' && $item->compared_value && $item->_compared_field) {
                switch ($item->compared_operator) {
                    case 'Include':
                    case '=': $linkParams[$item->_compared_field->field] = $item->compared_value;
                        break;
                    case '>': $linkParams[$item->_compared_field->field] = $item->compared_value+1;
                        break;
                    case '<': $linkParams[$item->_compared_field->field] = $item->compared_value-1;
                        break;
                }
            }
        }
        return $linkParams;
    }
}