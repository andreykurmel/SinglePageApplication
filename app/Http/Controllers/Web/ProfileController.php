<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Vanguard\Events\User\ChangedAvatar;
use Vanguard\Events\User\TwoFactorDisabled;
use Vanguard\Events\User\TwoFactorEnabled;
use Vanguard\Events\User\UpdatedProfileDetails;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\User\EnableTwoFactorRequest;
use Vanguard\Http\Requests\User\UpdateProfileDetailsRequest;
use Vanguard\Http\Requests\User\UpdateProfileLoginDetailsRequest;
use Vanguard\Repositories\Activity\ActivityRepository;
use Vanguard\Repositories\Country\CountryRepository;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\Session\SessionRepository;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Services\Upload\UserAvatarManager;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;
use Auth;
use Authy;
use Illuminate\Http\Request;

/**
 * Class ProfileController
 * @package Vanguard\Http\Controllers
 */
class ProfileController extends Controller
{
    /**
     * @var User
     */
    protected $theUser;
    /**
     * @var UserRepository
     */
    private $users;
    private $rolesRepo;
    private $countryRepository;

    /**
     * ProfileController constructor.
     * @param UserRepository $users
     * @param RoleRepository $rolesRepo
     * @param CountryRepository $countryRepository
     */
    public function __construct(UserRepository $users, RoleRepository $rolesRepo, CountryRepository $countryRepository)
    {
        $this->middleware('auth');
        $this->middleware('session.database', ['only' => ['sessions', 'invalidateSession']]);

        $this->users = $users;
        $this->rolesRepo = $rolesRepo;
        $this->countryRepository = $countryRepository;

        $this->middleware(function ($request, $next) {
            $this->theUser = Auth::user();
            return $next($request);
        });
    }

    /**
     * Display user's profile page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->theUser;
        $edit = true;
        $roles = $this->rolesRepo->lists();
        $countries = [0 => 'Select a Country'] + $this->countryRepository->lists()->toArray();
        $phone_countries = $this->countryRepository->phone_codes();
        $socialLogins = $this->users->getUserSocialLogins($this->theUser->id);
        $statuses = UserStatus::lists();

        return view(
            'user/profile',
            compact('user', 'edit', 'roles', 'countries', 'phone_countries', 'socialLogins', 'statuses')
        );
    }

    /**
     * Display selected user's profile page for Admin.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewProfile($id)
    {
        $user = User::where('id', $id)->first();
        if ($this->theUser->isAdmin() && $user) {
            $edit = false;
            $roles = $this->rolesRepo->lists();
            $countries = [0 => 'Select a Country'] + $this->countryRepository->lists()->toArray();
            $phone_countries = $this->countryRepository->phone_codes();
            $socialLogins = $this->users->getUserSocialLogins($user->id);
            $statuses = UserStatus::lists();

            return view(
                'user/profile',
                compact('user', 'edit', 'roles', 'countries', 'phone_countries', 'socialLogins', 'statuses')
            );
        } else {
            return redirect()->route('profile');
        }
    }

    /**
     * Update profile details.
     *
     * @param UpdateProfileDetailsRequest $request
     * @return mixed
     */
    public function updateDetails(UpdateProfileDetailsRequest $request)
    {
        $data = $request->except('role_id', 'status');

        //save subdomain icon
        if ($icon = $request->file('icon_sub_domain')) {
            $fileName = 'subdomain_icons/' . auth()->id() . '_';
            $fileName .= preg_replace('/[^\w\d]/i', '_', $icon->getClientOriginalName());
            $icon->storeAs("public/", $fileName);

            $data = array_merge($data, ['sub_icon' => $fileName]);
        }
        //delete subdomain icon
        if ($request->delete_sub_icon == 1 && $this->theUser->sub_icon) {
            Storage::delete($this->theUser->sub_icon);
            $data['sub_icon'] = '';
        }

        $this->users->update($this->theUser->id, $data);

        event(new UpdatedProfileDetails);

        return redirect()->back()
            ->withSuccess(trans('app.profile_updated_successfully'));
    }

