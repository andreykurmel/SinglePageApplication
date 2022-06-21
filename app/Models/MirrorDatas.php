<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\MirrorDatas
 *
 * @property int $id
 * @property int $table_id
 * @property int $table_field_id
 * @property int $row_id
 * @property string|null $mirror_row_ids
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereMirrorRowIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereTableFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\MirrorDatas whereUpdatedAt($value)
 */
class MirrorDatas extends Model
{
    /**
     * @var string
     */
    protected $table = 'mirror_datas';

    /**
     * @var string[]
     */
    protected $fillable = [
        'table_id',
        'table_field_id',
        'row_id',
        'mirror_row_ids',
    ];
}
