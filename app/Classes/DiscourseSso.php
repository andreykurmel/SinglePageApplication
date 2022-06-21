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
        if ($sso_nonce && $sso_url && auth()->user() && preg_match('/\/discourse\/sso/i', $_SERVER['REQUEST_URI'])) {//opened not from Iframe.
            Session::put('sso_nonce', null);
            Session::put('sso_url', null);
            $signed = $this->ssoQuery($sso_nonce);
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
        if (auth()->user() && config('app.discourse_secret') && !empty($_COOKIE['_discourse_sso'])) {
            $pars = [];
            parse_str(preg_replace('/\?/i', '', $_COOKIE['_discourse_sso']), $pars);
            $sso = $pars['sso'];
            $sig = $pars['sig'];

            if ($sso && $sig && hash_hmac('sha256', $sso, config('app.discourse_secret')) == $sig) {
                $params = [];
                $querystr = base64_decode( urldecode($sso) );
                parse_str($querystr, $params);
                if (!empty($params['nonce'])) {
                    $signed = $this->ssoQuery($params['nonce']);
                    setcookie('_discourse_login', $params['return_sso_url'] . '?' . $signed);
                    setcookie('_discourse_sso', '');
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
        return config('app.discourse_uri');
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
            'nonce' => ( $sso_nonce ),
            'external_id' => ( auth()->user()->id ),
            'email' => ( auth()->user()->email ),
            'username' => ( auth()->user()->username ),
            'name' => ( auth()->user()->first_name . ' ' . auth()->user()->last_name ),
            //'avatar_url' => ( auth()->user()->avatarLink() ),
        ];
        $payload = base64_encode( http_build_query($user) );
        return http_build_query([
            'sso' => $payload,
            'sig' => hash_hmac('sha256', $payload, config('app.discourse_secret')),
        ]);
    }

    /**
     * @param string $url
     * @param string|null $cookie
     * @return array
     */
    protected function ssoCurl(string $url, string $cookie = null)
    {
        $ch = curl_init();
        if($cookie) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Set-Cookie: ".$cookie));
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_HEADER, true );
        $response = explode("\r\n", curl_exec( $ch ));
        curl_close($ch);
        $info = [];
        foreach ($response as $elem) {
            $keyval = explode(": ", $elem);
            if (count($keyval) == 2) {
                $info[ strtolower($keyval[0]) ] = $keyval[1];
            }
        }
        return $info;
    }
}