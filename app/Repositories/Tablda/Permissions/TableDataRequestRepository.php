<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\AppSetting;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Dcr\TableDataRequest2Fields;
use Vanguard\Models\Dcr\TableDataRequestColumn;
use Vanguard\Models\Dcr\TableDataRequestDefaultField;
use Vanguard\Models\Dcr\TableDataRequestRight;
use Vanguard\Models\Dcr\DcrNotifLinkedTable;
use Vanguard\Models\Table\Table;
use Vanguard\Modules\QRGenerator;
use Vanguard\Repositories\Tablda\FileRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Tablda\HelperService;

class TableDataRequestRepository
{
    protected $service;

    /**
     * TableDataRequestRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param $table_data_request_id
     * @return TableDataRequest
     */
    public function getDataRequest($table_data_request_id)
    {
        return TableDataRequest::where('id', '=', $table_data_request_id)->first();
    }

    /**
     * @param $hash
     * @return TableDataRequest
     */
    public function getByHash($hash)
    {
        return TableDataRequest::where('dcr_hash', '=', $hash)->first();
    }

    /**
     * @return Collection
     */
    public function getTemplates()
    {
        $ids = array_merge(HelperService::adminIds(), [auth()->id()]);//get from admin and current user.
        return TableDataRequest::where('is_template', '=', 1)
            ->whereHas('_table', function ($tb) use ($ids) {
                $tb->whereIn('user_id', $ids);
            })
            ->with('_table:id,user_id,db_name,name')
            ->get();
    }

    /**
     * @param $link
     * @param $id
     * @return int
     */
    public function checkAddress($link, $id = 0)
    {
        return TableDataRequest::where('id', '!=', $id)
            ->where('custom_url', '=', $link)
            ->count();
    }

    /**
     * Add DataRequest.
     *
     * @param $data
     * [
     *  +user_group_id: int,
     *  +table_id: int,
     * ]
     * @return mixed
     */
    public function addDataRequest($data)
    {
        $data['active'] = 1;
        $data['dcr_hash'] = Uuid::uuid4();
        $data['link_hash'] = Uuid::uuid4();
        $data['user_id'] = auth()->id();

        $data['dcr_signature_txt'] = $data['dcr_save_signature_txt'] = $data['dcr_upd_signature_txt'] = "Best.\nTablDA Team.";

        foreach ($data as $key => $val) {
            if (in_array($key, ['active', 'one_per_submission', 'dcr_form_shadow', 'dcr_form_transparency', 'download_pdf', 'download_png'])) {
                $data[$key] = 0;
            }
            if (in_array($key, ['dcr_title_line_top', 'dcr_title_line_bot', 'dcr_form_line_thick', 'dcr_sec_line_thick'])) {
                $data[$key] = 1;
            }
            if (in_array($key, ['dcr_form_line_radius'])) {
                $data[$key] = 10;
            }
            if (in_array($key, ['dcr_many_rows_width'])) {
                $data[$key] = 250;
            }
            if (in_array($key, ['dcr_email_format', 'dcr_save_email_format', 'dcr_upd_email_format'])) {
                switch ($val) {
                    case 'list':
                        $data[$key] = 'list';
                        break;
                    case 'vertical':
                        $data[$key] = 'vertical';
                        break;
                    default:
                        $data[$key] = 'table';
                        break;
                }
            }
            if (in_array($key, ['dcr_sec_bg_img_fit', 'dcr_title_bg_fit'])) {
                $data[$key] = 'Width';
            }
            if (in_array($key, ['dcr_form_line_type'])) {
                $data[$key] = 'line';
            }
            if (in_array($key, ['dcr_form_shadow_dir'])) {
                $data[$key] = 'BR';
            }
            if (in_array($key, ['dcr_sec_scroll_style'])) {
                $data[$key] = 'scroll';
            }
            if (in_array($key, ['dcr_sec_background_by', 'dcr_title_background_by'])) {
                $data[$key] = 'color';
            }
            if (in_array($key, ['dcr_form_width', 'dcr_title_width'])) {
                $data[$key] = 600;
            }
            if (in_array($key, ['dcr_form_line_height'])) {
                $data[$key] = 14;
            }
            if (in_array($key, ['dcr_form_font_size'])) {
                $data[$key] = 12;
            }
            if (in_array($key, ['dcr_confirm_msg'])) {
                $data[$key] = 'Thanks for your submission.';
            }
            if (in_array($key, ['dcr_save_confirm_msg'])) {
                $data[$key] = 'You may save the URL for future access of the saved record if data_request is granted.';
            }
            if (in_array($key, ['dcr_upd_confirm_msg'])) {
                $data[$key] = 'Your submission has been updated.';
            }
            if (in_array($key, ['dcr_title_bg_color', 'dcr_form_bg_color'])) {
                $data[$key] = '#ffffff';
            }
        }

        $data['qr_link'] = (new QRGenerator())->forDCR($data['link_hash'])->asPNG();

        $created = TableDataRequest::create($this->service->delSystemFields($data));
        return $this->loadWithRelations($created->table_id, $created->id)->first();
    }

