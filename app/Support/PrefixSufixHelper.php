<?php

namespace Vanguard\Support;

use Carbon\Carbon;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;

class PrefixSufixHelper
{
    /**
     * @var array
     */
    protected static $prefixes_cache = [];

    /**
     * @param Table $table
     * @return void
     */
    public static function setCache(Table $table): void
    {
        if (empty(self::$prefixes_cache[$table->id])) {
            $data = [
                'id' => []
            ];
            foreach ($table->_fields as $fld) {
                if ($fld->has_copy_prefix || $fld->has_copy_suffix || $fld->has_datetime_suffix) {
                    $data[$fld->field] = [
                        'prefix' => $fld->has_copy_prefix ? $fld->copy_prefix_value : '',
                        'suffix' => $fld->has_copy_suffix ? $fld->copy_suffix_value : '',
                        'date' => $fld->has_datetime_suffix ? Carbon::now()->format('Ymd_His') : '',
                    ];
                }
            }
            self::$prefixes_cache[$table->id] = $data;
        }
    }

    /**
     * @param Table $master_table
     * @param Table $table
     * @param array $relations
     * @param array $master_row
     * @return void
     */
    public static function cacheFromMaster(Table $master_table, Table $table, array $relations, array $master_row): void
    {
        self::setCache($master_table);
        self::setCache($table);

        foreach ($relations as $child_fld => $master_fld) {
            self::$prefixes_cache[$table->id][$child_fld] = $master_fld != 'id'
                ? self::masterCache($master_table->id, $master_fld)
                : [
                    'prefix' => $master_row['_new_id'],
                    'suffix' => '',
                    'date' => '',
                    '_no_value' => true
                ];
        }
    }

    /**
     * @param int $master_id
     * @param string $master_fld
     * @return string[]
     */
    protected static function masterCache(int $master_id, string $master_fld): array
    {
        return self::$prefixes_cache[$master_id][$master_fld] ?? [
            'prefix' => '',
            'suffix' => '',
            'date' => '',
        ];
    }

    /**
     * @param Table $table
     * @param array $row
     * @return array
     */
    public static function applyToRow(Table $table, array $row): array
    {
        self::setCache($table);
        foreach (self::$prefixes_cache[$table->id] as $field => $changes) {
            $old_val = ($changes['_no_value'] ?? false)
                ? ''
                : ($row[$field] ?? '');

            $row[$field] = ($changes['prefix'] ?? '')
                . ($old_val)
                . ($changes['suffix'] ?? '')
                . ($changes['date'] ?? '');
        }
        return $row;
    }
}