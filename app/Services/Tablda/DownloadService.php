<?php
/*From old project*/

namespace Vanguard\Services\Tablda;

use Dompdf\Dompdf;
use Maatwebsite\Excel\Facades\Excel;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Support\Excel\ArrayExport;
use Maatwebsite\Excel\Excel as ExcelFormat;

class DownloadService
{
    private $tableDataRepository;

    private $col_per_page = 9;

    /**
     * DownloadController constructor.
     *
     * @param TableDataRepository $tableDataRepository
     * @param TableService $tableService
     */
    public function __construct(\Vanguard\Repositories\Tablda\TableData\TableDataRepository $tableDataRepository) {
        $this->tableDataRepository = $tableDataRepository;
    }

    /**
     * Download table data.
     *
     * @param Table $table
     * @param string $filename
     * @param array $data
     * @return string
     */
    public function download(Table $table, string $filename, array $data, $time) {
        $res = "";
        switch ($filename) {
            case 'CSV': $dwn_mode = "csv";
                $this->downloader_csv($table, $data, $table->name." ".$time);
                break;
            case 'XLSX': $dwn_mode = "xlsx";
                $this->downloader_xlsx($table, $data, $table->name." ".$time);
                break;
            case 'PDF': $dwn_mode = "pdf";
                $this->downloader_pdf($table, $data, $table->name." ".$time.".pdf");
                break;
            case 'JSON': $dwn_mode = "json";
                $this->downloader_json($table, $data);
                break;
            case 'XML': $dwn_mode = "xml";
                $this->downloader_xml($table, $data);
                break;
        }
        return $res;
    }

    /**
     * Download Chart as PDF.
     *
     * @param array $tableHeaders
     * @param array $tableRows
     * @return string
     */
    public function pdfChart(array $tableHeaders, array $tableRows, string $filename) {
        $html = "";
        $colInTb = $this->col_per_page;
        $colTables = ceil(count($tableHeaders) / $colInTb);

        for ($step = 1; $step <= $colTables; $step++) {
            $html .= "<table style='border-collapse: collapse;page-break-inside: auto;page-break-after: always;' width=\"100%\">";

            $html .= "<thead><tr><th style='border: solid 1px #000;padding: 3px 5px;background-color: #AAA;'>Row #</th>";
            foreach ($tableHeaders as $idx => $header) {
                if ($idx >= ($step-1)*$colInTb && $idx < $step*$colInTb) {
                    $html .= "<th style='border: solid 1px #000;padding: 3px 5px;background-color: #AAA;'>"
                        . implode(' ', array_unique(explode(',', $header['name'])))
                        . "</th>";
                }
            }
            $html .= "</tr></thead>";

            $html .= "<tbody>";
            foreach ($tableRows as $r_id => $row) {
                $row = (array)$row;
                $html .= "<tr><td style='border: solid 1px #000;padding: 3px 5px;'>".($r_id+1)."</td>";
                foreach ($tableHeaders as $idx => $header) {
                    if ($idx >= ($step-1)*$colInTb && $idx < $step*$colInTb) {
                        $html .= "<td style='border: solid 1px #000;padding: 3px 5px;'>" . $row[$header['field']] . "</td>";
                    }
                }
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "</table>";
        }

        $pdf = new Dompdf();
        $pdf->setPaper("A4", "landscape");
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream($filename);
    }

    /**
     * Download Chart as CSV.
     *
     * @param array $tableHeaders
     * @param array $tableRows
     * @param string $filename
     * @return string
     */
    public function csvChart(array $tableHeaders, array $tableRows, string $filename) {
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment;filename=" . $filename);
        header("Content-Transfer-Encoding: binary");

        $to_print = [];
        foreach ($tableHeaders as $header) {
            $header_name = implode(' ', array_unique(explode(',', $header['name'])));
            $to_print[] = '"'.str_replace('"','', $header_name).'"';
        }
        echo implode(',', $to_print)."\r\n";

        foreach ($tableRows as $row) {
            $to_print = [];
            foreach ($tableHeaders as $header) {
                $to_print[] = '"'.str_replace('"','', $row[$header['field']] ?? '').'"';
            }
            echo implode(',', $to_print)."\r\n";
        }
    }

    /*
     * Prepare Excel writer class
     */
    private function prepare_Excel(Table $table, array $post)
    {
        $post['page'] = 1;
        $post['rows_per_page'] = 10000;
        $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

        $data = [ 0 => [] ];
        foreach ($table->_fields as $val) {
            if ($val['is_showed']) {
                $data[0][] = implode(' ', array_unique(explode(',', $val['name'])));
            }
        }

        foreach ($tableRows['rows'] as $row) {
            $row = (array)$row;
            $tmp_row = [];
            foreach ($table->_fields as $hdr) {
                if ($hdr['is_showed']) {
                    $tmp_row[] = $row[$hdr['field']];
                }
            }
            $data[] = $tmp_row;
        }

        return new ArrayExport($data);
    }

    /**
     * Create data flow for csv file
     *
     * @param Table $table
     * @param array $post
     * @param string $filename
     */
    private function downloader_csv(Table $table, array $post, string $filename) {
        $post['page'] = 1;
        $post['rows_per_page'] = 10000;
        $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

        $headers = [];
        $to_print = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr['is_showed']) {
                $headers[$hdr['field']] = '';
                $to_print[] = '"'.str_replace('"','', $hdr['name']).'"';
            }
        }
        echo implode(',', $to_print)."\r\n";

