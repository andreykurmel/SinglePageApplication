<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\EmailSettings
 *
 * @property int $id
 * @property string $category
 * @property string $format
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @property string|null $row_hash
 * @property int|null $row_order
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereRowHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereRowOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\UploadingFileFormats whereUpdatedAt($value)
 */
class UploadingFileFormats extends Model
{
    /**
     * @var string
     */
    protected $table = 'uploading_file_formats';

    /**
     * @var string[]
     */
    protected $fillable = [
        'category',
        'format',
    ];
}
