<?php

namespace Vanguard\Modules\CloudBackup;

use Vanguard\Repositories\Tablda\UserCloudRepository;

trait WithAccessToken
{
    /**
     * Get Access Token.
     *
     * @param string $token_json
     * @param int $cloud_id
     * @return string
     */
    public function accessToken(string $token_json, int $cloud_id = 0): string
    {
        $token = json_decode($token_json, true);
        $cloud = (new UserCloudRepository())->getCloud($cloud_id);
        $exp = strtotime($cloud ? $cloud->modified_on : 'now') + intval($token['expires_in'] ?? 3600);
        if (!empty($token['refresh_token']) && time() > $exp) {
            $new_token = $this->getTokenFromCode($token['refresh_token'], true);
            $token['access_token'] = $new_token['access_token'] ?? '';
            $token['refresh_token'] = $new_token['refresh_token'] ?? $token['refresh_token'];

            if ($cloud_id) {
                (new UserCloudRepository())->updateUserCloud($cloud_id, ['token_json' => json_encode($token)]);
            }
        }
        return $token['access_token'] ?? '';
    }

    public function getCodeVerifier(): string
    {
        $hash = hash('sha256', now()->toDateString() . config('app.name'));
        return $hash . strtoupper($hash);
    }

    private function getCodeChallenge(): string
    {
        $codeVerifier = $this->getCodeVerifier();
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}