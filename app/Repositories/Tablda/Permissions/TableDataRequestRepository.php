<?php

namespace Vanguard\Repositories\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Dcr\TableDataRequestColumn;
use Vanguard\Models\Dcr\TableDataRequestDefaultField;
use Vanguard\Modules\QRGenerator;
use Vanguard\Repositories\Tablda\FileRepository;
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
     * Check availability of request address.
     *
     * @param $table_name
     * @param $link
     * @param $id
     * @return mixed
     */
    public function checkAddress($table_name, $link, $id = 0)
    {
        return TableDataRequest::where('id', '!=', $id)
            ->whereHas('_table', function ($t) use ($table_name) {
                $t->where('name', $table_name);
            })
            ->where('user_link', '=', $link)
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

        foreach ($data as $key => $val) {
            if (in_array($key, ['active', 'one_per_submission', 'dcr_form_shadow', 'dcr_form_transparency'])) {
                $data[$key] = 0;
            }
            if (in_array($key, ['dcr_title_line_top', 'dcr_title_line_bot', 'dcr_form_line_thick', 'dcr_sec_line_thick'])) {
                $data[$key] = 1;
            }
            if (in_array($key, ['dcr_form_line_radius'])) {
                $data[$key] = 10;
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
     * @param int $table_id
     * @param int|null $permis_id
     * @return Builder
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
        ]);
        return $sql;
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
     * @return array
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
        return TableDataRequest::where('id', $table_data_requests_id)
            ->update($this->service->delSystemFields($data));
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
        return DcrLinkedTable::where('id', '=', $linked_id)->first();
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
}