    /**
     * @param Table $table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $table->load([
            '_table_requests' => function ($q) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $q->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $q->with('_dcr_linked_tables', '_data_request_columns', '_default_fields', '_fields_pivot', '_dcr_rights',
                    '_dcr_linked_notifs', '_dcr_upd_linked_notifs', '_dcr_save_linked_notifs');
            },
        ]);
        $this->attachCFtoDCRs($table->_table_requests);
    }

    /**
     * @param int $table_id
     * @param int|null $permis_id
     * @return Collection
     */
    public function loadWithRelations(int $table_id, int $permis_id = null)
    {
        $sql = TableDataRequest::where('table_id', '=', $table_id);
        if ($permis_id) {
            $sql->where('id', '=', $permis_id);
        }
        $sql->with([
            '_dcr_linked_tables',
            '_data_request_columns',
            '_default_fields',
            '_fields_pivot',
            '_dcr_rights',
        ]);
        $dcrs = $sql->get();

        $this->attachCFtoDCRs($dcrs);

        return $dcrs;
    }

    public function attachCFtoDCRs(Collection $dcrs): void
    {
        $linkTable = (new TableRepository())->getTableByDB('dcr_linked_tables');
        $repo = new TableDataRowsRepository();
        foreach ($dcrs as $dcr) {
            $repo->attachSpecialFields($dcr->_dcr_linked_tables, $linkTable, auth()->id(), ['conds']);
        }
    }

    /**
     * @param string $dcr_hash
     * @return TableDataRequest
     */
    public function dcrRelation(string $dcr_hash)
    {
        return TableDataRequest::where('dcr_hash', '=', $dcr_hash)
            ->with([
                '_column_groups' => function ($q) {
                    $q->with('_fields');
                },
                '_default_fields' => function ($q) {
                    $q->with('_field:id,table_id,field,input_type');
                },
            ])
            ->first();
    }

    /**
     * Update DataRequest
     *
     * @param int $table_data_requests_id
     * @param $data
     * @return TableDataRequest
     */
    public function updateDataRequest($table_data_requests_id, $data)
    {
        foreach ($data as $key => $val) {
            if (in_array($key, ['dcr_email_format', 'dcr_save_email_format', 'dcr_upd_email_format'])) {
                switch ($val) {
                    case 'list':
                        $data[$key] = 'list';
                        break;
                    case 'vertical':
                        $data[$key] = 'vertical';
                        break;
                    default:
                        $data[$key] = 'table';
                        break;
                }
            }
        }

        $old = TableDataRequest::where('id', $table_data_requests_id)->first();

        TableDataRequest::where('id', $table_data_requests_id)->update($this->service->delSystemFields($data));

        $new = TableDataRequest::where('id', $table_data_requests_id)->first();

        if ($old->name != $new->name || $old->dcr_qr_with_name != $new->dcr_qr_with_name) {
            $label = $new->dcr_qr_with_name ? $new->name : '';
            $new->update([
                'qr_link' => (new QRGenerator())->forDCR($new->link_hash, $label)->asPNG()
            ]);
        }

        if ($old->dcr_record_status_id != $new->dcr_record_status_id) {
            $oldF = $new->_table->_fields()->where('id', $old->dcr_record_status_id)->first();
            if ($oldF) {
                $oldF->update([
                    'input_type' => 'Input',
                    'ddl_id' => null,
                ]);
            }
            $newF = $new->_table->_fields()->where('id', $new->dcr_record_status_id)->first();
            if ($newF) {
                $newF->update([
                    'input_type' => 'S-Select',
                    'ddl_id' => AppSetting::where('key', 'dcr_status_sys_ddl_id')->first()->val,
                ]);
            }
        }

        return $new;
    }

    /**
     * Delete DataRequest
     *
     * @param int $table_data_requests_id
     * @return mixed
     */
    public function deleteDataRequest($table_data_requests_id, $table_id)
    {
        return TableDataRequest::where('id', $table_data_requests_id)->delete();
    }

