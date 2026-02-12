<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\User\UserCloud;
use Vanguard\Modules\Salesforce\SalesforceApiModule;
use Vanguard\Repositories\Tablda\UserCloudRepository;
use Vanguard\Services\Tablda\ImportService;

class SalesforceController extends Controller
{
    /**
     * @var SalesforceApiModule
     */
    protected $api;

    /**
     *
     */
    public function __construct()
    {
        $this->api = new SalesforceApiModule();
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
    public function objects(Request $request)
    {
        $cloud = $this->auth($request);
        $objects = $this->api->listObjects($cloud->id);
        return array_map(function ($object) {
            return [
                'label' => $object['label'],
                'name' => $object['name']
            ];
        }, $objects);
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function objectFields(Request $request)
    {
        $cloud = $this->auth($request);
        $fields = $this->api->objectFields($cloud->id, $request->object_id);
        $service = new ImportService();
        $headers = $service->makeHeaders(array_combine($fields, $fields), true);
        return [
            'headers' => $headers,
            'fields' => $fields,
            'name_converter' => collect($fields)->mapWithKeys(function ($field) {
                $hideCustom = Str::endsWith($field, '__c')
                    ? str_replace('__c', '', $field)
                    : $field . ' (sys)';
                return [$field => str_replace('_', ' ', $hideCustom)];
            }),
        ];
    }
}
