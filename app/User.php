<?php

namespace Vanguard;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Vanguard\Models\AppTheme;
use Vanguard\Models\MenutreeRecent;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\SubscribedApp;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserCard;
use Vanguard\Models\User\UserCloud;
use Vanguard\Models\User\UserEmailAccount;
use Vanguard\Models\User\UserGroup;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\User\Plan;
use Vanguard\Models\User\PlanFeature;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\UserInvitation;
use Vanguard\Models\User\UserPaymentKey;
use Vanguard\Models\User\UserSubscription;
use Vanguard\Presenters\UserPresenter;
use Vanguard\Services\Auth\Api\TokenFactory;
use Vanguard\Services\Auth\TwoFactor\Authenticatable as TwoFactorAuthenticatable;
use Vanguard\Services\Auth\TwoFactor\Contracts\Authenticatable as TwoFactorAuthenticatableContract;
use Vanguard\Services\Logging\UserActivity\Activity;
use Vanguard\Support\Authorization\AuthorizationUserTrait;
use Vanguard\Support\Enum\UserStatus;
use Illuminate\Auth\Passwords\CanResetPassword;
use Laracasts\Presenter\PresentableTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Vanguard\User
 *
 * @property int $id
 * @property string $email
 * @property string|null $username
 * @property string $password
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $avatar
 * @property string|null $address
 * @property int|null $country_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $birthday
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property string|null $confirmation_token
 * @property string $status
 * @property int|null $two_factor_country_code
 * @property int|null $two_factor_phone
 * @property string|null $two_factor_options
 * @property string $two_factor_type
 * @property string|null $two_factor_etoken
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $avail_credit
 * @property string|null $company
 * @property string|null $team
 * @property int|null $plan_feature_id
 * @property string $renew
 * @property int $recurrent_pay
 * @property string|null $timezone
 * @property string|null $subdomain
 * @property string|null $sub_icon
 * @property int $auto_logout
 * @property string|null $personal_hash
 * @property float $invitations_reward
 * @property string|null $stripe_user_id
 * @property string|null $paypal_card_id
 * @property string|null $paypal_card_last
 * @property string $pay_method
 * @property int $use_credit
 * @property string|null $tos_accepted
 * @property int|null $selected_card
 * @property int|null $app_theme_id
 * @property string $memutree_hash
 * @property int $sync_reloading
 * @property \Vanguard\Models\User\UserSubscription|null $_all_subs
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_api_keys
 * @property int|null $_api_keys_count
 * @property \Vanguard\Models\User\PlanFeature|null $_available_features
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserCard[] $_cards
 * @property int|null $_cards_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserCloud[] $_clouds
 * @property int|null $_clouds_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Folder\Folder[] $_folders
 * @property int|null $_folders_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_google_api_keys
 * @property int|null $_google_api_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserEmailAccount[] $_google_email_accs
 * @property int|null $_google_email_accs_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\MenutreeRecent[] $_menutree_recents
 * @property int|null $_menutree_recents_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserInvitation[] $_invitations
 * @property int|null $_invitations_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup[] $_member_of_groups
 * @property int|null $_member_of_groups_count
 * @property \Vanguard\Models\User\UserSubscription|null $_next_subscription
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserPaymentKey[] $_payment_keys
 * @property int|null $_payment_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserPaymentKey[] $_paypal_payment_keys
 * @property int|null $_paypal_payment_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_sendgrid_api_keys
 * @property int|null $_sendgrid_api_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_twilio_api_keys
 * @property int|null $_twilio_api_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserPaymentKey[] $_stripe_payment_keys
 * @property int|null $_stripe_payment_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\SubscribedApp[] $_subscribed_apps
 * @property int|null $_subscribed_apps_count
 * @property \Vanguard\Models\User\UserSubscription|null $_subscription
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Table\Table[] $_tables
 * @property int|null $_tables_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\AppTheme[] $_themes
 * @property int|null $_themes_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserGroup[] $_user_groups
 * @property int|null $_user_groups_count
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Services\Logging\UserActivity\Activity[] $activities
 * @property int|null $activities_count
 * @property \Vanguard\Country|null $country
 * @property bool $using_two_factor_auth
 * @property \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property int|null $notifications_count
 * @property \Vanguard\Role $role
 * @mixin \Eloquent
 * @property int $extracttable_terms
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_airtable_api_keys
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_extracttable_api_keys
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_ai_api_keys
 * @property \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\User\UserApiKey[] $_jira_api_keys
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\User whereExtracttableTerms($value)
 */
class User extends Authenticatable implements TwoFactorAuthenticatableContract, JWTSubject
{
    use TwoFactorAuthenticatable,
        CanResetPassword,
        PresentableTrait,
        AuthorizationUserTrait,
        Notifiable;

