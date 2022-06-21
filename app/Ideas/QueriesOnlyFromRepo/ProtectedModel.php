<?php

namespace Vanguard\Ideas\QueriesOnlyFromRepo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class ProtectedModel extends Model
{
    /**
     * Should be empty for database protection.
     * @var string
     */
    protected $table = '';

    /**
     * Should be filled for database access via Model::db()
     * @var string
     */
    protected $query_table = 'should_be_replaced';

    /**
     * @return ProtectedModel
     */
    public static function db()
    {
        $model = new static();
        return $model->setTable($model->query_table);
    }
}