<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\User;

class TableAlert extends Model
{
    protected $table = 'table_alerts';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'name',
        'is_active',
        'on_added',
        'on_updated',
        'on_deleted',

        'recipients',
        'row_mail_field_id',

        'mail_subject',
        'mail_addressee',
        'mail_message',
        'mail_format', //['table', 'list']
        'mail_col_group_id',
    ];


    public function _table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _row_mail_field() {
        return $this->hasOne(TableField::class, 'id', 'row_mail_field_id');
    }

    public function _conditions() {
        return $this->hasMany(TableAlertCondition::class, 'table_alert_id', 'id');
    }

    public function _col_group() {
        return $this->belongsTo(TableColumnGroup::class, 'mail_col_group_id', 'id');
    }
}
