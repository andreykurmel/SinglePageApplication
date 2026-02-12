<?php


namespace Vanguard\Services\Tablda;


use Vanguard\Models\StaticPage;
use Vanguard\Repositories\Tablda\StaticPagesRepository;

class StaticPagesService
{
    protected $pagesRepository;

    /**
     * StaticPagesService constructor.
     * @param StaticPagesRepository $pagesRepository
     */
    public function __construct(StaticPagesRepository $pagesRepository)
    {
        $this->pagesRepository = $pagesRepository;
    }

    /**
     * Get Static Page by url and type.
     *
     * @param array $type_tree
     * @param string $url
     * @return object
     */
    public function getByTypeAndUrl(array $type_tree, string $url)
    {
        $url = urldecode($url);
        $id = $this->findInTree($url, $type_tree);
        return $this->pagesRepository->getById($id, auth()->user());
    }

    /**
     * Find Page in StaticTree.
     *
     * @param string $url_path
     * @param array $type_tree
     * @return int|null
     */
    public function findInTree(string $url_path, array $type_tree)
    {
        $res_page = null;
        foreach ($type_tree as $item) {
            if (!$res_page) {
                $res_page = $this->findInTree($url_path, $item['children']);
            }

            if (!$res_page) {
                $compare_name = $item['a_attr']['href'] ?? '';
                if ($url_path == $compare_name) {
                    $res_page = $item['li_attr']['data-id'] ?? null;
                }
            }
        }
        return $res_page;
    }

    /**
     * Get All Static Pages and group to Three groups.
     *
     * @return mixed
     */
    public function getAllAndGroup()
    {
        [$pages, $types] = $this->pagesRepository->getAll(auth()->user());

        $all = [];
        foreach ($types as $type) {
            $all[$type] = $this->buildPagesTree($pages[$type] ?? [], '/getstarted/' . $type . '/');
        }
        return $all;
    }

    /**
     * Build tree for js-tree from StaticPages.
     *
     * @param $pages
     * @param string $path
     * @param int $parentId
     * @return array
     */
    public function buildPagesTree($pages, string $path = '/', int $parentId = 0)
    {
        $branch = [];

        foreach ($pages as $page) {
            if ($page->parent_id == $parentId) {

                $sub_path = $path . ($page->is_folder ? $page->url . '/' : '');
                $children = $this->buildPagesTree($pages, $sub_path, $page->id);
                $branch[] = $this->PageTreeObject($page, $children, $sub_path);

            }
        }

        return $branch;
    }

    /**
     * Get Object for js-tree.
     *
     * @param $page
     * @param array $children
     * @param string $path
     * @return array
     */
    private function PageTreeObject($page, array $children, string $path)
    {
        return [
            'text' => $page->name,
            'icon' => $this->getIcon($page),
            'state' => [
                'opened' => true
            ],
            'children' => $children,
            'li_attr' => [
                'data-id' => $page->id,
                'data-object' => $page,
            ],
            'a_attr' => [
                'href' => $page->link_address
                    ?: $path . ($page->is_folder ? '' : $page->url),
                'class' => ($page->is_active ? '' : 'tree_not_active'),
            ]
        ];
    }

    /**
     * Get link type.
     *
     * @param $page
     * @return string
     */
    private function getIcon($page)
    {
        if ($page->is_folder) {
            return 'fa fa-folder-open';
        }
        switch ($page->node_icon) {
            case 'YouTube':
                $link = 'fab fa-youtube';
                break;
            case 'Page':
                $link = 'far fa-file-alt';
                break;
            case 'PowerPoint':
                $link = 'fas fa-file-powerpoint';
                break;
            case 'PDF':
                $link = 'fas fa-file-pdf';
                break;
            case 'File':
                $link = 'far fa-copy';
                break;
            default:
                $link = 'fa fa-link';
        }
        return $link;
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
     * @param string $url_folder
     * @return array
     */
    public function addPage(array $data, string $url_folder)
    {
        $page = $this->pagesRepository->addPage($data);
        $page = $this->pagesRepository->getById($page->id, auth()->user());//get with all fields
        $url_folder .= ($page->is_folder ? $page->url . '/' : '');
        return $this->PageTreeObject($page, [], $url_folder);
    }

    /**
     * Get Static Page by id.
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id)
    {
        return $this->pagesRepository->getById($id, auth()->user());
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
        return $this->pagesRepository->updatePage($id, $data);
    }

    /**
     * Delete Static Page.
     *
     * @param $id
     * @return mixed
     */
    public function deletePage(int $id)
    {
        return $this->pagesRepository->deletePage($id);
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
        return $this->pagesRepository->movePage($page_id, $folder_id, $position, $type);
    }

    /**
     * @param string $old
     * @param string $new
     * @return mixed
     */
    public function updateTabType(string $old, string $new)
    {
        $new = preg_replace('[^\w\d_]', '', $new);
        if (StaticPage::where('type', $new)->count()) {
            throw new \Exception("{$new} is already present!", 1);
        }
        if (!$new) {
            throw new \Exception("New type is incorrect/empty!", 1);
        }
        return $this->pagesRepository->updateTabType($old, $new);
    }
}