<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Exception;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\User\UserCloud;
use Vanguard\Modules\Jira\JiraApiModule;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Services\Tablda\ImportService;

class JiraController extends Controller
{
    /**
     * @var JiraApiModule
     */
    protected $api;

    /**
     *
     */
    public function __construct()
    {
        $this->api = new JiraApiModule();
    }

    /**
     * @param Request $request
     * @return UserCloud
     */
    protected function auth(Request $request): UserCloud
    {
        $cloud = (new UserCloudRepository())->getCloud($request->cloud_id);
        abort_if(!$cloud || $cloud->user_id != auth()->id(), 401, 'Unauthorized');
        return $cloud;
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function projects(Request $request)
    {
        $cloud = $this->auth($request);
        return $this->api->projects($cloud->id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function issues(Request $request)
    {
        $cloud = $this->auth($request);
        $fields = $this->api->issueFields($cloud->id, $request->project_names ?: [], $request->jql_query ?: '');
        $service = new ImportService();
        $headers = $service->makeHeaders(array_combine($fields, $fields), true);
        return [
            'headers' => $headers,
            'fields' => $fields,
            'name_converter' => $this->api->fieldNamesRelation($cloud->id, $fields),
        ];
    }
}
