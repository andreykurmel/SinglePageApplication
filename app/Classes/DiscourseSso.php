<?php

namespace Vanguard\Classes;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Vanguard\Events\User\LoggedOut;

class DiscourseSso
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLogin(Request $request)
    {
        if (hash_hmac('sha256', $request->sso, config('app.discourse_secret')) == $request->sig) {
            $params = [];
            $querystr = base64_decode( urldecode($request->sso) );
            parse_str($querystr, $params);
            if (!empty($params['nonce']) && !empty($params['return_sso_url'])) {
                Session::put('sso_nonce', $params['nonce']);
                Session::put('sso_url', $params['return_sso_url']);
                $socialProviders = config('auth.social.providers');
                return view('auth.login', compact('socialProviders'));
            }
        }
        abort(404);
    }

    /**
     * @param string $path
     * @return string
     */
    public function checkLogin(string $path) {
        $sso_nonce = Session::get('sso_nonce');
        $sso_url = Session::get('sso_url');
        if ($sso_nonce && $sso_url && auth()->user()) {
            Session::put('sso_nonce', null);
            Session::put('sso_url', null);
            $query = $this->ssoQuery($sso_nonce);
            $signed = 'sso=' . urlencode($query) . '&sig=' . hash_hmac('sha256', $query, config('app.discourse_secret'));
            $path = $sso_url . '?' . $signed;
        }
        return $path;
    }

    /**
     * @param int $user_id
     * @return array|mixed
     */
    public function syncUnlog(int $user_id) {
        if ($user_id && config('app.discourse_api_key') && config('app.discourse_api_username')) {
            $url = config('app.discourse_logout_url') . $user_id . '/log_out';
            $headers = [
                'Content-Type: multipart/form-data;',
                'Api-Key: '.config('app.discourse_api_key'),
                'Api-Username: '.config('app.discourse_api_username'),
            ];
            Session::put('discourse_community_url', '');
            return json_decode(PostCurl::send($url, $headers), true);
        }
        return [];
    }

    /**
     * @return array|mixed
     */
    public function syncLogin() {
        if (auth()->user() && config('app.discourse_url_login') && config('app.discourse_secret')) {
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'https://community.tablda.com/session/sso' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            $str = curl_exec( $ch );

            $sso = [];
            preg_match('/sso=([\w\d%]*)/i', $str, $sso);
            $sso = $sso[1] ?? '';

            $sig = [];
            preg_match('/sig=([\w\d%]*)/i', $str, $sig);
            $sig = $sig[1] ?? '';

            if ($sso && $sig) {
                $params = [];
                $querystr = base64_decode( urldecode($sso) );
                parse_str($querystr, $params);
                if (!empty($params['nonce']) && !empty($params['return_sso_url'])) {
                    $query = $this->ssoQuery($params['nonce']);
                    $signed = 'sso=' . urlencode($query) . '&sig=' . hash_hmac('sha256', $query, config('app.discourse_secret'));
                    Session::put('discourse_community_url', $params['return_sso_url'] . '?' . $signed);
                }
            }
        }
        return [];
    }

    /**
     * @return string
     */
    public static function communityUrl()
    {
        $path = Session::get('discourse_community_url');
        if ($path) {
            Session::put('discourse_community_url', '');
            return $path;
        } else {
            return config('app.discourse_uri');
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout()
    {
        $usr_id = auth()->id();
        if ($usr_id) {
            event(new LoggedOut);
            \Auth::logout();
            $this->syncUnlog($usr_id);
        }
    }

    /**
     * @param string $sso_nonce
     * @return string
     */
    protected function ssoQuery(string $sso_nonce)
    {
        $user = [
            'nonce' => urlencode( $sso_nonce ),
            'external_id' => urlencode( auth()->user()->id ),
            'email' => urlencode( auth()->user()->email ),
            'username' => urlencode( auth()->user()->username ),
            'name' => urlencode( auth()->user()->first_name . ' ' . auth()->user()->last_name ),
            'avatar_url' => urlencode( auth()->user()->avatarLink() ),
        ];
        return base64_encode( http_build_query($user) );
    }
}