    /**
     * Upload and update user's avatar.
     *
     * @param Request $request
     * @param UserAvatarManager $avatarManager
     * @return mixed
     */
    public function updateAvatar(Request $request, UserAvatarManager $avatarManager)
    {
        $this->validate($request, [
            'avatar' => 'image'
        ]);

        $name = $avatarManager->uploadAndCropAvatar(
            $this->theUser,
            $request->file('avatar'),
            $request->get('points')
        );

        if ($name) {
            return $this->handleAvatarUpdate($name);
        }

        return redirect()->route('profile')
            ->withErrors(trans('app.avatar_not_changed'));
    }

    /**
     * Update avatar for currently logged in user
     * and fire appropriate event.
     *
     * @param $avatar
     * @return mixed
     */
    private function handleAvatarUpdate($avatar)
    {
        $this->users->update($this->theUser->id, ['avatar' => $avatar]);

        event(new ChangedAvatar);

        return redirect()->route('profile')
            ->withSuccess(trans('app.avatar_changed'));
    }

    /**
     * Update user's avatar from external location/url.
     *
     * @param Request $request
     * @param UserAvatarManager $avatarManager
     * @return mixed
     */
    public function updateAvatarExternal(Request $request, UserAvatarManager $avatarManager)
    {
        $avatarManager->deleteAvatarIfUploaded($this->theUser);

        return $this->handleAvatarUpdate($request->get('url'));
    }

    /**
     * Update user's login details.
     *
     * @param UpdateProfileLoginDetailsRequest $request
     * @return mixed
     */
    public function updateLoginDetails(UpdateProfileLoginDetailsRequest $request)
    {
        $data = $request->except('role', 'status');

        // If password is not provided, then we will
        // just remove it from $data array and do not change it
        if (trim($data['password']) == '') {
            unset($data['password']);

            unset($data['password_confirmation']);
        }

        $this->users->update($this->theUser->id, $data);

        return redirect()->route('profile')
            ->withSuccess(trans('app.login_updated'));
    }

    /**
     * Enable 2FA for currently logged user.
     *
     * @param EnableTwoFactorRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableTwoFactorAuth(EnableTwoFactorRequest $request)
    {
        if (Authy::isEnabled($this->theUser)) {
            return redirect()->route('user.edit', $this->theUser->id)
                ->withErrors(trans('app.2fa_already_enabled'));
        }

        $this->theUser->setAuthPhoneInformation($request->country_code, $request->phone_number, $request->two_factor_type);

        Authy::register($this->theUser);

        $this->theUser->save();

        event(new TwoFactorEnabled);

        return redirect()->route('profile')
            ->withSuccess(trans('app.2fa_enabled'));
    }

    /**
     * Disable 2FA for currently logged user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableTwoFactorAuth()
    {
        if (! Authy::isEnabled($this->theUser)) {
            return redirect()->route('profile')
                ->withErrors(trans('app.2fa_not_enabled_for_this_user'));
        }

        $this->theUser->two_factor_options = null;
        $this->theUser->save();

        try {
            Authy::delete($this->theUser);
        } catch (\Exception $e) {
            Log::info('Authy Error');
            Log::info($e->getMessage());
        }

        event(new TwoFactorDisabled);

        return redirect()->route('profile')
            ->withSuccess(trans('app.2fa_disabled'));
    }

    /**
     * Display user activity log.
     *
     * @param ActivityRepository $activitiesRepo
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activity(ActivityRepository $activitiesRepo, Request $request)
    {
        $user = $this->theUser;

        $activities = $activitiesRepo->paginateActivitiesForUser(
            $user->id,
            $perPage = 20,
            $request->get('search')
        );

        return view('activity.index', compact('activities', 'user'));
    }


    /**
     * Display active sessions for current user.
     *
     * @param SessionRepository $sessionRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sessions(SessionRepository $sessionRepository)
    {
        $profile = true;
        $user = $this->theUser;
        $sessions = $sessionRepository->getUserSessions($user->id);

        return view('user.sessions', compact('sessions', 'user', 'profile'));
    }

    /**
     * Invalidate user's session.
     *
     * @param $session \stdClass Session object.
     * @param SessionRepository $sessionRepository
     * @return mixed
     */
    public function invalidateSession($session, SessionRepository $sessionRepository)
    {
        $sessionRepository->invalidateSession($session->id);

        return redirect()->route('profile.sessions')
            ->withSuccess(trans('app.session_invalidated'));
    }
}
