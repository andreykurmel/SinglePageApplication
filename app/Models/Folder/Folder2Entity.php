<?php

namespace Vanguard\Models\Folder;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\User;

/**
 * Vanguard\Models\Folder\Folder2Table
 *
 * @property int $id
 * @property int $entity_id
 * @property string $entity_type
 * @property int $user_id
 * @property int|null $folder_id
 * @property string $structure
 */
class Folder2Entity extends Model
{
    protected $table = 'folders_2_entities';

    public $timestamps = false;

    protected $fillable = [
        'entity_id',
        'entity_type',//['page']
        'user_id',
        'folder_id',
        'structure',//['public','private','favorite','account']
        'created_by',
        'created_on',
        'modified_by',
        'modified_on'
    ];
}
