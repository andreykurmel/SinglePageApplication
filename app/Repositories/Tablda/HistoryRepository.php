<?php

namespace Vanguard\Repositories\Tablda;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Vanguard\Models\HistoryField;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class HistoryRepository
{
    protected $service;
    protected $user_fields = [
        'id',
        'email',
        'username',
        'first_name',
        'last_name',
        'avatar'
    ];

    /**
     * HistoryRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Store History record.
     *
     * @param Table $table
     * @param int $user_id
     * @param int $field_id
     * @param int $row_id
     * @param $old_val
     * @return mixed
     */
    public function store(Table $table, int $user_id, int $field_id, int $row_id, $old_val)
    {
        $data = $this->newHistoryRow($table, $user_id, $field_id, $row_id, $old_val);
        return HistoryField::create($data);
    }

    /**
     * New History Row
     *
     * @param Table $table
     * @param int $user_id
     * @param int $field_id
     * @param int $row_id
     * @param $old_val
     * @return array
     */
    public function newHistoryRow(Table $table, int $user_id = null, int $field_id, int $row_id, $old_val)
    {
        $last = $this->getLastHistory($field_id, $row_id);
        $last = $last ? $last->toArray() : $table->toArray();

        $data = array_merge([
            'user_id' => $user_id,
            'table_id' => $table->id,
            'table_field_id' => $field_id,
            'row_id' => $row_id,
            'value' => $old_val
        ],
            $this->service->getModified($table)
        );
        return $this->service->setCreatedFromModif($data, $last);
    }

    /**
     * Get Last History Record.
     *
     * @param int $table_field_id
     * @param int $row_id
     * @return mixed
     */
    public function getLastHistory(int $table_field_id, int $row_id)
    {
        return $this->historyRequest($table_field_id, $row_id)
            ->first();
    }

    /**
     * Get History Builder.
     *
     * @param int $table_field_id
     * @param int $row_id
     * @return Builder
     */
    private function historyRequest(int $table_field_id, int $row_id)
    {
        return HistoryField::with('_user:' . join(',', $this->user_fields))
            ->whereNull('to_user_id')
            ->where('table_field_id', '=', $table_field_id)
            ->where('row_id', '=', $row_id)
            ->orderBy('id', 'desc');
    }

    /**
     * Get History for selected Field.
     *
     * @param int $table_field_id
     * @param int $row_id
     * @return Collection
     */
    public function getFieldHistory(int $table_field_id, int $row_id)
    {
        return $this->historyRequest($table_field_id, $row_id)
            ->get();
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @return \Illuminate\Database\Eloquent\Builder|Builder|HistoryField
     */
    protected function allHistory(Table $table, int $row_id)
    {
        return HistoryField::with([
            '_user:' . join(',', $this->user_fields),
            '_to_user:' . join(',', $this->user_fields),
            '_table_field:id,name,f_type',
        ])
            ->where('table_id', '=', $table->id)
            ->where('row_id', '=', $row_id)
            ->orderBy('id', 'desc');
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @return Collection
     */
    public function getAll(Table $table, int $row_id)
    {
        return $this->allHistory($table, $row_id)->limit(50)->get();
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @return int
     */
    public function getCount(Table $table, int $row_id)
    {
        return $this->allHistory($table, $row_id)->count();
    }

    /**
     * @param Table $table
     * @param int $row_id
     * @param int $from_user
     * @param int $to_user
     * @param $comment
     * @return HistoryField
     */
    public function insertComment(Table $table, int $row_id, int $from_user, int $to_user, $comment): HistoryField
    {
        $data = [
            'user_id' => $from_user,
            'table_id' => $table->id,
            'row_id' => $row_id,
            'to_user_id' => $to_user,
            'comment' => $comment,
        ];
        return HistoryField::create(array_merge(
            $this->service->delSystemFields($data),
            $this->service->getModified($table),
            $this->service->getCreated($table)
        ));
    }

    /**
     * @param Table $table
     * @param TableField $header
     * @param int $row_id
     * @return array
     */
    public function getCurrentHistory(Table $table, TableField $header, int $row_id)
    {
        $field = $header ? $header->field : '';
        $row = (new TableDataRepository())->getDirectRow($table, $row_id);
        $row = $row ? $row->toArray() : [];

        $history_row = $this->newHistoryRow($table, auth()->id(), $header->id, $row_id, $row[$field] ?? null);
        $history_row['_user'] = $this->getHistoryUser($row['modified_by'] ?? null);

        return $history_row;
    }

    /**
     * Get History User
     *
     * @param $user_id
     * @return mixed
     */
    public function getHistoryUser($user_id)
    {
        return User::where('id', '=', $user_id)
            ->select($this->user_fields)
            ->first();
    }

    /**
     * Get History by ID.
     *
     * @param array|int $ids
     * @return mixed
     */
    public function getHistory($ids)
    {
        return HistoryField::find($ids);
    }

    /**
     * Delete History record.
     *
     * @param HistoryField $historyField
     * @return mixed
     */
    public function delete(HistoryField $historyField)
    {
        return $historyField->delete();
    }
}