<?php

namespace Vanguard\Http\Controllers\Web\Tablda;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\PostLinkRequest;
use Vanguard\Models\Pages\Pages;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\PagesService;

class PagesController extends Controller
{
    protected $pagesService;
    protected $service;

    /**
     * TableReferController constructor.
     */
    public function __construct()
    {
        $this->pagesService = new PagesService();
        $this->service = new HelperService();
    }

    /**
     * @param Request $request
     * @return Pages[]
     * @throws AuthorizationException
     */
    public function getSettings(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id) ?? new Pages();
        $url_parts = parse_url($_SERVER['HTTP_REFERER']);
        $hash = str_replace('/dashboard/', '', $url_parts['path']);
        if ($page->user_id && $page->share_hash != $hash) { //if not 'public' and not View
            $this->authorize('isOwner', [Pages::class, $page]);
        }
        $page->_is_owner = $page->user_id === auth()->id();
        return [
            'pages' => $page,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function insert(Request $request)
    {
        $page = $this->pagesService->addPage([
            'user_id' => auth()->id(),
            'name' => $request->name,
        ]);
        $this->pagesService->connectToFolder($page->id, $request->folder_id, $request->structure);
        return $this->service->getPageObjectForMenuTree($page, $request->folder_path);
    }

    /**
     * @param Request $request
     * @return Pages|null
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        $this->pagesService->updatePage($page->id, array_merge($request->fields, ['user_id' => auth()->id()]));
        return $this->pagesService->getPage($page->id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        return ['status' => $this->pagesService->deletePage($page->id)];
    }

    /**
     * @param Request $request
     * @return array
     * @throws AuthorizationException
     */
    public function contentPositions(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        return [
            'status' => $this->pagesService->realignContent($page, $request->contents)
        ];
    }

    /**
     * @param Request $request
     * @return Pages|null
     * @throws AuthorizationException
     */
    public function insertContent(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        $this->pagesService->addPageContent(array_merge($request->fields, ['page_id' => $page->id]));
        return $this->pagesService->getPage($page->id);
    }

    /**
     * @param Request $request
     * @return Pages|null
     * @throws AuthorizationException
     */
    public function updateContent(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        $this->pagesService->updatePageContent($page->id, $request->page_content_id, array_merge($request->fields, ['page_id' => $page->id]));
        return $this->pagesService->getPage($page->id);
    }

    /**
     * @param Request $request
     * @return Pages|null
     * @throws AuthorizationException
     */
    public function deleteContent(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        $this->pagesService->deletePageContent($page->id, $request->page_content_id);
        return $this->pagesService->getPage($page->id);
    }

    /**
     * Toggle page in favorite for user.
     *
     * @param Request $request
     * @return array
     */
    public function favorite(Request $request)
    {
        return [
            'state' => $this->pagesService->favoriteToggle($request->page_id, auth()->id(), $request->favorite, $request->parent_id ?: null)
        ];
    }

    /**
     * Link table to folder.
     *
     * @param PostLinkRequest $request
     * @return array
     */
    public function createLink(PostLinkRequest $request)
    {
        return $this->pagesService->createLink($request->object_id, $request->folder_id, $request->structure, $request->path, auth()->id());
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function deleteLink(Request $request)
    {
        $page = $this->pagesService->getPage($request->page_id);
        $this->authorize('isOwner', [Pages::class, $page]);
        return $this->pagesService->deleteLink($request->page_id, $request->link_id);
    }
}
