<?php

namespace Vanguard\Classes;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelWrapper
{
    /**
     * Get all sheet names from the given Excel file.
     *
     * @param string $file
     * @return array
     */
    public static function getSheets(string $file): array
    {
        $path = storage_path('app/tmp_import/' . ltrim($file, '/'));
        $spreadsheet = IOFactory::load($path);

        return $spreadsheet->getSheetNames();
    }

    /**
     * Load a worksheet by name (or the first worksheet if none provided).
     *
     * @param string $file
     * @param string $sheet
     * @return Worksheet
     */
    public static function loadWorksheet(string $file, string $sheet = ''): Worksheet
    {
        $path = storage_path('app/tmp_import/' . ltrim($file, '/'));
        $spreadsheet = IOFactory::load($path);

        if ($sheet !== '') {
            $worksheet = $spreadsheet->getSheetByName($sheet);
            if ($worksheet instanceof Worksheet) {
                return $worksheet;
            }
        }

        // Fallback to the first sheet
        return $spreadsheet->getSheet(0);
    }

    /**
     * Convert a worksheet to an array of rows (optionally returning only the first row).
     *
     * @param Worksheet $worksheet
     * @param bool $first
     * @return array
     */
    public static function getWorkSheetRows(Worksheet $worksheet, bool $first = false): array
    {
        // Use toArray to include empty cells as empty strings for consistent column counts
        $rows = $worksheet->toArray('', false, false, false);

        if ($first) {
            return $rows[0] ?? [];
        }

        return $rows;
    }
}