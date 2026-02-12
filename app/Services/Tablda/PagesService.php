<?php

namespace Vanguard\Services\Tablda;

use Exception;
use Vanguard\Models\Folder\Folder2Entity;
use Vanguard\Models\Pages\PageContents;
use Vanguard\Models\Pages\Pages;
use Vanguard\Repositories\Tablda\PagesRepository;

class PagesService
{
    /**
     * @var PagesRepository
     */
    protected $repo;
    /**
     * @var HelperService
     */
    protected $service;

    /**
     * TableRepository constructor.
     */
    public function __construct()
    {
        $this->repo = new PagesRepository();
        $this->service = new HelperService();
    }

    /**
     * @param $pages_id
     * @return Pages|null
     */
    public function getPage($pages_id)
    {
        return $this->repo->getPage($pages_id);
    }

    /**
     * @param array $data
     * @return Pages
     */
    public function addPage(array $data)
    {
        return $this->repo->addPage($data);
    }

    /**
     * @param $pages_id
     * @param array $data
     * @return bool|int
     */
    public function updatePage($pages_id, array $data)
    {
        return $this->repo->updatePage($pages_id, $data);
    }

    /**
     * @param $pages_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deletePage($pages_id)
    {
        return $this->repo->deletePage($pages_id);
    }

    /**
     * @param array $data
     * @return PageContents
     */
    public function addPageContent(array $data)
    {
        return $this->repo->addPageContent($data);
    }

    /**
     * @param $pages_id
     * @param $page_conts_id
     * @param array $data
     * @return bool|int
     */
    public function updatePageContent($pages_id, $page_conts_id, array $data)
    {
        return $this->repo->updatePageContent($pages_id, $page_conts_id, $data);
    }

    /**
     * @param $pages_id
     * @param $page_conts_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deletePageContent($pages_id, $page_conts_id)
    {
        return $this->repo->deletePageContent($pages_id, $page_conts_id);
    }

    /**
     * @param int $page_id
     * @param ?int $folder_id
     * @param string $structure
     * @return Folder2Entity
     */
    public function connectToFolder(int $page_id, ?int $folder_id, string $structure)
    {
        return $this->repo->connectToFolder($page_id, $folder_id, $structure);
    }

    /**
     * @param Pages $pages
     * @param array $contents
     * @return bool
     */
    public function realignContent(Pages $pages, array $contents)
    {
        foreach ($contents as $cnt) {
            $this->repo->realignContent($pages->id, $cnt['id'], $cnt['grid_position']);
        }
        return true;
    }

    /**
     * Toggle page in favorite for user.
     *
     * @param int $page_id
     * @param int $user_id
     * @param bool $favorite
     * @param int|null $parent_id
     * @return bool
     */
    public function favoriteToggle(int $page_id, int $user_id, bool $favorite, ?int $parent_id)
    {
        return $this->repo->favoriteToggle($page_id, $user_id, $favorite, $parent_id);
    }

    /**
     * Link table to folder.
     *
     * @param $page_id
     * @param $folder_id
     * @param string $structure
     * @param string $folder_path
     * @param int $user_id
     * @return array
     */
    public function createLink($page_id, $folder_id, $structure = 'private', $folder_path, $user_id)
    {
        //dd($page_id, $folder_id, $structure, $folder_path, $user_id);;
        $link = $this->repo->linkPage($page_id, $folder_id, $user_id, $structure);

        $page = $this->repo->getPage($page_id);
        $page->link = $link;

        $path = ($folder_path ?: config('app.url') . "/data/");
        return $this->service->getPageObjectForMenuTree($page, $path, $folder_id);
    }

    /**
     * @param $page_id
     * @param $link_id
     * @return mixed
     */
    public function deleteLink($page_id, $link_id)
    {
        return $this->repo->deleteLink($page_id, $link_id);
    }

    /**
     * @param string $hash
     * @return Pages|null
     */
    public function viewExists(string $hash): ?Pages
    {
        return $this->repo->getByShareHash($hash);
    }
}