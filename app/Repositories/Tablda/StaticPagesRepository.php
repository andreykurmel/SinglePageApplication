<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Models\AppSetting;
use Vanguard\Models\StaticPage;
use Vanguard\Services\Tablda\HelperService;

class StaticPagesRepository
{
    protected $service;

    public function __construct(HelperService $service)
    {
        $this->service = $service;
    }

    /**
     * Get Static Page by id.
     *
     * @param int $id
     * @return mixed
     */
    public function getById($id, $user)
    {
        if ($user && in_array($user->role_id, [1, 3])) {
            return StaticPage::where('id', $id)->first();
        } else {
            return StaticPage::where('id', $id)->where('is_active', 1)->first();
        }
    }

    /**
     * @param $user
     * @return array
     */
    public function getAll($user): array
    {
        $orders = $this->allOrders();
        if (!$orders) {
            AppSetting::updateOrCreate(['key' => 'static_pages_tabs_orders'], [
                'key' => 'static_pages_tabs_orders',
                'val' => StaticPage::select('type')->distinct()->get()->implode('type', ',')
            ]);
            $orders = $this->allOrders();
        }

        $sql = StaticPage::orderBy('order');
        if (!$user || !in_array($user->role_id, [1, 3])) {
            $sql->where('is_active', 1);
        }
        $rows = $sql->get();
        $rows = $rows->groupBy('type');

        return [$rows, $orders];
    }

    /**
     * @return string[]|null
     */
    protected function allOrders()
    {
        $stg = AppSetting::where('key', '=', 'static_pages_tabs_orders')->first();
        return $stg && $stg->val ? explode(',', $stg->val) : null;
    }

    /**
     * Add Static Page.
     *
     * @param $data : [
     *  +type: string,
     *  +name: string,
     *  -url: string,
     *  -content: text,
     *  -embed_view: string,
     *  -parent_id: int,
     *  -is_folder: int,
     * ]
     * @return mixed
     */
    public function addPage(array $data)
    {
        $data['url'] = preg_replace('/[^\w\d ]+/i', '_', $data['name']);
        return StaticPage::create($this->service->delSystemFields($data));
    }

    /**
     * Update Static Page.
     *
     * @param $id
     * @param $data : [
     *  +type: string,
     *  +name: string,
     *  -url: string,
     *  -content: text,
     *  -embed_view: string,
     *  -parent_id: int,
     *  -is_folder: int,
     * ]
     * @return mixed
     */
    public function updatePage(int $id, array $data)
    {
        $data['url'] = preg_replace('/[^\w\d ]+/i', '_', $data['name']);
        StaticPage::where('id', $id)
            ->update($this->service->delSystemFields($data));
        return $data['url'];
    }

    /**
     * Delete Static Page.
     *
     * @param $id
     * @return mixed
     */
    public function deletePage(int $id)
    {
        return StaticPage::where('id', $id)
            ->delete();
    }

    /**
     * Move StaticPage.
     *
     * @param int $page_id
     * @param int|null $folder_id
     * @param int $position
     * @param string $type
     * @return mixed
     */
    public function movePage(int $page_id, $folder_id, int $position, string $type)
    {
        $res = StaticPage::where('id', '=', $page_id)
            ->update([
                'parent_id' => $folder_id,
                'order' => $position,
                'type' => $type,
            ]);
        $this->updateChildren([$page_id], ['type' => $type]);

        $idx = 0;
        $pages = StaticPage::where('parent_id', '=', $folder_id)
            ->where('type', '=', $type)
            ->where('id', '!=', $page_id)
            ->get();
        foreach ($pages as $page) {
            $idx++;
            if ($idx == $position) {
                $idx++;
            }
            $page->order = $idx;
            $page->type = $type;
            $page->save();
        }

        return $res;
    }

    /**
     * @param array $parents
     * @param array $updates
     * @return void
     */
    protected function updateChildren(array $parents, array $updates): void
    {
        $sql = StaticPage::whereIn('parent_id', $parents);
        if ((clone $sql)->count()) {
            (clone $sql)->update($updates);
            $this->updateChildren($sql->get()->pluck('id')->toArray(), $updates);
        }
    }

    /**
     * @param string $old
     * @param string $new
     * @return mixed
     */
    public function updateTabType(string $old, string $new)
    {
        return StaticPage::where('type', $old)
            ->update(['type' => $new]);
    }
}