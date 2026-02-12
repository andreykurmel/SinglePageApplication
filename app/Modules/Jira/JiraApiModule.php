<?php

namespace Vanguard\Modules\Jira;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Vanguard\Modules\CloudBackup\ApiModuleInterface;
use Vanguard\Modules\CloudBackup\WithAccessToken;
use Vanguard\Repositories\Tablda\UserCloudRepository;

class JiraApiModule implements ApiModuleInterface
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
        $app_key = env('JIRA_APP_KEY');
        $redirect = env('JIRA_CLOUD_ACTIVATE_URI');
        $state = json_encode([
            'cloud_id' => $cloud_id
        ]);
        return 'https://auth.atlassian.com/authorize?audience=api.atlassian.com&client_id=' . $app_key
            . '&redirect_uri=' . urlencode($redirect)
            . '&state=' . urlencode($state)
            . '&scope=' . urlencode(implode(' ', ['offline_access', 'read:jira-work', 'write:jira-work']))
            . '&prompt=consent'
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
        $curl = new \GuzzleHttp\Client();
        $response = $curl->post('https://auth.atlassian.com/oauth/token', [
            'form_params' => [
                'code' => $code,
                'refresh_token' => $code,
                'grant_type' => $is_refresh ? 'refresh_token' : 'authorization_code',
                'redirect_uri' => env('JIRA_CLOUD_ACTIVATE_URI'),
                'client_id' => env('JIRA_APP_KEY'),
                'client_secret' => env('JIRA_APP_SECRET'),
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @return void
     */
    public function connectAccessibleResource(int $cloud_id): void
    {
        $curl = new Client();
        $response = $curl->get('https://api.atlassian.com/oauth/token/accessible-resources', [
            'headers' => $this->headers($cloud_id),
        ]);
        $array = json_decode($response->getBody()->getContents(), true);
        (new UserCloudRepository())->updateUserCloud($cloud_id, [
            'extra_params' => Arr::first($array),
        ]);
    }

    /**
     * @param int $cloud_id
     * @return array
     * @throws \Exception
     */
    public function projects(int $cloud_id): array
    {
        $curl = new Client();
        try {
            $response = $curl->get($this->atlassianUrl($cloud_id, 'project'), [
                'headers' => $this->headers($cloud_id),
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @param string $jqlQuery
     * @param int $startAt
     * @param string $lastAction
     * @return array
     * @throws \Exception
     */
    public function issues(int $cloud_id, string $jqlQuery, int $startAt = 0, string $lastAction = ''): array
    {
        $curl = new Client();
        try {
            if ($lastAction) {
                $jqlQuery .= ' and updated >= "'.(Carbon::parse($lastAction)->format('Y-m-d H:i')).'"';
            }
            $response = $curl->get($this->atlassianUrl($cloud_id, 'search?jql='.$jqlQuery.'&maxResults=100&startAt='.$startAt), [
                'headers' => $this->headers($cloud_id),
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @return array
     * @throws \Exception
     */
    public function fields(int $cloud_id): array
    {
        $curl = new Client();
        try {
            $response = $curl->get($this->atlassianUrl($cloud_id, 'field'), [
                'headers' => $this->headers($cloud_id),
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $cloud_id
     * @param array $projects
     * @param string $jqlQuery
     * @return array
     * @throws \Exception
     */
    public function issueFields(int $cloud_id, array $projects = [], string $jqlQuery = ''): array
    {
        $fields = ['IssueId', 'IssueName'];
        if ($projects) {
            foreach ($projects as $project) {
                $issues = $this->issues($cloud_id, 'project="'.$project.'"');
                foreach ($issues['issues'] as $iss) {
                    $locFields = array_keys(array_filter($iss['fields']));
                    $fields = array_unique(array_merge($fields, $locFields));
                }
            }
        }
        if ($jqlQuery) {
            $issues = $this->issues($cloud_id, $jqlQuery);
            foreach ($issues['issues'] as $iss) {
                $locFields = array_keys(array_filter($iss['fields']));
                $fields = array_unique(array_merge($fields, $locFields));
            }
        }
        return array_values($fields);
    }

    /**
     * @param int $cloud_id
     * @param array $fields
     * @return array
     * @throws \Exception
     */
    public function fieldNamesRelation(int $cloud_id, array $fields): array
    {
        $names = collect($this->fields($cloud_id));
        $result = [];
        foreach ($fields as $fld) {
            $nameArr = $names->where('id', '=', $fld)->first() ?: [];
            $result[$fld] = $nameArr['name'] ?? $fld;
        }
        return $result;
    }

    /**
     * @param array $issue
     * @param string $key
     * @return mixed
     */
    public static function fromIssueReceive(array $issue, string $key)
    {
        if ($key == 'IssueId') {
            return $issue['id'];
        }
        elseif ($key == 'IssueName') {
            return $issue['key'];
        }
        else {
            $el = $issue['fields'][$key] ?? null;
            if (is_array($el)) {
                $sub = Arr::first($el) ?? null;
                return is_array($sub)
                    ? ($sub['name'] ?? $sub['value'] ?? $sub['displayName'] ?? $sub[$key] ?? null)
                    : ($el['name'] ?? $el['value'] ?? $el['displayName'] ?? $el[$key] ?? null);
            }
            return $el;
        }
    }

    /**
     * @param int $cloud_id
     * @param string $path
     * @return string
     */
    protected function atlassianUrl(int $cloud_id, string $path): string
    {
        $cloud = (new UserCloudRepository())->getCloud($cloud_id);
        return 'https://api.atlassian.com/ex/jira/'. $cloud->jiraCloudId() .'/rest/api/3/' . $path;
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