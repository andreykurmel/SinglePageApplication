<?php

namespace Vanguard\Http\Controllers\Web\Auth;

use Authy;
use Vanguard\Classes\DiscourseSso;
use Vanguard\Events\User\LoggedIn;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Auth\Social\SaveEmailRequest;
use Vanguard\Repositories\User\UserRepository;
use Auth;
use Session;
use Socialite;
use Laravel\Socialite\Contracts\User as SocialUser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vanguard\Services\Auth\Social\SocialManager;
use Vanguard\Services\Tablda\HelperService;

class SocialAuthController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var SocialManager
     */
    private $socialManager;

    public function __construct(UserRepository $users, SocialManager $socialManager)
    {
        $this->middleware('guest');

        $this->users = $users;
        $this->socialManager = $socialManager;
    }

    /**
     * Redirect user to specified provider in order to complete the authentication process.
     *
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        if (strtolower($provider) == 'facebook') {
            return Socialite::driver('facebook')->with(['auth_type' => 'rerequest'])->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle response authentication provider.
     *
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        $socialUser = $this->getUserFromProvider($provider);

        $user = $this->users->findBySocialId($provider, $socialUser->getId());

        if (! $user) {
            if (! settings('reg_enabled')) {
                return redirect('login')->withErrors(trans('app.only_users_with_account_can_login'));
            }

            // Only allow missing email from Twitter provider
            if (! $socialUser->getEmail()) {
                return redirect('login')->withErrors(trans('app.you_have_to_provide_email'));
            }

            $user = $this->socialManager->associate($socialUser, $provider);
        }

        return $this->loginAndRedirect($user);
    }

    /**
     * Get user from authentication provider.
     *
     * @param $provider
     * @return SocialUser
     */
    private function getUserFromProvider($provider)
    {
        return Socialite::driver($provider)->user();
    }

    /**
     * Log provided user in and redirect him to intended page.
     *
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function loginAndRedirect($user)
    {
        if ($user->isBanned()) {
            return redirect()->to('login')
                ->withErrors(trans('app.your_account_is_banned'));
        }

        if (settings('2fa.enabled') && Authy::isEnabled($user)) {
            session()->put('auth.2fa.id', $user->id);
            return redirect()->route('auth.token');
        }

        Auth::login($user);
        (new DiscourseSso())->syncLogin();

        event(new LoggedIn);

        $serv = new HelperService($_SERVER['HTTP_REFERER']);
        $path = $serv->cur_subdomain == 'e3c' || $serv->matchReferer('apps')
            ? $serv->matchReferer('apps')
            : url('/data');

        $path = (new DiscourseSso())->checkLogin($path);

        return redirect($path);

        //return redirect()->intended('/');
    }
}
