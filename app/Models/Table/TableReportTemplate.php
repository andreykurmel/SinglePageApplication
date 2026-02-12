<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\User\UserCloud;

/**
 * Vanguard\Models\Table\TableReport
 *
 * @property int $id
 * @property int $table_id
 * @property int $user_id
 * @property string $name
 * @property string $doc_type
 * @property int|null $connected_cloud_id
 * @property string $template_source
 * @property string $template_file
 * @property string|null $cloud_report_folder
 * @property UserCloud|null $_cloud
 * @mixin Eloquent
 */
class TableReportTemplate extends Model
{
    public $timestamps = false;

    protected $table = 'table_report_templates';

    protected $fillable = [
        'table_id',
        'user_id',
        'name',
        'doc_type',
        'connected_cloud_id',
        'template_source', // ['Upload','URL']
        'template_file',
        'cloud_report_folder',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _cloud()
    {
        return $this->belongsTo(UserCloud::class, 'connected_cloud_id', 'id');
    }

    public function _user()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}
