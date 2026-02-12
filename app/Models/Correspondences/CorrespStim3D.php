<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Correspondences\CorrespStim3D
 *
 * @property int $id
 * @property string|null $top_tab
 * @property string|null $select
 * @property string|null $accordion
 * @property string|null $horizontal_lvl1
 * @property string|null $vertical_lvl1
 * @property string|null $horizontal_lvl2
 * @property string|null $vertical_lvl2
 * @property string|null $options
 * @property string|null $model_3d
 * @property string|null $type_tablda
 * @property string|null $row_hash
 * @property int $row_order
 * @property string $style
 * @property string|null $db_table
 * @property string|null $avail_to_user
 * @property string|null $stimvis_status
 * @property int|null $stimvis_table_id
 * @property int|null $stimvis_field_id
 * @property string|null $stimvis_operator
 * @property string|null $stimvis_value
 * @mixin \Eloquent
 */
class CorrespStim3D extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'correspondence_stim_3d';

    public $timestamps = false;

    protected $fillable = [
        'avail_to_user',
        'top_tab',
        'select',
        'style', // ['vh_tabs', 'accordion']
        'accordion',
        'horizontal_lvl1',
        'vertical_lvl1',
        'horizontal_lvl2',
        'vertical_lvl2',
        'type_tablda',
        'db_table',
        'options',
        'model_3d',
        'stimvis_status',
        'stimvis_table_id',
        'stimvis_field_id',
        'stimvis_operator',
        'stimvis_value',
    ];
}
