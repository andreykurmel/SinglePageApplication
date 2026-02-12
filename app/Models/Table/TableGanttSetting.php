<?php

namespace Vanguard\Models\Table;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Table\TableGantt
 *
 * @property int $id
 * @property int $table_gantt_id
 * @property int $table_field_id
 * @property int|null $gantt_tooltip
 * @property int|null $gantt_left_header
 * @property int|null $is_gantt_group
 * @property int|null $is_gantt_parent_group
 * @property int|null $is_gantt_name
 * @property int|null $is_gantt_parent
 * @property int|null $is_gantt_start
 * @property int|null $is_gantt_end
 * @property int|null $is_gantt_progress
 * @property int|null $is_gantt_color
 * @property int|null $is_gantt_label_symbol
 * @property int|null $is_gantt_milestone
 * @property int|null $is_gantt_main_group
 * @mixin \Eloquent
 */
class TableGanttSetting extends Model
{
    protected $table = 'table_gantt_settings';

    public $timestamps = false;

    protected $fillable = [
        'table_gantt_id',
        'table_field_id',
        'gantt_tooltip',
        'gantt_left_header',
        'is_gantt_group',
        'is_gantt_parent_group',
        'is_gantt_main_group',
        'is_gantt_name',
        'is_gantt_parent',
        'is_gantt_start',
        'is_gantt_end',
        'is_gantt_progress',
        'is_gantt_color',
        'is_gantt_label_symbol',
        'is_gantt_milestone',
    ];

    public static function booleans(): array
    {
        return array_merge(self::radios(), [
            'gantt_tooltip',
            'gantt_left_header',
        ]);
    }

    public static function radios(): array
    {
        return [
            'is_gantt_group',
            'is_gantt_parent_group',
            'is_gantt_main_group',
            'is_gantt_name',
            'is_gantt_parent',
            'is_gantt_start',
            'is_gantt_end',
            'is_gantt_progress',
            'is_gantt_color',
            'is_gantt_label_symbol',
            'is_gantt_milestone',
        ];
    }



    public function _gantt() {
        return $this->belongsTo(TableGantt::class, 'table_gantt_id', 'id');
    }
}
