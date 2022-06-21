<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

/**
 * Vanguard\Models\Table\TableViewFiltering
 *
 * @property int $id
 * @property int $table_view_id
 * @property int $field_id
 * @property string $criteria
 * @property int|null $active
 * @property int|null $input_only
 * @property-read \Vanguard\Models\Table\TableField $_field
 * @property-read \Vanguard\Models\Table\TableView $_view
 * @mixin \Eloquent
 */
class TableViewFiltering extends Model
{
    protected $table = 'table_view_filtering';

    public $timestamps = false;

    protected $fillable = [
        'table_view_id',
        'active',
        'field_id',
        'criteria',
        'input_only',
    ];


    public function _field() {
        return $this->belongsTo(TableField::class, 'field_id', 'id');
    }

    public function _view() {
        return $this->belongsTo(TableView::class, 'table_view_id', 'id');
    }
}
