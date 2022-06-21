<?php

namespace Vanguard\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;

/**
 * Vanguard\Models\File
 *
 * @property int $id
 * @property int $table_id
 * @property int $table_field_id
 * @property string $row_id
 * @property string $remote_link
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property-read TableField $_table_field
 * @property-read Table $_table
 * @mixin Eloquent
 */
class RemoteFile extends Model
{
    public $timestamps = false;

    protected $table = 'remote_files';

    protected $fillable = [
        'table_id',
        'table_field_id',
        'row_id',
        'remote_link',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_field()
    {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }
}