    protected $presenter = UserPresenter::class;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $dates = ['last_login', 'birthday'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'username', 'first_name', 'last_name', 'phone', 'avatar',
        'address', 'country_id', 'birthday', 'last_login', 'confirmation_token', 'status',
        'remember_token', 'role_id', 'avail_credit', 'company', 'team', 'plan_feature_id',
        'recurrent_pay', 'timezone', 'subdomain', 'sub_icon', 'auto_logout', 'renew',
        'personal_hash', 'invitations_reward', 'tos_accepted', 'app_theme_id',
        'stripe_user_id', 'paypal_card_id', 'paypal_card_last', 'pay_method', 'use_credit',
        'memutree_hash', 'extracttable_terms', 'sync_reloading',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function _tables() {
        return $this->hasMany(Table::class, 'user_id', 'id');
    }

    public function _user_groups() {
        return $this->hasMany(UserGroup::class, 'user_id', 'id')
            ->where('is_system', '=', 0);
    }

    public function _sys_user_groups() {
        return $this->hasMany(UserGroup::class, 'user_id', 'id')
            ->where('is_system', '=', 1);
    }

    public function _member_of_groups() {
        return $this->belongsToMany(UserGroup::class, 'user_groups_2_users', 'user_id', 'user_group_id')
            ->as('_link')
            ->withPivot(['user_group_id', 'user_id', 'cached_from_conditions', 'is_edit_added']);
    }

    public function _folders() {
        return $this->hasMany(Folder::class, 'user_id', 'id');
    }

    public function _invitations() {
        return $this->hasMany(UserInvitation::class, 'user_id', 'id')
            ->orderBy('status');
    }

    public function _menutree_recents() {
        return $this->hasMany(MenutreeRecent::class, 'user_id', 'id')
            ->orderBy('last_access', 'desc')
            ->limit(50);
    }

    public function _cards() {
        return $this->hasMany(UserCard::class, 'user_id', 'id');
    }

    public function _subscription() {
        return $this->hasOne(UserSubscription::class, 'user_id', 'id')
            ->where('active', 1);
    }

    public function _next_subscription() {
        return $this->hasOne(UserSubscription::class, 'user_id', 'id')
            ->where('active', 0);
    }

    public function _all_subs() {
        return $this->hasOne(UserSubscription::class, 'user_id', 'id');
    }

    public function _available_features() {
        return $this->hasOne(PlanFeature::class, 'id', 'plan_feature_id');
    }

    public function _themes() {
        return $this->hasMany(AppTheme::class, 'obj_id', 'id')
            ->where('obj_type', 'user');
    }

    public function _subscribed_apps() {
        return $this->hasMany(SubscribedApp::class, 'user_id', 'id');
    }

    public function _clouds() {
        return $this->hasMany(UserCloud::class, 'user_id', 'id');
    }

    public function _api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id');
    }
    public function _google_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'google');
    }
    public function _sendgrid_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'sendgrid');
    }
    public function _twilio_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'twilio');
    }
    public function _extracttable_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'extracttable');
    }
    public function _ai_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->whereIn('type', ['openai', 'gemini']);
    }
    public function _openai_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'openai');
    }
    public function _gemini_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'gemini');
    }
    public function _jira_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'jira');
    }
    public function _airtable_api_keys() {
        return $this->hasMany(UserApiKey::class, 'user_id', 'id')
            ->where('type', '=', 'airtable');
    }
    public function _google_email_accs() {
        return $this->hasMany(UserEmailAccount::class, 'user_id', 'id')
            ->where('type', '=', 'google');
    }

    public function _payment_keys() {
        return $this->hasMany(UserPaymentKey::class, 'user_id', 'id');
    }
    public function _stripe_payment_keys() {
        return $this->hasMany(UserPaymentKey::class, 'user_id', 'id')
            ->where('type', '=', 'stripe');
    }
    public function _paypal_payment_keys() {
        return $this->hasMany(UserPaymentKey::class, 'user_id', 'id')
            ->where('type', '=', 'paypal');
    }

    /**
     * Always encrypt password when it is updated.
     *
     * @param $value
     * @return string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function setBirthdayAttribute($value)
    {
        $this->attributes['birthday'] = trim($value) ?: null;
    }

    public function full_name()
    {
        return $this->first_name
            ? $this->first_name . ($this->last_name ? ' ' . $this->last_name : '')
            : $this->username;
    }

    public function gravatar()
    {
        $hash = hash('md5', strtolower(trim($this->attributes['email'])));

        return sprintf("https://www.gravatar.com/avatar/%s?size=150", $hash);
    }

    public function isUnconfirmed()
    {
        return $this->status == UserStatus::UNCONFIRMED;
    }

    public function isActive()
    {
        return $this->status == UserStatus::ACTIVE;
    }

    public function isBanned()
    {
        return $this->status == UserStatus::BANNED;
    }

    public function isAdmin()
    {
        return $this->role_id == 1;
    }

    public function canEditStatic()
    {
        return in_array($this->role_id, [1,3]);
    }

    public function isSessionActive()
    {
        $sess = \DB::connection('mysql')
            ->table('sessions')
            ->where('user_id', $this->id)
            ->first();
        $date = time()-60;

        return $sess && $sess->last_activity > $date;
    }

    public function disconnectOtherSessions()
    {
        \DB::connection('mysql')
            ->table('sessions')
            ->where('user_id', $this->id)
            ->update([
                'disconnected' => 1
            ]);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'user_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->id;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $token = app(TokenFactory::class)->forUser($this);

        return [
            'jti' => $token->id
        ];
    }

    /**
     * @return string
     */
    public function avatarLink()
    {
        if (preg_match('/^http/i', $this->avatar)) {
            return $this->avatar;
        }

        $link = '/upload/users/' . $this->avatar;
        return file_exists(public_path($link)) ? $link : '/assets/img/profile.png';
    }
}
