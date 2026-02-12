<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableSavedFilter;
use Vanguard\Services\Tablda\HelperService;

class TableSavedFilterRepository
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
     * @param int $model_id
     * @return TableSavedFilter
     */
    public function get(int $model_id)
    {
        return TableSavedFilter::where('id', '=', $model_id)->first();
    }

    /**
     * @param Table $table
     * @param array $input
     * @return array
     */
    protected function defData(Table $table, array $input): array
    {
        return array_merge($input, [
            'table_id' => $table->id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * @param Table $table
     * @param array $data
     * @return Model|TableSavedFilter
     */
    public function insert(Table $table, array $data)
    {
        $data = $this->defData($table, $data);
        return TableSavedFilter::create($this->service->delSystemFields($data));
    }

    /**
     * @param Table $table
     * @param $model_id
     * @param array $data
     * @return bool|int
     */
    public function update(Table $table, $model_id, array $data)
    {
        $model = $this->get($model_id);
        $model->name = $data['name'];
        $model->filters_object = $data['filters_object'];
        $model->related_colgroup_id = $data['related_colgroup_id'];
        return $model->save();
    }

    /**
     * @param $model_id
     * @return bool|int|null
     * @throws Exception
     */
    public function delete($model_id)
    {
        return TableSavedFilter::where('id', '=', $model_id)
            ->delete();
    }
}