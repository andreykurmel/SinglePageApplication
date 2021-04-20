<?php

namespace Vanguard\AppsModules\StimWid;

use Ramsey\Uuid\Uuid;
use Vanguard\Models\Correspondences\StimAppView;
use Vanguard\Services\Tablda\HelperService;

class StimAppViewRepository
{
    protected $service;

    /**
     * TableViewRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAppView($id)
    {
        return StimAppView::where('id', '=', $id)->first();
    }

    /**
     * @param $hash
     * @return mixed
     */
    public function getByHash($hash)
    {
        return StimAppView::where('hash', '=', $hash)->where('is_active', '=', 1)->first();
    }

    /**
     * @param array $data
     * @return StimAppView
     */
    public function insertAppView(array $data)
    {
        $data['name'] = preg_replace('/[^\w\d_\s]/i', '', $data['name']);
        $data['hash'] = Uuid::uuid4();
        $data['user_id'] = auth()->id();
        $data['side_top'] = $data['side_top'] ?? 'na';
        $data['side_left'] = $data['side_left'] ?? 'na';
        $data['side_right'] = $data['side_right'] ?? 'na';
        return StimAppView::create($this->service->delSystemFields($data));
    }

    /**
     * @param $view_id
     * @param array $data
     * @return StimAppView
     */
    public function updateAppView($view_id, Array $data)
    {
        if (!empty($data['name'])) {
            $data['name'] = preg_replace('/[^\w\d_\s]/i', '', $data['name']);
        }
        return StimAppView::where('id', '=', $view_id)->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_view_id
     * @return mixed
     */
    public function deleteAppView($table_view_id)
    {
        return StimAppView::where('id', '=', $table_view_id)->delete();
    }
}