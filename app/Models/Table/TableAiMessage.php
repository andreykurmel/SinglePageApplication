<?php

namespace Vanguard\Models\Table;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\DataSetPermissions\TablePermission;

/**
 * Vanguard\Models\Table\TableAiRight
 *
 * @property int $id
 * @property int $table_ai_id
 * @property string $who
 * @property string $content
 * @property-read TableAi $_ai
 * @mixin Eloquent
 */
class TableAiMessage extends Model
{
    protected $table = 'table_ai_messages';

    public $timestamps = true;

    protected $fillable = [
        'table_ai_id',
        'who',
        'content'
    ];


    public function _ai()
    {
        return $this->belongsTo(TableAi::class, 'table_ai_id', 'id');
    }
}
