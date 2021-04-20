<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Models\StaticPage;
use Vanguard\Policies\StaticPagePolicy;
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
    public function getById(int $id, $user) {
        if ($user && in_array($user->role_id, [1,3])) {
            return StaticPage::where('id', $id)->first();
        } else {
            return StaticPage::where('id', $id)->where('is_active', 1)->first();
        }
    }

    /**
     * Get All Static Pages.
     *
     * @return mixed
     */
    public function getAll($user) {
        if ($user && in_array($user->role_id, [1,3])) {
            return StaticPage::orderBy('order')->get();
        } else {
            return StaticPage::where('is_active', 1)->orderBy('order')->get();
        }
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
    public function addPage(array $data) {
        $data['url'] = preg_replace('/[^\w\d ]+/i', '_', $data['name']);
        return StaticPage::create( $this->service->delSystemFields($data) );
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
    public function updatePage(int $id, array $data) {
        $data['url'] = preg_replace('/[^\w\d ]+/i', '_', $data['name']);
        StaticPage::where('id', $id)
            ->update( $this->service->delSystemFields($data) );
        return $data['url'];
    }

    /**
     * Delete Static Page.
     *
     * @param $id
     * @return mixed
     */
    public function deletePage(int $id) {
        return StaticPage::where('id', $id)
            ->delete();
    }

    /**
     * Move StaticPage.
     *
     * @param int $page_id
     * @param int $folder_id
     * @param int $position
     * @param string $type
     * @return mixed
     */
    public function movePage(int $page_id, int $folder_id, int $position, string $type) {
        $res = StaticPage::where('id', $page_id)
            ->update([
                'parent_id' => $folder_id,
                'order' => $position,
            ]);

        $idx = 0;
        $pages = StaticPage::where('parent_id', $folder_id)
            ->where('type', $type)
            ->where('id', '!=', $page_id)
            ->get();
        foreach ($pages as $page) {
            $idx++;
            if ($idx == $position) {
                $idx++;
            }
            $page->order = $idx;
            $page->save();
        }

        return $res;
    }
}