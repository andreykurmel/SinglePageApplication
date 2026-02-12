<?php

namespace Vanguard\AppsModules\DaLoadingOcrParser;


use Exception;
use GuzzleHttp\Psr7\MimeType;
use Vanguard\AppsModules\ParseAndStoreDaLoadings;
use Vanguard\Models\AppSetting;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Modules\AiRequests\GeminiAiApi;
use Vanguard\Services\Tablda\FileService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class DaLoadingOcrParserModule
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
        $this->outputTable = $link->_da_output_table;
        $this->prefix = 'da_';

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
        $content = $this->getContentFromImage($row_id);

        //Load relations
        $this->link->load(['_link_app_correspondences']);

        //Remove prev records if needed
        if ($this->link->eri_remove_prev_records) {
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
    protected function getContentFromImage($row_id): array
    {
        $apiKey = null;

//        if ($this->link->da_loading_type == 'stim') {
//            $stimId = AppSetting::where('key', '=', 'stim_user_id')->first();
//            $stimUser = User::find($stimId->value);
//            $apiKey = $stimUser ? $stimUser->_ai_api_keys()->first() : null;
//        }

        if (!$apiKey) {
            $apiKey = auth()->user()->_ai_api_keys()->where('id', '=', $this->link->da_loading_gemini_key_id)->first();
        }
        if (!$apiKey) {
            throw new Exception('AI Model was not found!', 1);
        }

        if (!$this->link->_da_output_table) {
            throw new Exception('Output table was not found!', 1);
        }

        return $this->parseDocumentByAI($this->prompt, $this->link->da_loading_image_field_id, $row_id);
    }

protected $prompt = '
You are a highly accurate document parser tasked with extracting every single row from a table labeled “DESIGNED APPURTENANCE LOADING”. The table structure is either a **two-column** or **one-column** layout, where each column contains two subcolumns labeled:
- TYPE
- ELEVATION

These are tabular entries, often listing items like antennas or mounts with optional carrier or quantity information embedded in brackets.

Your job is to:
1. Carefully scan and extract **ALL rows** from both left and right columns (if both exist), row by row, without missing a single entry.
2. If there is 2 colums then extract all the information from left column then go to right column.
2. Extract each row’s details precisely based on the **text shown in the TYPE and ELEVATION columns**.
3. Treat each pair of TYPE and ELEVATION as one unique item. If either is missing, skip the row.
4. For the TYPE column, follow this breakdown:

    **Field Extraction Logic:**
    - **Qty**: If TYPE starts with a number in parentheses, like “(2)”, that number is the quantity. If missing, default Qty = 1.
    - **Type**: This is the core description **excluding any parentheses**, such as equipment type or model name.
    - **Carrier/Note**: If the last part of the TYPE string includes a name inside parentheses (like “(Verizon)”), that is the carrier or note. If not present, return null.

5. The ELEVATION value should be taken exactly as shown including the decimal part if available. If elevation is unclear or missing, return null.

---

IMPORTANT RULES:
- Do NOT merge, reorder, or summarize any rows.
- Do NOT infer or assume values.
- If any value cannot be determined from the image, return **null**.
- Extract the values **exactly as shown**, cleaned of parentheses for `Type` and `Carrier`.

---

EXTRACTION FORMAT:
Return a valid JSON list. Each row should follow this format:

[
  {
    "Serial": 1,
    "Qty": 2,
    "Type": "Commscope NHH-65C-R2B w/MP",
    "Carrier": "Verizon",
    "Elevation": 195
  },
  {
    "Serial": 2,
    "Qty": 1,
    "Type": "Raycap DC-12 Surge Suppressor",
    "Carrier": null,
    "Elevation": 175.45
  }
  // More rows...
]

---

Only return valid JSON. Do NOT include any explanations, comments, or markdown formatting like ```json. If no valid rows are found, return an empty list: `[]`.
';
}