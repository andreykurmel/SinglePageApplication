<?php

namespace Vanguard\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Vanguard\Services\Tablda\HelperService;

class RedirectSubdomains
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * RedirectSubdomains constructor.
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $service = new HelperService();
        $user = $this->auth->user();
        $avail_subdom = $user && $user->_available_features && $user->_available_features->apps_are_avail;

        //skip [public,blog] subdomains
        if (in_array($service->cur_subdomain, $service->no_redirect_subdomains)) {
            return $next($request);
        }

        //return to homepage if subdomains are not available
        if ($service->cur_subdomain && !$avail_subdom) {
            return redirect( $service->getUrlWithSubdomain('') );
        }

        //ignore redirect for all urls except '/data/'
        if (!preg_match('/^data/i', $request->path())) {
            //redirect to url without subdomain if not 'ping'
            if ($service->cur_subdomain != '' && !preg_match('/^ping/i', $request->path())) {
                return redirect( $service->getUrlWithSubdomain('') . $request->server('REQUEST_URI') );
            }
            return $next($request);
        }

        //redirect not-logged user to site without subdomain
        if (!$user && $service->cur_subdomain) {
            return redirect( $service->getUrlWithSubdomain('') . $request->server('REQUEST_URI') );
        }
        //redirect users to their subdomain
        if ($user && $avail_subdom && strtolower($user->subdomain) != $service->cur_subdomain) {
            return redirect( $service->getUrlWithSubdomain($user->subdomain) . $request->server('REQUEST_URI') );
        }

        return $next($request);
    }
}
