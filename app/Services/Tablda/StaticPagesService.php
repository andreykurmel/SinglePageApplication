<?php


namespace Vanguard\Services\Tablda;


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
    public function getByTypeAndUrl(array $type_tree, string $url) {
        $url = preg_replace('/%20/i', ' ', $url);
        return $this->findInTree($url, $type_tree);
    }

    /**
     * Find Page in StaticTree.
     *
     * @param string $url_path
     * @param array $type_tree
     * @return object
     */
    public function findInTree(string $url_path, array $type_tree) {
        $res_page = null;
        foreach ($type_tree as $item) {
            if (!$res_page) {
                $res_page = $this->findInTree($url_path, $item['children']);
            }

            if (!$res_page) {
                $compare_name = $item['a_attr']['href'] ?? '';
                if ($url_path == $compare_name) {
                    $res_page = $item['li_attr']['data-object'] ?? null;
                }
            }
        }
        return $res_page;
    }

    /**
     * Get Static Page by id.
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id) {
        return $this->pagesRepository->getById($id, auth()->user());
    }

    /**
     * Get All Static Pages and group to Three groups.
     *
     * @return mixed
     */
    public function getAllAndGroup() {
        $pages = $this->pagesRepository->getAll(auth()->user());
        $pages = $pages->groupBy('type');
        return [
            'introduction' => $this->buildPagesTree($pages['introduction'] ?? [], '/introduction/'),
            'tutorials' => $this->buildPagesTree($pages['tutorials'] ?? [], '/tutorials/'),
            'templates' => $this->buildPagesTree($pages['templates'] ?? [], '/templates/'),
            'applications' => $this->buildPagesTree($pages['applications'] ?? [], '/applications/')
        ];
    }

    /**
     * Build tree for js-tree from StaticPages.
     *
     * @param $pages
     * @param string $path
     * @param int $parentId
     * @return array
     */
    public function buildPagesTree($pages, string $path = '/', int $parentId = 0) {
        $branch = [];

        foreach ($pages as $page) {
            if ($page->parent_id == $parentId) {

                $sub_path = $path . ($page->is_folder ? $page->url.'/' : '');
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
    private function PageTreeObject($page, array $children, string $path) {
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
    private function getIcon($page) {
        if ($page->is_folder) {
            return 'fa fa-folder-open';
        }
        switch ($page->node_icon) {
            case 'YouTube': $link = 'fab fa-youtube';
                            break;
            case 'Page': $link = 'far fa-file-alt';
                            break;
            case 'PowerPoint': $link = 'fas fa-file-powerpoint';
                            break;
            case 'PDF': $link = 'fas fa-file-pdf';
                            break;
            case 'File': $link = 'far fa-copy';
                            break;
            default: $link = 'fa fa-link';
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
    public function addPage(array $data, string $url_folder) {
        $page = $this->pagesRepository->addPage($data);
        $page = $this->pagesRepository->getById($page->id, auth()->user());//get with all fields
        $url_folder .= ($page->is_folder ? $page->url.'/' : '');
        return $this->PageTreeObject($page, [], $url_folder);
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
        return $this->pagesRepository->updatePage($id, $data);
    }

    /**
     * Delete Static Page.
     *
     * @param $id
     * @return mixed
     */
    public function deletePage(int $id) {
        return $this->pagesRepository->deletePage($id);
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
        return $this->pagesRepository->movePage($page_id, $folder_id, $position, $type);
    }
}