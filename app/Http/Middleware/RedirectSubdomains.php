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

        //skip [public,blog] subdomains
        if (in_array($service->cur_subdomain, $service->no_redirect_subdomains)) {
            return $next($request);
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
        if ($user && strtolower($user->subdomain) != $service->cur_subdomain) {
            return redirect( $service->getUrlWithSubdomain($user->subdomain) . $request->server('REQUEST_URI') );
        }

        return $next($request);
    }
}
