<?php

namespace Vanguard\Classes;


use Maatwebsite\Excel\Facades\Excel;

class ExcelWrapper
{
    /**
     * @param string $file
     * @return array
     */
    public static function getSheets(string $file)
    {
        $reader = Excel::load(storage_path("app/tmp_import/" . $file));
        return $reader->excel->getSheetNames();
    }

    /**
     * @param string $file
     * @param string $sheet
     * @return \PHPExcel_Worksheet
     */
    public static function loadWorksheet(string $file, string $sheet = '')
    {
        $reader = Excel::load(storage_path("app/tmp_import/" . $file));
        $reader->noHeading();
        if ($sheet) {
            $worksheet = $reader->excel->getSheetByName($sheet);
        } else {
            $name = array_first($reader->excel->getSheetNames());
            $worksheet = $reader->excel->getSheetByName($name);
        }
        return $worksheet;
    }

    /**
     * @param \PHPExcel_Worksheet $worksheet
     * @param bool $first
     * @return array
     */
    public static function getWorkSheetRows(\PHPExcel_Worksheet $worksheet, bool $first = false)
    {
        $rows = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }
            $rows[] = $cells;

            if ($first) {
                return $cells;
            }
        }
        return $rows;
    }
}