<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\User\UserCloud;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableBackup
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property int $user_cloud_id
 * @property int|null $is_active
 * @property string $name
 * @property string|null $day
 * @property string|null $timezone
 * @property string|null $time
 * @property int|null $mysql
 * @property int|null $csv
 * @property int|null $overwrite
 * @property int|null $ddl_attach
 * @property int|null $created_by
 * @property string $created_on
 * @property int|null $modified_by
 * @property string $modified_on
 * @property int|null $attach
 * @property string|null $root_folder
 * @property string|null $notes
 * @property int|null $bkp_email_field_id
 * @property int|null $bkp_addressee_field_id
 * @property string|null $bkp_email_field_static
 * @property string|null $bkp_email_subject
 * @property string|null $bkp_addressee_txt
 * @property string|null $bkp_email_message
 * @property-read \Vanguard\Models\Table\TableField|null $_bkp_addressee_field
 * @property-read \Vanguard\Models\Table\TableField|null $_bkp_email_field
 * @property-read \Vanguard\Models\User\UserCloud $_cloud
 * @property-read \Vanguard\User|null $_created_user
 * @property-read \Vanguard\User|null $_modified_user
 * @property-read \Vanguard\Models\Table\Table $_table
 * @property-read \Vanguard\User $_user
 * @mixin \Eloquent
 */
class TableBackup extends Model
{
    protected $table = 'table_backups';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'user_cloud_id',
        'name',
        'day',
        'overwrite',
        'timezone',
        'time',
        'mysql',
        'csv',
        'attach',
        'ddl_attach',
        'notes',
        'root_folder',
        'is_active',
        //messages
        'bkp_email_field_id',
        'bkp_email_field_static',
        'bkp_email_subject',
        'bkp_addressee_field_id',
        'bkp_addressee_txt',
        'bkp_email_message',
        //
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    /**
     * @return null|string
     */
    public function getsubfolder()
    {
        return $this->root_folder ?: $this->_user->username;
    }


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function _cloud() {
        return $this->belongsTo(UserCloud::class, 'user_cloud_id', 'id');
    }

    public function _bkp_email_field() {
        return $this->hasOne(TableField::class, 'id', 'bkp_email_field_id');
    }
    public function _bkp_addressee_field() {
        return $this->hasOne(TableField::class, 'id', 'bkp_addressee_field_id');
    }

    public function _created_user() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function _modified_user() {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}
