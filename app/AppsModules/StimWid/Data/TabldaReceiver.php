<?php

namespace Vanguard\AppsModules\StimWid\Data;


use Tablda\DataReceiver\TabldaDataReceiver;
use Tablda\DataReceiver\TabldaTable;

class TabldaReceiver extends TabldaDataReceiver
{

    /**
     * Get Query with field mapping.
     *
     * @param string $table
     * @return TableConverter
     */
    public function tableReceiver(string $table)
    {
        $tb = $this->getTableWithMaps($table);

        $model = (new TabldaTable())
            ->setConnection($this->connection_data)
            ->setTable($tb['data_table'])
            ->setMaps($tb['_app_maps']);

        return new TableConverter($model, !empty($this->settings['case_sens']));
    }
}