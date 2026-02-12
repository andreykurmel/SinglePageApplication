<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\StaticPages\PageAddRequest;
use Vanguard\Http\Requests\Tablda\StaticPages\PageDeleteRequest;
use Vanguard\Http\Requests\Tablda\StaticPages\PageMoveRequest;
use Vanguard\Http\Requests\Tablda\StaticPages\PageUpdateRequest;
use Vanguard\Models\StaticPage;
use Vanguard\Services\Tablda\BladeVariablesService;
use Vanguard\Services\Tablda\StaticPagesService;

class StaticPageController extends Controller
{
    private $pagesService;
    private $variablesService;

    /**
     * StaticPageController constructor.
     * @param StaticPagesService $pagesService
     * @param BladeVariablesService $variablesService
     */
    public function __construct(
        StaticPagesService    $pagesService,
        BladeVariablesService $variablesService
    )
    {
        $this->pagesService = $pagesService;
        $this->variablesService = $variablesService;
    }

    /**
     * 'Get Started' Page.
     * @return Factory|View
     */
    public function getStartedPage()
    {
        $vars = $this->variablesService->getVariables();
        $vars['pages'] = $this->pagesService->getAllAndGroup();
        $vars['route_group'] = 'getstarted';
        $vars['lightweight'] = true;
        $vars['no_settings'] = true;
        return view('tablda.statics.getstarted', $vars);
    }

    /**
     * Get Static Page.
     *
     * @param $url
     * @param $type
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function getPage(Request $request, $type, $url)
    {
        $pages_tree = $this->pagesService->getAllAndGroup();
        $selected_page = $this->pagesService->getByTypeAndUrl($pages_tree[$type] ?? [], '/' . $request->path());
        if ($selected_page) {
            $vars = $this->variablesService->getVariables();
            $vars['pages'] = $pages_tree;
            $vars['selected_type'] = $type;
            $vars['selected_page'] = $selected_page;
            $vars['route_group'] = 'getstarted';
            $vars['lightweight'] = true;
            $vars['no_settings'] = true;
            return view('tablda.statics.pages', $vars);
        } else {
            return redirect(route('getstarted'));
        }
    }

    /**
     * Add Static Page.
     *
     * @param PageAddRequest $request
     * @return mixed
     */
    public function addPage(PageAddRequest $request)
    {
        $this->authorize('edit', StaticPage::class);
        return $this->pagesService->addPage($request->fields, $request->page_url);
    }

    /**
     * Update Static Page.
     *
     * @param PageUpdateRequest $request
     * @return mixed
     */
    public function updatePage(PageUpdateRequest $request)
    {
        $this->authorize('edit', StaticPage::class);
        return $this->pagesService->updatePage($request->page_id, $request->fields);
    }

    /**
     * Delete Static Page.
     *
     * @param PageDeleteRequest $request
     * @return mixed
     */
    public function deletePage(PageDeleteRequest $request)
    {
        $this->authorize('edit', StaticPage::class);
        return $this->pagesService->deletePage($request->page_id);
    }

    /**
     * Delete Static Page.
     *
     * @param PageMoveRequest $request
     * @return mixed
     */
    public function movePage(PageMoveRequest $request)
    {
        $this->authorize('edit', StaticPage::class);
        return $this->pagesService->movePage($request->page_id, $request->folder_id, $request->position, $request->type);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateTabType(Request $request)
    {
        $this->authorize('edit', StaticPage::class);
        return $this->pagesService->updateTabType($request->old_type ?: '', $request->new_type ?: '');
    }
}
