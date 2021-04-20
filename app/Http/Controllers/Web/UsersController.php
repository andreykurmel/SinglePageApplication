<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Events\User\Banned;
use Vanguard\Events\User\Deleted;
use Vanguard\Events\User\TwoFactorDisabledByAdmin;
use Vanguard\Events\User\TwoFactorEnabledByAdmin;
use Vanguard\Events\User\UpdatedByAdmin;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\User\CreateBulkUsersRequest;
use Vanguard\Http\Requests\User\CreateUserRequest;
use Vanguard\Http\Requests\User\EnableTwoFactorRequest;
use Vanguard\Http\Requests\User\UpdateDetailsRequest;
use Vanguard\Http\Requests\User\UpdateLoginDetailsRequest;
use Vanguard\Repositories\Activity\ActivityRepository;
use Vanguard\Repositories\Country\CountryRepository;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\Session\SessionRepository;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Role;
use Vanguard\Services\Upload\UserAvatarManager;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;
use Auth;
use Authy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

/**
 * Class UsersController
 * @package Vanguard\Http\Controllers
 */
class UsersController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * UsersController constructor.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->middleware('auth');
        $this->middleware('session.database', ['only' => ['sessions', 'invalidateSession']]);
        $this->middleware('permission:users.manage');
        $this->users = $users;
    }

    /**
     * Display paginated list of all users.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $users = $this->users->paginate(
            $perPage = 20,
            Input::get('search'),
            Input::get('status')
        );

        $statuses = ['' => trans('app.all')] + UserStatus::lists();

        return view('user.list', compact('users', 'statuses'));
    }

    /**
     * Displays user profile page.
     *
     * @param User $user
     * @param ActivityRepository $activities
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(User $user, ActivityRepository $activities)
    {
        $userActivities = $activities->getLatestActivitiesForUser($user->id, 10);

        return view('user.view', compact('user', 'userActivities'));
    }

    /**
     * Displays form for creating a new user.
     *
     * @param CountryRepository $countryRepository
     * @param RoleRepository $roleRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(CountryRepository $countryRepository, RoleRepository $roleRepository)
    {
        $countries = $this->parseCountries($countryRepository);
        $phone_countries = $countryRepository->phone_codes();
        $roles = $roleRepository->lists();
        $statuses = UserStatus::lists();
        $user = null;

        return view('user.add', compact('user', 'countries', 'phone_countries', 'roles', 'statuses'));
    }

    /**
     * Displays form for bulk creating a new users from CSV.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bulk()
    {
        return view('user.add_bulk');
    }

    /**
     * Parse countries into an array that also has a blank
     * item as first element, which will allow users to
     * leave the country field unpopulated.
     * @param CountryRepository $countryRepository
     * @return array
     */
    private function parseCountries(CountryRepository $countryRepository)
    {
        return [0 => 'Select a Country'] + $countryRepository->lists()->toArray();
    }

    /**
     * Stores new user into the database.
     *
     * @param CreateUserRequest $request
     * @return mixed
     */
    public function store(CreateUserRequest $request)
    {
        // When user is created by administrator, we will set his
        // status to Active by default.
        $data = $request->all() + ['status' => UserStatus::ACTIVE];

        if (! array_get($data, 'country_id')) {
            $data['country_id'] = null;
        }

        // Username should be updated only if it is provided.
        // So, if it is an empty string, then we just leave it as it is.
        if (trim($data['username']) == '') {
            $data['username'] = null;
        }

        $user = $this->users->create($data);

        return redirect()->route('user.list')
            ->withSuccess(trans('app.user_created'));
    }

    /**
     * Stores new bulk users into the database if uploaded correct csv.
     *
     * @param CreateBulkUsersRequest $request
     * @return mixed
     */
    public function storeBulk(CreateBulkUsersRequest $request)
    {
        $tmp_csv = time()."_".rand().".csv";
        $request->users->storeAs('csv', $tmp_csv);

        $columns = 0;
        $row_idx = 0;
        $existing_users = [];
        $role = Role::where('name', 'User')->first();
        $fileHandle = fopen(storage_path("app/csv/".$tmp_csv), 'r');

        while (($row = fgetcsv($fileHandle)) !== FALSE) {
            $row_idx++;
            if (!$columns) {
                $columns = count($row);
            }

            if ($columns != count($row)) {
                return ['error' => "Incorrect csv format (your rows have different number of columns)!"];
            }

            if ($row_idx == 1 && ((strtolower($row[0]) == 'username') || (strtolower($row[1]) == 'email') || (strtolower($row[2]) == 'password'))) {
                continue; //it seems that first row is 'header'
            }

            $data = [
                'username' => preg_replace('/[^\w\d_]/i', '', $row[0]),
                'email' => trim($row[1]),
                'password' => trim($row[2]),
                'first_name' => trim($row[3]),
                'last_name' => trim($row[4]),
                'role_id' => $role->id,
                'status' => UserStatus::ACTIVE
            ];

            if (!$data['username'] || !$data['email'] || !$data['password']) {
                continue; //it seems that row doens't contain required fields
            }

            // When user is created by administrator, we will set his
            // status to Active by default.
            if (!$this->users->findByEmailOrUsername($data['email'], $data['username'])) {
                $user = $this->users->create($data);
            } else {
                $filter_data = collect($data)->except(['role_id', 'status'])->toArray();
                array_push( $existing_users, implode(', ', $filter_data ) );
            }
        }

        Storage::delete(storage_path("app/csv/".$tmp_csv));

        if (count($existing_users)) {
            $str = trans('app.user_bulk_created_with_existings', ['list' => implode('<br>', $existing_users)] );
        } else {
            $str = trans('app.user_bulk_created');
        }

        return redirect()->route('user.list')
            ->withSuccess($str);
    }

    /**
     * Displays edit user form.
     *
     * @param User $user
     * @param CountryRepository $countryRepository
     * @param RoleRepository $roleRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(User $user, CountryRepository $countryRepository, RoleRepository $roleRepository)
    {
        $edit = true;
        $countries = $this->parseCountries($countryRepository);
        $phone_countries = $countryRepository->phone_codes();
        $roles = $roleRepository->lists();
        $statuses = UserStatus::lists();
        $socialLogins = $this->users->getUserSocialLogins($user->id);

        return view(
            'user.edit',
            compact('edit', 'user', 'countries', 'phone_countries', 'socialLogins', 'roles', 'statuses')
        );
    }

    /**
     * Updates user details.
     *
     * @param User $user
     * @param UpdateDetailsRequest $request
     * @return mixed
     */
    public function updateDetails(User $user, UpdateDetailsRequest $request)
    {
        $data = $request->all();

        if (! array_get($data, 'country_id')) {
            $data['country_id'] = null;
        }

        $this->users->update($user->id, $data);
        $this->users->setRole($user->id, $request->role_id);

        event(new UpdatedByAdmin($user));

        // If user status was updated to "Banned",
        // fire the appropriate event.
        if ($this->userIsBanned($user, $request)) {
            event(new Banned($user));
        }

        return redirect()->back()
            ->withSuccess(trans('app.user_updated'));
    }

    /**
     * Check if user is banned during last update.
     *
     * @param User $user
     * @param Request $request
     * @return bool
     */
    private function userIsBanned(User $user, Request $request)
    {
        return $user->status != $request->status && $request->status == UserStatus::BANNED;
    }

    /**
     * Update user's avatar from uploaded image.
     *
     * @param User $user
     * @param UserAvatarManager $avatarManager
     * @return mixed
     */
    public function updateAvatar(User $user, UserAvatarManager $avatarManager, Request $request)
    {
        $this->validate($request, ['avatar' => 'image']);

        $name = $avatarManager->uploadAndCropAvatar(
            $user,
            $request->file('avatar'),
            $request->get('points')
        );

        if ($name) {
            $this->users->update($user->id, ['avatar' => $name]);

            event(new UpdatedByAdmin($user));

            return redirect()->route('user.edit', $user->id)
                ->withSuccess(trans('app.avatar_changed'));
        }

        return redirect()->route('user.edit', $user->id)
            ->withErrors(trans('app.avatar_not_changed'));
    }

    /**
     * Update user's avatar from some external source (Gravatar, Facebook, Twitter...)
     *
     * @param User $user
     * @param Request $request
     * @param UserAvatarManager $avatarManager
     * @return mixed
     */
    public function updateAvatarExternal(User $user, Request $request, UserAvatarManager $avatarManager)
    {
        $avatarManager->deleteAvatarIfUploaded($user);

        $this->users->update($user->id, ['avatar' => $request->get('url')]);

        event(new UpdatedByAdmin($user));

        return redirect()->route('user.edit', $user->id)
            ->withSuccess(trans('app.avatar_changed'));
    }

    /**
     * Update user's login details.
     *
     * @param User $user
     * @param UpdateLoginDetailsRequest $request
     * @return mixed
     */
    public function updateLoginDetails(User $user, UpdateLoginDetailsRequest $request)
    {
        $data = $request->all();

        if (trim($data['password']) == '') {
            unset($data['password']);
            unset($data['password_confirmation']);
        }

        $this->users->update($user->id, $data);

        event(new UpdatedByAdmin($user));

        return redirect()->route('user.edit', $user->id)
            ->withSuccess(trans('app.login_updated'));
    }

    /**
     * Removes the user from database.
     *
     * @param User $user
     * @return $this
     */
    public function delete(User $user)
    {
        if ($user->id == Auth::id()) {
            return redirect()->route('user.list')
                ->withErrors(trans('app.you_cannot_delete_yourself'));
        }

        $this->users->delete($user->id);

        event(new Deleted($user));

        return redirect()->route('user.list')
            ->withSuccess(trans('app.user_deleted'));
    }

    /**
     * Enables Authy Two-Factor Authentication for user.
     *
     * @param User $user
     * @param EnableTwoFactorRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableTwoFactorAuth(User $user, EnableTwoFactorRequest $request)
    {
        if (Authy::isEnabled($user)) {
            return redirect()->route('user.edit', $user->id)
                ->withErrors(trans('app.2fa_already_enabled_user'));
        }

        $user->setAuthPhoneInformation($request->country_code, $request->phone_number, $request->two_factor_type);

        Authy::register($user);

        $user->save();

        event(new TwoFactorEnabledByAdmin($user));

        return redirect()->route('user.edit', $user->id)
            ->withSuccess(trans('app.2fa_enabled'));
    }

    /**
     * Disables Authy Two-Factor Authentication for user.
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableTwoFactorAuth(User $user)
    {
        if (! Authy::isEnabled($user)) {
            return redirect()->route('user.edit', $user->id)
                ->withErrors(trans('app.2fa_not_enabled_user'));
        }

        Authy::delete($user);

        $user->save();

        event(new TwoFactorDisabledByAdmin($user));

        return redirect()->route('user.edit', $user->id)
            ->withSuccess(trans('app.2fa_disabled'));
    }


    /**
     * Displays the list with all active sessions for selected user.
     *
     * @param User $user
     * @param SessionRepository $sessionRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sessions(User $user, SessionRepository $sessionRepository)
    {
        $adminView = true;
        $sessions = $sessionRepository->getUserSessions($user->id);

        return view('user.sessions', compact('sessions', 'user', 'adminView'));
    }

    /**
     * Invalidate specified session for selected user.
     *
     * @param User $user
     * @param $session
     * @param SessionRepository $sessionRepository
     * @return mixed
     */
    public function invalidateSession(User $user, $session, SessionRepository $sessionRepository)
    {
        $sessionRepository->invalidateSession($session->id);

        return redirect()->route('user.sessions', $user->id)
            ->withSuccess(trans('app.session_invalidated'));
    }
}