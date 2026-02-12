<?php

namespace Vanguard\Models\Pages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Vanguard\Models\Table\TableView;

/**
 * Vanguard\Models\Folder\Folder
 *
 * @property int $id
 * @property int $page_id
 * @property string|null $name
 * @property string $type
 * @property string|null $view_part
 * @property string $row_hash
 * @property string $grid_position
 * @property int|null $table_id
 * @property int|null $table_view_id
 */
class PageContents extends Model
{
    public $timestamps = false;

    protected $table = 'page_contents';

    protected $fillable = [
        'page_id',
        'name',
        'type',//['table_view']
        'view_part',
        'table_id',
        'table_view_id',
        'row_hash',
        'grid_position',
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    /**
     * @return HasOne
     */
    public function _mrv()
    {
        return $this->hasOne(TableView::class, 'id', 'table_view_id');
    }
}
