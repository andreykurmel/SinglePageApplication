<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\User\UserCloud;
use Vanguard\User;

class TableBackup extends Model
{
    protected $table = 'table_backups';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_cloud_id',
        'name',
        'day',
        'timezone',
        'time',
        'mysql',
        'csv',
        'attach',
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


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
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
