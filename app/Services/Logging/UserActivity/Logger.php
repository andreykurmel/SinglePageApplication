<?php

namespace Vanguard\Services\Logging\UserActivity;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Factory;
use Vanguard\Repositories\Activity\ActivityRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;
use Illuminate\Http\Request;

class Logger
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Factory
     */
    private $auth;

    /**
     * @var User|null
     */
    protected $user = null;
    /**
     * @var ActivityRepository
     */
    private $activities;

    public function __construct(Request $request, Factory $auth, ActivityRepository $activities)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->activities = $activities;
    }

    /**
     * Log user action.
     *
     * @param $description
     * @return static
     */
    public function log($description)
    {
        return $this->activities->log([
            'description' => $description,
            'user_id' => $this->getUserId(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->getUserAgent()
        ]);
    }

    /**
     * @param $description
     * @return mixed
     */
    public function logUserIn($description)
    {
        $present = $this->activities->find( ['ip_address' => $this->request->ip()] );
        if ($present) {
            $lat = $present->lat ?: 0;
            $lng = $present->lng ?: 0;
        } else {
            $info = HelperService::getClientLocation( $this->request->ip() );
            $location = explode(',', $info['loc'] ?? '');
            $lat = $location[0] ?? 0;
            $lng = $location[1] ?? 0;
        }

        return $this->activities->log([
            'description' => $description,
            'description_time' => time(),
            'user_id' => $this->getUserId(),
            'lat' => $lat ?: 0,
            'lng' => $lng ?: 0,
            'year' => (new Carbon())->format("Y"),
            'month' => (new Carbon())->format("n"),
            'week' => (new Carbon())->format("W"),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->getUserAgent()
        ]);
    }

    /**
     * @param $find
     * @param $description
     * @return mixed
     */
    public function logUserOut($find, $description)
    {
        return $this->activities->logWhere([
            'ending' => $description,
            'ending_time' => time(),
            'difference_time' => \DB::raw(time().' - `description_time`'),
        ], [
            'user_id' => $this->getUserId(),
            'ending' => null,
            'description' => $find
        ]);
    }

    /**
     * Get id if the user for who we want to log this action.
     * If user was manually set, then we will just return id of that user.
     * If not, we will return the id of currently logged user.
     *
     * @return int|mixed|null
     */
    private function getUserId()
    {
        if ($this->user) {
            return $this->user->id;
        }

        return $this->auth->guard()->id();
    }

    /**
     * Get user agent from request headers.
     *
     * @return string
     */
    private function getUserAgent()
    {
        return substr((string) $this->request->header('User-Agent'), 0, 500);
    }

    /**
     * @param User|null $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
