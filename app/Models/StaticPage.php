<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\StaticPage
 *
 * @property int $id
 * @property string $type
 * @property string $url
 * @property string $name
 * @property string $content
 * @property string|null $embed_view
 * @property int|null $parent_id
 * @property int $is_folder
 * @property int $embed_view_active
 * @property string $node_icon
 * @property int $is_active
 * @property int $order
 * @property string|null $link_address
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereEmbedView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereEmbedViewActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereIsFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereLinkAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereNodeIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\StaticPage whereUrl($value)
 * @mixin \Eloquent
 */
class StaticPage extends Model
{
    protected $table = 'static_pages';

    public $timestamps = false;

    protected $fillable = [
        'type', // ['introduction','tutorials','templates','applications']
        'parent_id',
        'is_folder',
        'url',
        'name',
        'content',
        'node_icon',
        'link_address',
        'is_active',
        'order',
        'embed_view',
        'embed_view_active',
    ];
}
