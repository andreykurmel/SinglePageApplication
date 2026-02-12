<?php

namespace Vanguard\Repositories\Tablda;

use Exception;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\Folder\Folder2Entity;
use Vanguard\Models\Pages\PageContents;
use Vanguard\Models\Pages\Pages;
use Vanguard\Services\Tablda\HelperService;

class PagesRepository
{
    protected $service;

    /**
     * TableRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param $pages_id
     * @return Pages|null
     */
    public function getPage($pages_id)
    {
        $page = Pages::where('id', '=', $pages_id)
            ->with([
                '_contents' => function ($q) {
                    $q->with('_mrv');
                }
            ])
            ->first();
        foreach ($page->_contents as $content) {
            $content->grid_position = json_decode($content->grid_position);
        }
        return $page;
    }

    /**
     * @param array $data
     * @return Pages
     */
    public function addPage(array $data)
    {
        return Pages::create(array_merge($data, $this->service->getCreated(), $this->service->getModified()));
    }

    /**
     * @param $pages_id
     * @param array $data
     * @return bool|int
     */
    public function updatePage($pages_id, array $data)
    {
        return Pages::where('id', '=', $pages_id)->update($this->service->delSystemFields($data));
    }

    /**
     * @param $pages_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deletePage($pages_id)
    {
        return Pages::where('id', '=', $pages_id)->delete();
    }

    /**
     * @param array $data
     * @return PageContents
     */
    public function addPageContent(array $data)
    {
        $data['row_hash'] = Uuid::uuid4();
        $data['grid_position'] = $data['grid_position'] ?? json_encode(['wi'=>6, 'he'=>6, 'x'=>0, 'y'=>0]);
        return PageContents::create(array_merge($data, $this->service->getCreated(), $this->service->getModified()));
    }

    /**
     * @param $pages_id
     * @param $page_conts_id
     * @param array $data
     * @return bool|int
     */
    public function updatePageContent($pages_id, $page_conts_id, array $data)
    {
        return PageContents::where('id', '=', $page_conts_id)
            ->where('page_id', '=', $pages_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $pages_id
     * @param $page_conts_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deletePageContent($pages_id, $page_conts_id)
    {
        return PageContents::where('id', '=', $page_conts_id)
            ->where('page_id', '=', $pages_id)
            ->delete();
    }

    /**
     * @param int $page_id
     * @param ?int $folder_id
     * @param string $structure
     * @return Folder2Entity
     */
    public function connectToFolder(int $page_id, ?int $folder_id, string $structure)
    {
        if (!$folder_id) {
            (new UserRepository())->newMenutreeHash(auth()->id());
        }

        return Folder2Entity::create(array_merge([
            'entity_id' => $page_id,
            'entity_type' => 'page',
            'user_id' =>auth()->id(),
            'folder_id' =>$folder_id,
            'structure' => $structure,
        ], $this->service->getCreated(), $this->service->getModified()));
    }

    /**
     * @param int $page_id
     * @param int $contents_id
     * @param array $positions
     * @return bool|int
     */
    public function realignContent(int $page_id, int $contents_id, array $positions)
    {
        return PageContents::where('id', '=', $contents_id)
            ->where('page_id', '=', $page_id)
            ->update([
                'grid_position' => json_encode($positions)
            ]);
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
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        if ($favorite) {

            $link = Folder2Entity::where('entity_id', '=', $page_id)
                ->where('entity_type', '=', 'page')
                ->where('user_id', '=', $user_id)
                ->where('structure', '=', 'favorite')
                ->where('folder_id', '=', $parent_id ?: null)
                ->first();

            if (!$link) {
                $link = Folder2Entity::create(array_merge([
                    'entity_type' => 'page',
                    'entity_id' => $page_id,
                    'user_id' => $user_id,
                    'folder_id' => $parent_id ?: null,
                    'structure' => 'favorite'
                ], $this->service->getModified(), $this->service->getCreated()));
            }

            return $link;

        } else {

            return Folder2Entity::where('entity_id', '=', $page_id)
                ->where('entity_type', '=', 'page')
                ->where('user_id', '=', $user_id)
                ->where('structure', '=', 'favorite')
                ->where('folder_id', '=', $parent_id ?: null)
                ->delete();

        }
    }

    /**
     * @param $page_id
     * @param $folder_id
     * @param $user_id
     * @param $structure
     * @return mixed
     * @throws Exception
     */
    public function linkPage($page_id, $folder_id, $user_id, $structure = 'private')
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        return Folder2Entity::create(array_merge(
            [
                'entity_type' => 'page',
                'entity_id' => $page_id,
                'folder_id' => $folder_id,
                'user_id' => $user_id,
                'structure' => $structure,
            ],
            $this->service->getCreated(),
            $this->service->getModified()
        ));
    }

    /**
     * @param $page_id
     * @param $link_id
     * @return string
     */
    public function deleteLink($page_id, $link_id)
    {
        $res = Folder2Entity::where('entity_type', '=', 'page')
            ->where('entity_id', '=', $page_id)
            ->where('id', '=', $link_id)
            ->delete();
        return $res ? 'removed' : 'not found';
    }

    /**
     * @param string $hash
     * @return Pages|null
     */
    public function getByShareHash(string $hash): ?Pages
    {
        return Pages::where('share_hash', '=', $hash)->first();
    }
}