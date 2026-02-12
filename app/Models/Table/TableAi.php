<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableAi
 *
 * @property int $id
 * @property int $table_id
 * @property int|null $user_id
 * @property string $name
 * @property int $openai_key_id
 * @property array|null $related_table_ids
 * @property string $ai_data_range
 * @property string|null $bg_color
 * @property string|null $bg_me_color
 * @property string|null $bg_gpt_color
 * @property string|null $txt_color
 * @property string|null $font_family
 * @property array|null $font_style
 * @property int|null $font_size
 * @property string|null $description
 * @property int $with_table_data
 * @property int $with_outside_data
 * @property int $is_right_panel
 * @property Table $_table
 * @mixin Eloquent
 */
class TableAi extends Model
{
    public $timestamps = false;

    protected $table = 'table_ais';

    protected $fillable = [
        'table_id',
        'user_id',
        'name',
        'openai_key_id',
        'related_table_ids',
        'ai_data_range',
        'bg_color',
        'bg_me_color',
        'bg_gpt_color',
        'txt_color',
        'font_family',
        'font_style',
        'font_size',
        'description',
        'with_table_data',
        'with_outside_data',
        'is_right_panel',
    ];

    protected $casts = [
        'related_table_ids' => 'array',
        'font_style' => 'array',
    ];


    public function _table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function _table_permissions()
    {
        return $this->belongsToMany(TablePermission::class, 'table_ai_rights', 'table_ai_id', 'table_permission_id')
            ->as('_right')
            ->withPivot(['id', 'table_ai_id', 'table_permission_id', 'can_edit']);
    }

    public function _ai_rights()
    {
        return $this->hasMany(TableAiRight::class, 'table_ai_id', 'id');
    }

    public function _ai_messages()
    {
        return $this->hasMany(TableAiMessage::class, 'table_ai_id', 'id');
    }
}
