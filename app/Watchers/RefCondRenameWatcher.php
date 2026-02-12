<?php

namespace Vanguard\Watchers;

use Vanguard\Models\DataSetPermissions\TableRefCondition;

class RefCondRenameWatcher
{
    use UpdateFormulaTrait;

    /**
     * @param TableRefCondition $ref_cond
     * @param string $new_name
     */
    public function watchRename(TableRefCondition $ref_cond, string $new_name)
    {
        $table = $ref_cond->_table()->with('_fields_are_formula')->first();
        $this->updTableFormulas($table, '\"' . $ref_cond->name . '\"', '\"' . $new_name . '\"');
    }
}