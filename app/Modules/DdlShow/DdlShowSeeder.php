<?php

namespace Vanguard\Modules\DdlShow;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Vanguard\Models\Table\Table;

class DdlShowSeeder
{
    /**
     * @return float
     */
    public static function seedColumns(): float
    {
        $start = microtime(true);
        self::tbQuery()->chunk(50, function ($tables) {
            $tables->load('_fields');
            foreach ($tables as $table) {
                try {
                    $d = new DdlShowModule($table);
                    foreach ($table->_fields as $field) {
                        if ($field->ddl_id) {
                            $d->columnForField($field);
                        }
                    }
                } catch (\Exception $e) {}
            }
        });
        return microtime(true) - $start;
    }

    /**
     * @return float
     * @throws Exception
     */
    public static function seedDatas(): float
    {
        $start = microtime(true);
        self::tbQuery()->chunk(50, function ($tables) {
            try {
                foreach ($tables as $table) {
                    (new DdlShowModule($table))->fillShows();
                }
            } catch (\Exception $e) {}
        });
        return microtime(true) - $start;
    }

    /**
     * @return Builder
     */
    protected static function tbQuery(): Builder
    {
        return Table::query()->where('is_system', '=', 0)
            ->whereHas('_fields', function ($q) {
                $q->whereNotNull('ddl_id');
            });
    }
}