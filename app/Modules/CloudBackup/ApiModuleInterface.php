<?php

namespace Vanguard\Modules\CloudBackup;


interface ApiModuleInterface
{
    public function getCloudActivationUrl(int $cloud_id);

    public function getTokenFromCode(string $code);

    public function accessToken(string $token_json);
}