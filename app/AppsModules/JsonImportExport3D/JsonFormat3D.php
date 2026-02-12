<?php

namespace Vanguard\AppsModules\JsonImportExport3D;

class JsonFormat3D
{
    /**
     * @param array $present
     * @return array
     */
    public static function get(array $present = []): array
    {
        return array_replace_recursive([
            'Loading' => [
                'Model' => [], //Loading record
                'Children' => [], //All related records in format: ['app_table_name' => [ [record1], [record2], ... ], ...]
            ],
            'Geometry' => [
                'Model' => [], //Geometry record
                'Children' => [], //All related records in format: ['app_table_name' => [ [record1], [record2], ... ], ...]
            ],
            'MA' => [
                'Model' => [], //MA record
                'Children' => [], //All related records in format: ['app_table_name' => [ [record1], [record2], ... ], ...]
            ],
        ], $present);
    }
}