    /**
     * Update or Create Table Column DataRequest in Table DataRequest.
     *
     * @param $table_data_request_id
     * @param $col_group_id
     * @param $view
     * @param $edit
     * @return int
     */
    public function updateTableColDataRequest($table_data_request_id, $col_group_id, $view = 0, $edit = 0)
    {
        $permis = TableDataRequestColumn::where('table_data_requests_id', $table_data_request_id)
            ->where('table_column_group_id', $col_group_id)
            ->first();

        if (!$permis) {
            $permis = TableDataRequestColumn::create([
                'table_data_requests_id' => $table_data_request_id,
                'table_column_group_id' => $col_group_id
            ]);
        }

        $permis->update(['view' => $view, 'edit' => $edit]);

        return $permis;
    }

    /**
     * Get Default Field for provided TableDataRequest.
     *
     * @param Int $table_data_request_id
     * @param Int $table_field_id
     * @return mixed
     */
    public function getDefField(int $table_data_request_id, int $table_field_id)
    {
        return TableDataRequestDefaultField::where('table_data_requests_id', '=', $table_data_request_id)
            ->where('table_field_id', '=', $table_field_id)
            ->first();
    }

    /**
     * Insert Default Field for provided TableDataRequest.
     *
     * @param $data
     * [
     *  +$table_data_request_id: int,
     *  +$table_field_id: int,
     *  +default: string,
     * ]
     * @return mixed
     */
    public function insertDefField($data)
    {
        return TableDataRequestDefaultField::create($this->service->delSystemFields($data));
    }

    /**
     * Update Default Field for provided TableDataRequest.
     *
     * @param Int $table_data_request_id
     * @param Int $table_field_id
     * @param $default
     * @return mixed
     */
    public function updateDefField(int $table_data_request_id, int $table_field_id, $default)
    {
        return TableDataRequestDefaultField::where('table_data_requests_id', '=', $table_data_request_id)
            ->where('table_field_id', '=', $table_field_id)
            ->update(['default' => $default]);
    }

    /**
     * Insert DCR File to TableDataRequest.
     *
     * @param TableDataRequest $tableDataRequest
     * @param string $field
     * @param UploadedFile $upload_file
     * @return string
     */
    public function insertDCRFile(TableDataRequest $tableDataRequest, string $field, UploadedFile $upload_file)
    {
        if ($tableDataRequest->{$field}) {
            Storage::delete($tableDataRequest->{$field});
        }

        $fileRepo = app()->make(FileRepository::class);
        $filePath = $fileRepo->getStorageTable($tableDataRequest->_table) . '/';
        $fileName = 'dcr_' . Uuid::uuid4();
        $upload_file->storeAs('public/' . $filePath, $fileName);

        $tableDataRequest->{$field} = $filePath . $fileName;
        $tableDataRequest->save();
        return $tableDataRequest->{$field};
    }

    /**
     * Delete DCR File to TableDataRequest.
     *
     * @param TableDataRequest $tableDataRequest
     * @param string $field
     * @return bool
     */
    public function deleteDCRFile(TableDataRequest $tableDataRequest, string $field)
    {
        Storage::delete($tableDataRequest->{$field});

        return $tableDataRequest->update([
            $field => null
        ]);
    }

    /**
     * @param int $dcr_id
     * @param array $fields
     * @return DcrLinkedTable
     */
    public function insertLinkedTable(int $dcr_id, array $fields): DcrLinkedTable
    {
        if (empty($fields['linked_permission_id'])) {
            $permis = (new TablePermissionRepository())->getSysPermission($fields['linked_table_id'], 1);
            $fields['linked_permission_id'] = $permis ? $permis->id : null;
        }
        $fields['table_request_id'] = $dcr_id;
        $fields['is_active'] = true;
        $fields['position'] = $fields['position'] ?? 'After';
        $fields['style'] = $fields['style'] ?? 'Default';
        $fields['default_display'] = $fields['default_display'] ?? 'Table';
        $fields['embd_table'] = 1;
        $fields['embd_listing'] = 1;
        $fields['embd_board'] = 1;
        $fields['listing_rows_width'] = 250;
        $fields['listing_rows_min_width'] = 70;
        $fields['max_height_inline_embd'] = $fields['max_height_inline_embd'] ?? 0;
        $fields['embd_table_align'] = $fields['embd_table_align'] ?? 'start';
        $fields['ctlg_columns_number'] = $fields['ctlg_columns_number'] ?? 3;
        $fields['ctlg_data_range'] = $fields['ctlg_data_range'] ?? '0';
        $fields['ctlg_display_option'] = $fields['ctlg_display_option'] ?? 'inline';
        return DcrLinkedTable::create($fields);
    }

