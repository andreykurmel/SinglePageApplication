<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\EmailSettings
 *
 * @property int $id
 * @property string $email_code
 * @property string $scenario
 * @property string|null $sender_name
 * @property string|null $sender_email
 * @property string|null $to
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $subject
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereBcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereCc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereEmailCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereScenario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereSenderEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\EmailSettings whereUpdatedAt($value)
 */
class EmailSettings extends Model
{
    /**
     * @var string
     */
    protected $table = 'email_settings';

    /**
     * @var string[]
     */
    protected $fillable = [
        'email_code',
        'scenario',
        'sender_name',
        'sender_email',
        'to',
        'cc',
        'bcc',
        'subject'
    ];
}
