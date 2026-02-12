<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAi;
use Vanguard\Models\Table\TableAiMessage;
use Vanguard\Models\Table\TableAiRight;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Services\Tablda\HelperService;

class TableAiRepository
{
    protected $service;

    /**
     * TableAlertRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $table->load([
            '_table_ais' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' AIs for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with([
                    '_ai_rights',
                    '_ai_messages' => function ($m) {
                        $m->orderBy('created_at', 'desc')->limit(15);
                    },
                ]);
            }
        ]);
        return $table;
    }

    /**
     * @param int $model_id
     * @return TableAi
     */
    public function get(int $model_id)
    {
        return TableAi::where('id', '=', $model_id)->first();
    }

    /**
     * @param Table $table
     * @param array $input
     * @return array
     */
    protected function aiDef(Table $table, array $input): array
    {
        $input['is_right_panel'] = $input['is_right_panel'] ?? 0;
        return array_merge($input, [
            'table_id' => $table->id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * @param Table $table
     * @param array $data
     * @return Model|TableAi
     */
    public function insert(Table $table, array $data)
    {
        $data = $this->aiDef($table, $data);
        return TableAi::create($this->service->delSystemFields($data));
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(Table $table, $model_id, array $data)
    {
        $data = $this->aiDef($table, $data);
        $ai = TableAi::find($model_id);

        if (! empty($data['is_right_panel'])) {
            TableAi::where('table_id', '=', $table->id)
                ->where('id', '!=', $model_id)
                ->update(['is_right_panel' => 0]);
        }

        return $ai->update($this->service->delSystemFields($data));
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($model_id)
    {
        return TableAi::where('id', '=', $model_id)
            ->delete();
    }

    /**
     * @param TableAi $ai
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableAiRight
     */
    public function toggleAiRight(TableAi $ai, int $table_permis_id, $can_edit)
    {
        $right = $ai->_ai_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableAiRight::create([
                'table_ai_id' => $ai->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableAi $ai
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteAiRight(TableAi $ai, int $table_permis_id)
    {
        return $ai->_ai_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }

    /**
     * @param TableAi $ai
     * @param int $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAiMessage(TableAi $ai, int $offset)
    {
        return $ai->_ai_messages()
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit(10)
            ->get();
    }

    /**
     * @param TableAi $ai
     * @param string $message
     * @param array $requestParams
     * @return array
     * @throws Exception
     */
    public function sendAiMessage(TableAi $ai, string $message, array $requestParams)
    {
        $t = microtime(true);

        $question = TableAiMessage::create([
            'table_ai_id' => $ai->id,
            'who' => 'Me',
            'content' => $message,
        ]);

        $key = (new UserConnRepository())->getUserApi($ai->openai_key_id);
        if ($key) {
            $tableContext = $ai->with_table_data ? $this->getContextTable($ai, $requestParams) : '';
            $api = $key->AiInteface();
            $chatResponse = $api ? $api->textGeneration($message, $tableContext) : 'Module for '.$key->type.' was not found.';
        } else {
            $chatResponse = 'No API Key available.';
        }

        if (microtime(true) - $t < 1) {
            sleep(1);
        }

        $response = TableAiMessage::create([
            'table_ai_id' => $ai->id,
            'who' => 'ChatGPT',
            'content' => $chatResponse,
        ]);

        return [
            'question' => $question,
            'response' => $response,
        ];
    }

    /**
     * @param TableAi $ai
     * @param int|null $direct_id
     * @return bool|null
     * @throws Exception
     */
    public function removeAiMessage(TableAi $ai, int $direct_id = null)
    {
        $sql = TableAiMessage::where('table_ai_id', $ai->id);
        if ($direct_id) {
            $sql->where('id', '=', $direct_id);
        }
        return $sql->delete();
    }

    /**
     * @param TableAi $ai
     * @param array $requestParams
     * @return string
     */
    protected function getContextTable(TableAi $ai, array $requestParams): string
    {
        $multiple = count($ai->related_table_ids) > 1;
        $string = "Use the below table" . ($multiple ? 's' : '') . " to answer all questions.\n";
        $tables = Table::whereIn('id', $ai->related_table_ids)->get();

        foreach ($tables as $table) {
            $fields = $table
                ->_fields
                ->filter(function ($field) {
                    return !in_array($field->field, ['row_hash', 'static_hash', 'row_order', 'refer_tb_id', 'request_id', 'created_by', 'modified_on', 'modified_by'])
                        && $field->f_type != 'Attachment';
                })
                ->mapWithKeys(function ($field) {
                    return [$field->field => $field->name];
                });


            $json = [];
            $sql = new TableDataQuery($table, true, $table->user_id);

            if ($requestParams['table_id'] ?? 0 == $table->id) {
                $sql->testViewAndApplyWhereClauses($requestParams, $table->user_id);
                $sql->checkAndApplyDataRange($ai->ai_data_range ?: '-2');
            }

            $sql->getQuery()->chunk(100, function ($rows) use (&$json, $fields) {
                foreach ($rows as $row) {
                    $js = [];
                    foreach ($fields as $field => $name) {
                        $js[$name] = $row[$field] ?? '';
                    }
                    $json[] = $js;
                }
            });

            $string .= "Table '{$table->name}':\n" . json_encode($json) . "\n";
        }

        return $string;
    }
}