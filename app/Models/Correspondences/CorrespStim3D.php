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
 * @property string|null $horizontal
 * @property string|null $vertical
 * @property string|null $options
 * @property string|null $model_3d
 * @property string|null $type_tablda
 * @property string|null $row_hash
 * @property int $row_order
 * @property string $style
 * @property string|null $db_table
 * @mixin \Eloquent
 * @property string|null $avail_to_user
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereAccordion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereAvailToUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereDbTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereHorizontal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereModel3d($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereTopTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereTypeTablda($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\CorrespStim3D whereVertical($value)
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
        'horizontal',
        'vertical',
        'type_tablda',
        'db_table',
        'options',
        'model_3d',
    ];
}
