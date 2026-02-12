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

    /**
     * @return User
     */
    public static function unlogged(): User
    {
        $user = User::where('email', '=', 'unlogged@tablda.com')->first();
        if (!$user) {
            User::create([
                'email' => 'unlogged@tablda.com',
                'username' => 'Unlogged',
                'password' => 'no-pass',
                'role_id' => 2,
                'status' => 'Active',
            ]);
            $user = User::where('email', '=', 'unlogged@tablda.com')->first();
        }
        return $user;
    }

    /**
     * @return bool
     */
    public static function syncReloading(): bool
    {
        $user = auth()->id()
            ? User::where('id', '=', auth()->id())->first()
            : User::where('email', '=', 'unlogged@tablda.com')->first();

        $user->sync_reloading++;
        return $user->save();
    }
}