<?php

namespace Vanguard\Watchers;

use Vanguard\Models\Table\Table;

class FieldRenameWatcher
{
    use UpdateFormulaTrait;

    /**
     * @param Table $table
     * @param string $old
     * @param string $new
     * @return void
     */
    public function watchRename(Table $table, string $old, string $new)
    {
        $this->updTableFormulas($table, '\{' . $old . '\}', '\{' . $new . '\}');
        $old = preg_replace('/[^\w]/i', '', $old);
        $new = preg_replace('/[^\w]/i', '', $new);
        $this->updTableFormulas($table, '\{' . $old . '\}', '\{' . $new . '\}');
    }
}