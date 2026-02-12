<?php

namespace Vanguard\Models\Pages;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Folder\Folder
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $cell_spacing
 * @property int $edge_spacing
 * @property int $border_width
 * @property int $border_radius
 * @property string $share_hash
 * @property string $name
 */
class Pages extends Model
{
    public $timestamps = false;

    protected $table = 'pages';

    protected $fillable = [
        'user_id',
        'name',

        'cell_spacing',
        'edge_spacing',
        'border_width',
        'border_radius',
        'share_hash',

        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];

    public function _contents()
    {
        return $this->hasMany(PageContents::class, 'page_id', 'id');
    }
}
