<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\CondFormat\CondFormatAddRequest;
use Vanguard\Http\Requests\Tablda\CondFormat\CondFormatDeleteRequest;
use Vanguard\Http\Requests\Tablda\CondFormat\CondFormats2TablePermissionsRequest;
use Vanguard\Http\Requests\Tablda\CondFormat\CondFormatUpdateRequest;
use Vanguard\Models\DataSetPermissions\CondFormat;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\Permissions\CondFormatsRepository;
use Vanguard\Services\Tablda\TableService;

class TableCondFormatController extends Controller
{
    private $tableService;
    private $condFormatsRepository;

    /**
     * TableCondFormatController constructor.
     *
     * @param TableService $tableService
     * @param CondFormatsRepository $condFormatsRepository
     */
    public function __construct(TableService $tableService, CondFormatsRepository $condFormatsRepository)
    {
        $this->tableService = $tableService;
        $this->condFormatsRepository = $condFormatsRepository;
    }

    /**
     * Add Conditional Format
     *
     * @param CondFormatAddRequest $request
     * @return mixed
     */
    public function insertCondFormat(CondFormatAddRequest $request)
    {
        $table = $this->tableService->getTable($request->table_id);
        $this->authorize('load', [TableData::class, $table]);
        return $this->condFormatsRepository->addFormat(
            array_merge($request->fields, ['table_id' => $request->table_id, 'user_id' => auth()->id()])
        );
    }

    /**
     * Update Conditional Format
     *
     * @param CondFormatUpdateRequest $request
     * @return array
     */
    public function updateCondFormat(CondFormatUpdateRequest $request)
    {
        $cond_format = $this->condFormatsRepository->getFormat($request->cond_format_id);
        $table = $this->tableService->getTable($cond_format ? $cond_format->table_id : null);
        $this->authorize('load', [TableData::class, $table]);

        if (auth()->id() != $cond_format->user_id) {
            if ($cond_format->_table_permissions()->wherePivot('always_on', 1)->first()) {
                return response('Disabled by owner', 400);
            } else {
                $fields = collect($request->fields)->only(['status'])->toArray();
                return $this->condFormatsRepository->updateFormatSettings($cond_format->id, auth()->id(), $fields);
            }
        } else {
            return $this->condFormatsRepository->updateFormat($cond_format->id, $request->fields);
        }
    }

    /**
     * Delete Conditional Format
     *
     * @param CondFormatDeleteRequest $request
     * @return mixed
     */
    public function deleteCondFormat(CondFormatDeleteRequest $request)
    {
        $cond_format = $this->condFormatsRepository->getFormat($request->cond_format_id);

        $this->authorize('isOwner', [CondFormat::class, $cond_format]);

        return $this->condFormatsRepository->deleteFormat($cond_format->id);
    }

    /**
     * Add Field to Col Group
     *
     * @param CondFormats2TablePermissionsRequest $request
     * @return mixed
     */
    public function insertCondFormatRight(CondFormats2TablePermissionsRequest $request)
    {
        $cond_format = $this->condFormatsRepository->getFormat($request->cond_format_id);

        $this->authorize('isOwner', [CondFormat::class, $cond_format]);

        return $this->condFormatsRepository->addRightForCondFormat($cond_format, $request->table_permission_id);
    }

    /**
     * Add Field to Col Group
     *
     * @param CondFormats2TablePermissionsRequest $request
     * @return mixed
     */
    public function updateCondFormatRight(CondFormats2TablePermissionsRequest $request)
    {
        $cond_format = $this->condFormatsRepository->getFormat($request->cond_format_id);

        $this->authorize('isOwner', [CondFormat::class, $cond_format]);

        return $this->condFormatsRepository->updateRightForCondFormat(
            $cond_format, $request->table_permission_id, $request->always_on, $request->visible_shared
        );
    }

    /**
     * Delete Field from Col Group
     *
     * @param CondFormats2TablePermissionsRequest $request
     * @return mixed
     */
    public function deleteCondFormatRight(CondFormats2TablePermissionsRequest $request)
    {
        $cond_format = $this->condFormatsRepository->getFormat($request->cond_format_id);

        $this->authorize('isOwner', [CondFormat::class, $cond_format]);

        return $this->condFormatsRepository->deleteRightFromCondFormat($cond_format, $request->table_permission_id);
    }
}
