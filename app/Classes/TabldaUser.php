<?php

namespace Vanguard\Classes;


use Vanguard\Models\Correspondences\StimAppView;
use Vanguard\User;

class TabldaUser
{

    /**
     * @param array $request
     * @return bool|\Illuminate\Contracts\Auth\Authenticatable|null|User
     */
    public static function get(array $request)
    {
        $user = auth()->id() && !empty($request['user_id'])
            ? auth()->user()
            : new User();

        if ($request['user_hash'] ?? '') {
            $appview = StimAppView::where('hash', '=', $request['user_hash'] ?? '')->first();
            if ($appview) {
                $user = User::where('id', '=', $appview->user_id)->first();
            }
        }
        return $user;
    }
}