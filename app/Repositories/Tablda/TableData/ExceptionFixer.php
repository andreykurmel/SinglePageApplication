<?php

namespace Vanguard\Repositories\Tablda\TableData;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Vanguard\Classes\SysColumnCreator;
use Vanguard\Models\Table\Table;

class ExceptionFixer
{
    public static function handle(\Exception $e, Table $table): bool
    {
        if (Str::startsWith($e->getMessage(), 'SQLSTATE[42S22]: Column not found:')) {
            //Sys Column is not present
            if (preg_match('/Unknown column \'[^\']+(_formula|_mirror)\' in \'[^\']+\'/i', $e->getMessage())) {
                $sys = new SysColumnCreator();
                foreach ($table->_fields as $field) {
                    if ($field->input_type == 'Formula' || $field->input_type == 'Mirror') {
                        $sys->watchInputType($table, $field);
                    }
                }
                Log::alert('ExceptionFixer::SQLSTATE[42S22] Column not found::tried fix for "'.$table->id.':'.$table->name.'".');
                return true;
            }
        }
        return false;
    }
}