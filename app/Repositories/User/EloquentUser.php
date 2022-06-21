<?php

namespace Vanguard\Repositories\User;

use Carbon\Carbon;
use DB;
use Laravel\Socialite\Contracts\User as SocialUser;
use Vanguard\Models\AppTheme;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Subscription;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\Tablda\FolderRepository;
use Vanguard\Role;
use Vanguard\Services\Auth\Social\ManagesSocialAvatarSize;
use Vanguard\Services\Tablda\UserService;
use Vanguard\Services\Upload\UserAvatarManager;
use Vanguard\User;

class EloquentUser implements UserRepository
{
    use ManagesSocialAvatarSize;

    /**
     * @var UserAvatarManager
     */
    private $avatarManager;
    /**
     * @var RoleRepository
     */
    private $roles;
    private $userService;

    public function __construct(UserAvatarManager $avatarManager, RoleRepository $roles, UserService $userService)
    {
        $this->avatarManager = $avatarManager;
        $this->roles = $roles;
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return User::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmailOrUsername($email, $username)
    {
        return User::where('email', $email)->orWhere('username', $username)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySocialId($provider, $providerId)
    {
        return User::leftJoin('social_logins', 'users.id', '=', 'social_logins.user_id')
            ->select('users.*')
            ->where('social_logins.provider', $provider)
            ->where('social_logins.provider_id', $providerId)
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySessionId($sessionId)
    {
        return User::leftJoin('sessions', 'users.id', '=', 'sessions.user_id')
            ->select('users.*')
            ->where('sessions.id', $sessionId)
            ->first();
    }

    /**
     * @param $invite
     * @param $mail
     * @param bool $is_create
     */
    public function storeInvites($invite, $mail, $is_create = false)
    {
        $hash = $invite ?: \session('invite');
        $email = $mail && filter_var($mail, FILTER_VALIDATE_EMAIL) ? $mail : '';
        if ($hash) {
            \session(['invite' => $hash]);
            if ($email) {
                \session(['invited_mail' => $email]);
            }
            if ($hash && $email && $is_create) {
                $this->userService->inviteAccepted($email, $hash);
            }
        }
    }

    /**
     *
     */
    public function awardInvites()
    {
        $hash = \session('invite');
        $mail = \session('invited_mail');
        if ($hash && $mail) {
            $this->userService->awardReferral($mail, $hash);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $data['avail_credit'] = 15;
        $user = User::create($data);

        if (!settings('reg_email_confirmation')) {
            $this->awardInvites();
        } elseif ($data['email'] ?? null) {
            $this->storeInvites('', $data['email'], true);
        }

        $this->userService->checkAndSetPlan($user);

        //create system folders.
        $repo = new FolderRepository();
        $repo->insertSystems($user->id);

        //create themes
        while ($user->_themes()->count() < AppTheme::USERS_THEMES_COUNT) {
            AppTheme::create([
                'obj_type' => 'user',
                'obj_id' => $user->id,
            ]);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function associateSocialAccountForUser($userId, $provider, SocialUser $user)
    {
        return DB::table('social_logins')->insert([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_id' => $user->getId(),
            'avatar' => $this->getAvatarForProvider($provider, $user),
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage, $search = null, $status = null)
    {
        $query = User::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', "like", "%{$search}%");
                $q->orWhere('email', 'like', "%{$search}%");
                $q->orWhere('first_name', 'like', "%{$search}%");
                $q->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $result = $query->orderBy('id', 'desc')
            ->paginate($perPage);

        if ($search) {
            $result->appends(['search' => $search]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        if (isset($data['country_id']) && $data['country_id'] == 0) {
            $data['country_id'] = null;
        }

        $user = $this->find($id);

        $user->update($data);

        //watch for Applications
        $app_user = User::where('id', $id)->first();
        CorrespApp::where('user_id', $app_user ? $app_user->id : null)
            ->update([
                'subdomain' => $app_user ? $app_user->subdomain : null,
                'icon_full_path' => $app_user ? $app_user->sub_icon : null,
            ]);
        //-----------

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $user = $this->find($id);

        $this->avatarManager->deleteAvatarIfUploaded($user);

        return $user->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return User::count();
    }

    /**
     * {@inheritdoc}
     */
    public function newUsersCount()
    {
        return User::whereBetween('created_at', [Carbon::now()->firstOfMonth(), Carbon::now()])
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countByStatus($status)
    {
        return User::where('status', $status)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function latest($count = 20)
    {
        return User::orderBy('created_at', 'DESC')
            ->limit($count)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function countOfNewUsersPerMonth(Carbon $from, Carbon $to)
    {
        $result = User::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get(['created_at'])
            ->groupBy(function ($user) {
                return $user->created_at->format("Y_n");
            });

        $counts = [];

        while ($from->lt($to)) {
            $key = $from->format("Y_n");

            $counts[$this->parseDate($key)] = count($result->get($key, []));

            $from->addMonth();
        }

        return $counts;
    }

    /**
     * Parse date from "Y_m" format to "{Month Name} {Year}" format.
     * @param $yearMonth
     * @return string
     */
    private function parseDate($yearMonth)
    {
        list($year, $month) = explode("_", $yearMonth);

        $month = trans("app.months.{$month}");

        return "{$month} {$year}";
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersWithRole($roleName)
    {
        return Role::where('name', $roleName)
            ->first()
            ->users;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserSocialLogins($userId)
    {
        return DB::table('social_logins')
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function setRole($userId, $roleId)
    {
        return $this->find($userId)->setRole($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function findByConfirmationToken($token)
    {
        return User::where('confirmation_token', $token)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function switchRolesForUsers($fromRoleId, $toRoleId)
    {
        return User::where('role_id', $fromRoleId)
            ->update(['role_id' => $toRoleId]);
    }
}
