<?php

namespace Vanguard\AppsModules\MtoDalPdfParser;


use Exception;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Support\Facades\Storage;
use Vanguard\AppsModules\ParseAndStoreDaLoadings;
use Vanguard\Models\AppSetting;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Modules\AiRequests\GeminiAiApi;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Services\Tablda\FileService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class MtoDalPdfParserModule
{
    use ParseAndStoreDaLoadings;

    /**
     * @param Table $table
     * @param TableFieldLink $link
     */
    public function __construct(Table $table, TableFieldLink $link)
    {
        $this->table = $table;
        $this->link = $link;
        $this->outputTable = $link->_mto_dal_output_table;
        $this->prefix = 'mtodal_';

        $this->dataService = new TableDataService();
    }

    /**
     * @param int $row_id
     * @return string
     * @throws Exception
     */
    public function parse(int $row_id): string
    {
        $this->masterRow = $this->dataService->getDirectRow($this->table, $row_id, ['none'])->toArray();

        //Receive JSON
        $content = $this->getContentFromPdf($row_id);

        //Load relations
        $this->link->load(['_link_app_correspondences']);

        //Remove prev records if needed
        if ($this->link->mto_dal_pdf_remove_prev_rec) {
            $this->removeRecordsByInheritedValues();
        }

        //Parse rows
        $this->parseContent($content, $row_id);

        return 'Parsing completed!';
    }

    /**
     * @param $row_id
     * @return array
     * @throws Exception
     */
    protected function getContentFromPdf($row_id): array
    {
        $repo = new FileRepository();
        $file = $repo->getSql($this->table->id, $this->link->mto_dal_pdf_doc_field_id, [$row_id])->first();
        copy(
            Storage::path('/public/'.$file->filepath.$file->filename),
            dirname(__FILE__).'/truePDFS_w_MTO_DAL/1.pdf'
        );

        shell_exec('chmod 777 '.dirname(__FILE__).'/truePDFS_w_MTO_DAL/1.pdf');
        shell_exec('python3 '.dirname(__FILE__).'/MTO_DAL_PDF_Parser.py');

        try {
            $json = file_get_contents(dirname(__FILE__) . '/truePDFS_w_MTO_DAL/Playride_Tables/1_Appurtenance.json');
            return json_decode($json, true);
        } catch (Exception $e) {
            throw new Exception('No data was found in the PDF', 1);
        }
    }
}