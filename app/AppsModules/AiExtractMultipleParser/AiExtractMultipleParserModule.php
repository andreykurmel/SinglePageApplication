<?php

namespace Vanguard\AppsModules\AiExtractMultipleParser;


use Exception;
use Illuminate\Support\Arr;
use Vanguard\AppsModules\ParseAndStoreDaLoadings;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\TableDataService;

class AiExtractMultipleParserModule
{
    use ParseAndStoreDaLoadings;

    protected $prompt = '';

    /**
     * @param Table $table
     * @param TableFieldLink $link
     */
    public function __construct(Table $table, TableFieldLink $link)
    {
        $this->table = $table;
        $this->link = $link;
        $this->outputTable = $link->_ai_extract_output_table ?: $table;
        $this->prefix = 'ai_ext_';

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
        $content = $this->getContentFromAI($row_id);

        //Load relations
        $this->link->load(['_link_app_correspondences']);

        //Remove prev records if needed
        if ($this->link->ai_extract_remove_prev_rec) {
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
    protected function getContentFromAI($row_id): array
    {
        $repo = new UserConnRepository();
        $apiKey = $repo->getUserApi($this->table->openai_tb_key_id ?: 0, true);
        if (!$apiKey) {
            $apiKey = $repo->defaultAiAcc($this->table->user_id);
        }

        $this->prompt = "Extract the following data points from the attached document and return them in a flat JSON object,
with no nested levels. Use keys with the same name as the data points, except replace # with Number (e.g., Application Number
instead of Application #). Include the following data if available:";
        foreach ($this->link->_link_app_correspondences as $daLoading) {
            if ($daLoading->is_active && $daLoading->column_key) {
                $this->prompt .= "\n- " . $daLoading->column_key;
            }
        }
        $this->prompt .= "\nReturn only the flat JSON object. Do not include any explanation or formatting.";

        $content = $this->parseDocumentByAI($this->prompt, $this->link->ai_extract_doc_field_id, $row_id, $this->link->ai_extract_ai_id);
        if (!is_numeric(Arr::first(array_keys($content)))) {
            $content = [$content];
        }
        return $content;
    }

}