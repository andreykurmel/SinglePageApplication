<?php

namespace Vanguard\Models\User;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Modules\AiRequests\GeminiAiApi;
use Vanguard\Modules\AiRequests\OpenAiApi;
use Vanguard\Modules\AiRequests\StdAiCalls;
use Vanguard\User;

/**
 * Vanguard\Models\User\UserApiKey
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $this
 * @property string|null $model
 * @property string|null $auth_token
 * @property string|null $air_base
 * @property string|null $air_type
 * @property string|null $twilio_phone
 * @property string|null $twiml_app_id
 * @property string|null $search_key
 * @property string|null $jira_email
 * @property string|null $jira_host
 * @property int $is_active
 * @property-read User $_user
 * @mixin Eloquent
 * @property string $name
 * @property string $notes
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereAirBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereAirType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\User\UserApiKey whereUserId($value)
 */
class UserApiKey extends Model
{
    public $timestamps = false;
    protected $table = 'user_api_keys';
    protected $fillable = [
        'user_id',
        'name',
        'type', // 'google','sendgrid','extracttable','airtable','twilio','jira','openai','gemini'
        'key',
        'model',
        'auth_token',
        'air_base', // used in key
        'air_type',
        'twilio_phone',
        'twiml_app_id',
        'jira_email',
        'jira_host',
        'search_key',
        'notes',
        'is_active',
    ];


    /**
     * @return string
     */
    public function decryptedKey()
    {
        return TabldaEncrypter::decrypt($this->key ?? '');
    }

    /**
     * @return string
     */
    public function decryptedToken()
    {
        return TabldaEncrypter::decrypt($this->auth_token ?? '');
    }

    /**
     * @return GeminiAiApi|OpenAiApi|null
     */
    public function AiInteface()
    {
        if ($this->type == 'openai') {
            return new OpenAiApi($this->decryptedKey(), $this->getAiModel());
        }
        if ($this->type == 'gemini') {
            return new GeminiAiApi($this->decryptedKey(), $this->getAiModel());
        }
        return null;
    }

    public function getAiModel(): string
    {
        $model = '';
        if ($this->type == 'openai') {
            $model = $this->model ?: 'gpt-4o-mini';
        }
        if ($this->type == 'gemini') {
            $model = $this->model ?: '2.5-flash';
        }
        return strtolower($model);
    }


    /**
     * @return BelongsTo
     */
    public function _user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