    /**
     * @param int $linked_id
     * @param array $fields
     * @return DcrLinkedTable
     */
    public function updateLinkedTable(int $linked_id, array $fields): DcrLinkedTable
    {
        $filter = new DcrLinkedTable($fields);
        DcrLinkedTable::where('id', '=', $linked_id)->update($filter->toArray());
        return $this->getLinkedTable($linked_id);
    }

    /**
     * @param int $linked_id
     * @return DcrLinkedTable
     */
    public function getLinkedTable(int $linked_id): DcrLinkedTable
    {
        $dcr = DcrLinkedTable::where('id', '=', $linked_id)->first();

        $collection = collect([$dcr]);
        $linkTable = (new TableRepository())->getTableByDB('dcr_linked_tables');
        (new TableDataRowsRepository())->attachSpecialFields($collection, $linkTable, auth()->id(), ['conds']);

        return $collection->first();
    }

    /**
     * @param int $linked_id
     * @return DcrLinkedTable
     */
    public function getLinkedAsDcr(int $linked_id): DcrLinkedTable
    {
        return DcrLinkedTable::where('id', '=', $linked_id)
            ->with(['_linked_permission'])
            ->first();
    }

    /**
     * @param int $linked_id
     * @return bool
     */
    public function deleteLinkedTable(int $linked_id): bool
    {
        return DcrLinkedTable::where('id', '=', $linked_id)->delete();
    }

    /**
     * @param int $dcr_id
     * @param int $field_id
     * @return void
     */
    public function attachIfNeeded(int $dcr_id, int $field_id)
    {
        TableDataRequest2Fields::updateOrCreate([
            'table_data_requests_id' => $dcr_id,
            'table_field_id' => $field_id,
        ], [
            'table_data_requests_id' => $dcr_id,
            'table_field_id' => $field_id,
        ]);
    }

    /**
     * @param int $dcr_id
     * @param int $field_id
     * @param string $setting
     * @param $val
     * @return void
     */
    public function changePivotFld(int $dcr_id, int $field_id, string $setting, $val)
    {
        TableDataRequest2Fields::where('table_data_requests_id', '=', $dcr_id)
            ->where('table_field_id', '=', $field_id)
            ->update([
                $setting => $val
            ]);
    }

    /**
     * @param TableDataRequest $dcr
     * @return void
     * @throws \Exception
     */
    public function fillDcrUrl(TableDataRequest $dcr)
    {
        if ($dcr->_dcr_record_url_field) {
            $repo = new TableDataRepository();
            $tdq = new TableDataQuery($dcr->_table);
            $tdq->getQuery()
                ->whereNull($dcr->_dcr_record_url_field->field)
                ->orWhere($dcr->_dcr_record_url_field->field, '=', '')
                ->chunk(100, function ($rows) use ($dcr, $repo) {
                    foreach ($rows as $row) {
                        $upd = [
                            $dcr->_dcr_record_url_field->field => Uuid::uuid4()
                        ];
                        $repo->quickUpdate($dcr->_table, $row->id, $upd, false);
                    }
                });
        }
    }

    /**
     * @param TableDataRequest $dcr
     * @param int $table_permis_id
     * @param $can_edit
     * @return mixed
     */
    public function toggleDcrRight(TableDataRequest $dcr, int $table_permis_id, $can_edit)
    {
        $right = $dcr->_dcr_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableDataRequestRight::create([
                'table_data_request_id' => $dcr->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableDataRequest $dcr
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteDcrRight(TableDataRequest $dcr, int $table_permis_id)
    {
        return $dcr->_dcr_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }

    /**
     * @param $id
     * @return DcrNotifLinkedTable|null
     */
    public function getNotifLinkedTable($id)
    {
        return DcrNotifLinkedTable::where('id', $id)->first();
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return DcrNotifLinkedTable
     */
    public function insertNotifLinkedTable($alert_id, array $data)
    {
        $data['dcr_id'] = $alert_id;

        return DcrNotifLinkedTable::create($this->service->delSystemFields($data));
    }

    /**
     * @param $id
     * @param array $data
     * @return bool
     */
    public function updateNotifLinkedTable($id, array $data)
    {
        return DcrNotifLinkedTable::where('id', '=', $id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteNotifLinkedTable($id)
    {
        return DcrNotifLinkedTable::where('id', '=', $id)
            ->delete();
    }
}