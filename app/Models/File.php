<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\File
 *
 * @property int $id
 * @property int $table_id
 * @property int $table_field_id
 * @property string $row_id
 * @property string $filepath
 * @property string $filename
 * @property int|null $filesize
 * @property int $is_img
 * @property string|null $notes
 * @property string|null $filehash
 * @property int|null $created_by
 * @property string|null $created_name
 * @property string $created_on
 * @property int|null $modified_by
 * @property string|null $modified_name
 * @property string $modified_on
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\TableField $_table_field
 * @property-read \Vanguard\Models\Table\Table $_table_info
 * @mixin \Eloquent
 */
class File extends Model
{
    protected $table = 'files';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'table_field_id',
        'row_id',
        'filepath',
        'filename',
        'filesize',
        'is_img',
        'notes',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];


    public function _table_info() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_field() {
        return $this->belongsTo(TableField::class, 'table_field_id', 'id');
    }


    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