        foreach ($tableRows['rows'] as $row) {
            $to_print = $headers;
            foreach ($row as $fld => $val) {
                if (isset($to_print[$fld])) {
                    $to_print[$fld] = '"'.str_replace('"','', $val).'"';
                }
            }
            echo implode(',', $to_print)."\r\n";
        }

        while ($tableRows['rows_count'] > ($post['page'] * $post['rows_per_page'])) {
            $post['page'] ++;
            $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

            foreach ($tableRows['rows'] as $row) {
                $to_print = $headers;
                foreach ($row as $fld => $val) {
                    if (isset($to_print[$fld])) {
                        $to_print[$fld] = '"'.str_replace('"','', $val).'"';
                    }
                }
                echo implode(',', $to_print)."\r\n";
            }
        }
    }

    /*
     * Create data flow for xlsx file
     */
    private function downloader_xlsx(Table $table, array $post, $filename) {
        $data = $this->prepare_Excel($table, $post);
        echo Excel::raw($data, ExcelFormat::XLSX);
    }

    /*
     * Create data flow for pdf file
     */
    private function downloader_pdf(Table $table, $post, string $filename) {
        $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

        $tableHeaders = $table->_fields->filter(function ($header) {
            return $header['is_showed'];
        });

        $html = "";
        $colInTb = $this->col_per_page;
        $mult = ceil(count($tableHeaders) / $colInTb);

        for ($step = 1; $step <= $mult; $step++) {
            $html .= "<table style='border-collapse: collapse;page-break-inside: auto;page-break-after: always;' width=\"100%\">";

            $html .= "<thead><tr><th style='border: solid 1px #000;padding: 3px 5px;background-color: #AAA;'>Row #</th>";
            foreach ($tableHeaders as $idx => $header) {
                if ($idx >= ($step-1)*$colInTb && $idx < $step*$colInTb) {
                    $html .= "<th style='border: solid 1px #000;padding: 3px 5px;background-color: #AAA;'>"
                        . implode(' ', array_unique(explode(',', $header['name'])))
                        . "</th>";
                }
            }
            $html .= "</tr></thead>";

            $html .= "<tbody>";
            foreach ($tableRows['rows'] as $r_id => $row) {
                $row = (array)$row;
                $html .= "<tr><td style='border: solid 1px #000;padding: 3px 5px;'>".($r_id+1)."</td>";
                foreach ($tableHeaders as $idx => $header) {
                    if ($idx >= ($step-1)*$colInTb && $idx < $step*$colInTb) {
                        $html .= "<td style='border: solid 1px #000;padding: 3px 5px;'>" . $row[$header['field']] . "</td>";
                    }
                }
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "</table>";
        }

        $pdf = new Dompdf();
        $pdf->setPaper("A4", "landscape");
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream($filename);
    }

    /*
     * Create data flow for json file
     */
    private function downloader_json(Table $table, $post) {
        $post['page'] = 1;
        $post['rows_per_page'] = 10000;
        $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

        $headers = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr['is_showed']) {
                $headers[$hdr['field']] = '';
            }
        }


        $to_print = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr['is_showed']) {
                $to_print[] = ['field' => $hdr['field'], 'title' => $hdr['name']];
            }
        }

        echo '{"headers":'.json_encode($to_print);
        echo ',"data":[';

        foreach ($tableRows['rows'] as $row) {
            $to_print = $headers;
            foreach ($row as $fld => $val) {
                if (isset($to_print[$fld])) {
                    $to_print[$fld] = str_replace('"','', $val);
                }
            }
            echo json_encode($to_print).',';
        }

        while ($tableRows['rows_count'] > ($post['page'] * $post['rows_per_page'])) {
            $post['page'] ++;
            $tableRows = $this->tableDataRepository->getRows($post, auth()->id());
            foreach ($tableRows['rows'] as $row) {
                $to_print = $headers;
                foreach ($row as $fld => $val) {
                    if (isset($to_print[$fld])) {
                        $to_print[$fld] = str_replace('"','', $val);
                    }
                }
                echo json_encode($to_print).',';
            }
        }

        echo json_encode($headers).']}';
    }

    /*
     * Create data flow for xml file
     */
    private function downloader_xml(Table $table, $post) {
        $post['page'] = 1;
        $post['rows_per_page'] = 10000;
        $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

        $headers = [];
        foreach ($table->_fields as $hdr) {
            if ($hdr['is_showed']) {
                $headers[$hdr['field']] = '';
            }
        }

        echo "<table><header>";

        foreach ($table->_fields as $hdr) {
            if (isset($headers[$hdr['field']])) {
                echo "<".$hdr['field'].">" . str_replace('"','', $hdr['name']) . "</".$hdr['field'].">";
            }
        }

        echo "</header>";

        foreach ($tableRows['rows'] as $row) {
            echo "</row>";
            foreach ($row as $fld => $val) {
                if (isset($headers[$fld])) {
                    echo "<".$fld.">" . str_replace('"','', $val) . "</".$fld.">";
                }
            }
            echo "</row>";
        }

        while ($tableRows['rows_count'] > ($post['page'] * $post['rows_per_page'])) {
            $post['page'] ++;
            $tableRows = $this->tableDataRepository->getRows($post, auth()->id());

            foreach ($tableRows['rows'] as $row) {
                echo "</row>";
                foreach ($row as $fld => $val) {
                    if (isset($headers[$fld])) {
                        echo "<".$fld.">" . str_replace('"','', $val) . "</".$fld.">";
                    }
                }
                echo "</row>";
            }
        }

        echo "</table>";
    }
}
