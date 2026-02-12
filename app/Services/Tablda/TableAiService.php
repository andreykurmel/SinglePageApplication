<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAi;
use Vanguard\Models\Table\TableAiRight;
use Vanguard\Repositories\Tablda\TableAiRepository;

class TableAiService
{
    protected $addonRepo;

    /**
     * TableTwilioAddonService constructor.
     */
    public function __construct()
    {
        $this->addonRepo = new TableAiRepository();
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $this->addonRepo->loadForTable($table, $user_id);
        return $table;
    }

    /**
     * @param int $model_id
     * @return TableAi
     */
    public function get(int $model_id)
    {
        return $this->addonRepo->get($model_id);
    }

    /**
     * @param Table $table
     * @param array $data
     * @return Model|TableAi
     */
    public function insert(Table $table, array $data)
    {
        return $this->addonRepo->insert($table, $data);
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(Table $table, $model_id, array $data)
    {
        return $this->addonRepo->update($table, $model_id, $data);
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($model_id)
    {
        return $this->addonRepo->delete($model_id);
    }

    /**
     * @param TableAi $ai
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableAiRight
     */
    public function toggleAiRight(TableAi $ai, int $table_permis_id, $can_edit)
    {
        return $this->addonRepo->toggleAiRight($ai, $table_permis_id, $can_edit);
    }

    /**
     * @param TableAi $ai
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteAiRight(TableAi $ai, int $table_permis_id)
    {
        return $this->addonRepo->deleteAiRight($ai, $table_permis_id);
    }

    /**
     * @param TableAi $ai
     * @param int $offset
     * @return mixed
     */
    public function getAiMessage(TableAi $ai, int $offset)
    {
        return $this->addonRepo->getAiMessage($ai, $offset);
    }

    /**
     * @param TableAi $ai
     * @param string $message
     * @param array $requestParams
     * @return array
     */
    public function sendAiMessage(TableAi $ai, string $message, array $requestParams)
    {
        return $this->addonRepo->sendAiMessage($ai, $message, $requestParams);
    }

    /**
     * @param TableAi $ai
     * @param int|null $direct_id
     * @return bool|null
     * @throws Exception
     */
    public function removeAiMessage(TableAi $ai, int $direct_id = null)
    {
        return $this->addonRepo->removeAiMessage($ai, $direct_id);
    }
}