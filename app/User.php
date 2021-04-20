<?php

namespace Vanguard;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Vanguard\Models\AppTheme;
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
        'memutree_hash',
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
        return $this->hasMany(UserGroup::class, 'user_id', 'id');
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
    public function _google_email_accs() {
        return $this->hasMany(UserEmailAccount::class, 'user_id', 'id');
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
    public function avatarLink() {
        if (!$this->avatar) {
            return '/assets/img/profile.png';
        }

        return preg_match('/^http/i', $this->avatar)
            ? $this->avatar
            : '/upload/users/' . $this->avatar;
    }
}
