<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

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
