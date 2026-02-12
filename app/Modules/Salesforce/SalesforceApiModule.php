<?php

namespace Vanguard\Modules\Salesforce;


use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Vanguard\Modules\CloudBackup\ApiModuleInterface;
use Vanguard\Modules\CloudBackup\WithAccessToken;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class SalesforceApiModule implements ApiModuleInterface
{
    use WithAccessToken;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     */
    public function __construct(string $type = '')
    {
        $this->type = $type;
    }

    /**
     * @param int $cloud_id
     * @return string
     */
    public function getCloudActivationUrl(int $cloud_id): string
    {
        $app_key = env('SALESFORCE_APP_KEY');
        $redirect = env('SALESFORCE_CLOUD_ACTIVATE_URI');
        $state = json_encode([
            'cloud_id' => $cloud_id
        ]);
        return 'https://login.salesforce.com/services/oauth2/authorize?client_id=' . $app_key
            . '&redirect_uri=' . urlencode($redirect)
            . '&state=' . urlencode($state)
            . '&scope=' . urlencode(implode(' ', ['offline_access', 'api']))
            . '&code_challenge=' . $this->getCodeChallenge()
            . '&response_type=code';
    }

    /**
     * @param int $cloud_id
     * @return string
     */
    protected function getToken(int $cloud_id)
    {
        $token = (new UserCloudRepository())->getCloudToken($cloud_id);
        return $this->accessToken($token, $cloud_id);
    }

    /**
     * @param string $code
     * @param bool $is_refresh
     * @return array
     */
    public function getTokenFromCode(string $code, bool $is_refresh = false): array
    {
        $params = [
            'code' => $code,
            'refresh_token' => $code,
            'grant_type' => $is_refresh ? 'refresh_token' : 'authorization_code',
            'redirect_uri' => env('SALESFORCE_CLOUD_ACTIVATE_URI'),
            'client_id' => env('SALESFORCE_APP_KEY'),
            'client_secret' => env('SALESFORCE_APP_SECRET'),
        ];
        if (! $is_refresh) {
            $params['code_verifier'] = $this->getCodeVerifier();
        }

        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://login.salesforce.com/services/oauth2/token', [
            'form_params' => $params,
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listObjects(int $cloud_id, bool $custom = true): array
    {
        $curl = new Client();
        $response = $curl->get($this->salesforceUrl($cloud_id, 'sobjects/'), [
            'headers' => $this->headers($cloud_id),
        ]);

        $array = json_decode($response->getBody()->getContents(), true);
        $array = $array['sobjects'] ?? [];

        if ($custom) {
            $array = array_filter($array, function ($item) {
                return $item['custom'] ?? false;
            });
            $array = array_values($array);
        }

        return $array;
    }

    /**
     * @param int $cloud_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function objectFields(int $cloud_id, string $objectApiName): array
    {
        $curl = new Client();
        $response = $curl->get($this->salesforceUrl($cloud_id, 'query?q=SELECT FIELDS(ALL) FROM ' . $objectApiName . ' LIMIT 1'), [
            'headers' => $this->headers($cloud_id),
        ]);

        $array = json_decode($response->getBody()->getContents(), true);
        $record = Arr::first($array['records'] ?? []);
        unset($record['attributes']);

        return array_keys($record ?? []);
    }

    /**
     * @param int $cloud_id
     * @param string $objectApiName
     * @param int $startAt
     * @param string $lastAction
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function objectRows(int $cloud_id, string $objectApiName, int $startAt = 0, string $lastAction = ''): array
    {
        $curl = new Client();
        $query = 'SELECT FIELDS(ALL) FROM ' . $objectApiName;
        if ($lastAction) {
            $query .= ' WHERE LastModifiedDate > '.(Carbon::parse($lastAction)->format('Y-m-d\TH:i:s\Z')).' ';
        }
        $query .= ' LIMIT 100 OFFSET ' . $startAt;

        $response = $curl->get($this->salesforceUrl($cloud_id, 'query?q='.$query), [
            'headers' => $this->headers($cloud_id),
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @param string $path
     * @return string
     */
    protected function salesforceUrl(int $cloud_id, string $path): string
    {
        $cloud = (new UserCloudRepository())->getCloud($cloud_id);
        $token = json_decode($cloud->gettoken(), true);
        $instance = $token['instance_url'] ?? 'https://login.salesforce.com';
        return $instance . '/services/data/v65.0/' . $path;
    }

    /**
     * @param int $cloud_id
     * @return string[]
     */
    protected function headers(int $cloud_id): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getToken($cloud_id),
        ];
    }
}