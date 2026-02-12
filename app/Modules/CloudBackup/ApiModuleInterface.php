<?php

namespace Vanguard\Modules\CloudBackup;


interface ApiModuleInterface
{
    /**
     * @param string $type
     */
    public function __construct(string $type = '');

    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id): string;

    /**
     * @param string $code
     * @param bool $is_refresh
     * @return array
     */
    public function getTokenFromCode(string $code, bool $is_refresh = false): array;

    /**
     * @param string $token_json
     * @param int $cloud_id
     * @return string
     */
    public function accessToken(string $token_json, int $cloud_id = 0): string;
}