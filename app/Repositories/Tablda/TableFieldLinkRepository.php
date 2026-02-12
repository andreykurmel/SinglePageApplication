<?php

namespace Vanguard\Repositories\Tablda;


use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Vanguard\Classes\EriCollections;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\DataSetPermissions\TableRefCondition;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\TableFieldLink;
use Vanguard\Models\Table\TableFieldLinkColumn;
use Vanguard\Models\Table\TableFieldLinkDaLoading;
use Vanguard\Models\Table\TableFieldLinkEriField;
use Vanguard\Models\Table\TableFieldLinkEriFieldConversion;
use Vanguard\Models\Table\TableFieldLinkEriPart;
use Vanguard\Models\Table\TableFieldLinkEriPartVariable;
use Vanguard\Models\Table\TableFieldLinkEriTable;
use Vanguard\Models\Table\TableFieldLinkParam;
use Vanguard\Models\Table\TableFieldLinkToDcr;
use Vanguard\Models\Table\TableStatuse;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableFieldLinkRepository
{
    protected $service;

    /**
     * TableFieldLinkRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param Table $table
     * @return TableFieldLink|null
     */
    public function findExportLink(Table $table)
    {
        $papp = CorrespApp::where('code', '=', 'general_json_export')->first();
        return TableFieldLink::whereIn('table_field_id', $table->_fields()->pluck('id'))
            ->where('table_app_id', $papp->id)
            ->first();
    }

    /**
     * Get Group of Rows.
     *
     * @param $link_id
     * @return TableFieldLink
     */
    public function getLink($link_id)
    {
        return TableFieldLink::where('id', '=', $link_id)->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLink|null
     */
    public function getLinkByParam($link_param_id)
    {
        return TableFieldLink::whereHas('_params', function ($q) use ($link_param_id) {
            $q->where('id', '=', $link_param_id);
        })->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLink|null
     */
    public function getLinkByEriTable($link_param_id)
    {
        return TableFieldLink::whereHas('_eri_tables', function ($q) use ($link_param_id) {
            $q->where('id', '=', $link_param_id);
        })->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLinkEriField|null
     */
    public function getTableLinkEriField($link_param_id)
    {
        return TableFieldLinkEriField::where('id', '=', $link_param_id)->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLinkDaLoading|null
     */
    public function getTableLinkDaLoading($link_param_id)
    {
        return TableFieldLinkDaLoading::where('id', '=', $link_param_id)->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLinkEriFieldConversion|null
     */
    public function getTableLinkEriFieldConversion($link_param_id)
    {
        return TableFieldLinkEriFieldConversion::where('id', '=', $link_param_id)->first();
    }

    /**
     * @param $link_param_id
     * @return mixed
     */
    public function getLinkByEriPart($link_param_id)
    {
        return TableFieldLink::whereHas('_eri_parts', function ($q) use ($link_param_id) {
            $q->where('id', '=', $link_param_id);
        })->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLinkEriPart
     */
    public function getTableLinkEriPart($link_param_id)
    {
        return TableFieldLinkEriPart::where('id', '=', $link_param_id)->first();
    }

    /**
     * @param $link_param_id
     * @return TableFieldLinkEriPartVariable
     */
    public function getTableLinkEriPartVariable($link_param_id)
    {
        return TableFieldLinkEriPartVariable::where('id', '=', $link_param_id)->first();
    }

    /**
     * @param $link_dcr_id
     * @return mixed
     */
    public function getLinkByToDcr($link_dcr_id)
    {
        return TableFieldLink::whereHas('_to_dcrs', function ($q) use ($link_dcr_id) {
            $q->where('id', '=', $link_dcr_id);
        })->first();
    }

    /**
     * @param $link_col_id
     * @return mixed
     */
    public function getLinkByColumn($link_col_id)
    {
        return TableFieldLink::whereHas('_columns', function ($q) use ($link_col_id) {
            $q->where('id', '=', $link_col_id);
        })->first();
    }

    /**
     * @param array $fields_ids
     * @return mixed
     */
    public function getRortForFields(array $fields_ids)
    {
        return TableFieldLink::whereIn('table_field_id', $fields_ids)
            ->whereIn('link_display', ['Popup','RorT'])
            ->get();
    }

    /**
     * @param int $fields_id
     * @return TableFieldLink|null
     */
    public function getWebForField(int $fields_id)
    {
        return TableFieldLink::where('table_field_id', '=', $fields_id)
            ->where('link_type', '=', 'Web')
            ->first();
    }

    /**
     * @param TableFieldLink $link
     * @param TableRefCondition $reversedCondition
     * @return array
     * @throws \Exception
     */
    public function reversedLink(TableFieldLink $link, TableRefCondition $reversedCondition): array
    {
        $item = $reversedCondition->_items->where('item_type', 'P2S')->first();
        if (!$item) {
            throw new \Exception('Reversed RefCondition without P2S items for Reversed Link!', 1);
        }

        $attributes = $link->attributesToArray();
        foreach ($attributes as $key => $value) {
            if (Str::endsWith($key, '_id')) {
                $attributes[$key] = null;
            }
        }

        return array_merge($attributes, [
            'name' => 'R_' . $link->name,
            'table_field_id' => $item->table_field_id,
            'table_ref_condition_id' => $reversedCondition->id,
        ]);
    }

    /**
     * @param TableFieldLink $link
     * @return void
     */
    public function fillDrilledFields(TableFieldLink $link): void
    {
        $table = null;
        if (!$table && $link->_ref_condition) {
            $table = $link->_ref_condition->_ref_table;
        }
        if (!$table && $link->_field) {
            $table = $link->_field->_table;
        }

        if ($table) {
            $link->update([
                'link_export_drilled_fields' => $table->_fields->pluck('id')->toJson()
            ]);
        }
    }

    /**
     * Add Group of Rows.
     *
     * @param $data
     * [
     *  +table_field_id: int,
     *  +link_type: string,
     *  +icon: string,
     *  -tooltip: string,
     *  -table_ref_condition_id: int,
     *  -listing_field_id: int,
     *  -address_field_id: int,
     * ]
     * @return mixed
     */
    public function addLink(Table $table, $data)
    {
        //activate Links for TableField.
        $data['popup_can_table'] = 0;
        $data['popup_can_list'] = 1;
        $data['popup_can_board'] = 0;
        $data['popup_display'] = 'Listing';
        $data['inline_style'] = 'simple';
        $data['inline_in_vert_table'] = 1;
        $data['inline_width'] = 'field';
        $data['inline_hide_tab'] = 1;
        $data['inline_hide_boundary'] = 1;
        $data['inline_hide_padding'] = 1;
        $data['listing_panel_status'] = 'hidden';
        $data['listing_header_wi'] = 0.35;
        $data['listing_rows_min_width'] = 70;
        $data['listing_rows_width'] = 250;
        $data['link_preview_show_flds'] = 1;
        $data['link_pos'] = $data['link_pos'] ?? 'before';
        $data['history_fld_id'] = $data['table_field_id'] ?? null;
        $data['max_height_in_vert_table'] = $data['max_height_in_vert_table'] ?? 400;
        $data['pop_width_px'] = $data['pop_width_px'] ?? 768;
        $data['pop_width_px_min'] = $data['pop_width_px_min'] ?? 200;
        $data['pop_height'] = $data['pop_height'] ?? 80;
        $data['pop_height_min'] = $data['pop_height_min'] ?? 20;
        $data['smart_select_data_range'] = $data['smart_select_data_range'] ?? '0';
        $data['row_order'] = TableFieldLink::query()->max('id') + 1;

        $link = TableFieldLink::create( array_merge(
            $this->service->delSystemFields($data),
                $this->service->getModified(),
                $this->service->getCreated()
        ) );

        $new_ref = $data['table_ref_condition_id'] ?? null;
        if ($link->table_ref_condition_id != $new_ref) {
            $this->fillDrilledFields($link);
        }

        return $this->loadLink([$link->id]);
    }

    /**
     * Update Group of Rows.
     *
     * @param TableFieldLink $link
     * @param $data
     * [
     *  +table_field_id: int,
     *  -link_type: string,
     *  -icon: string,
     *  -tooltip: string,
     *  -table_ref_condition_id: int,
     *  -listing_field_id: int,
     *  -address_field_id: int,
     * ]
     * @return TableFieldLink
     */
    public function updateLink(TableFieldLink $link, $data)
    {
        if (isset($data['table_app_id']) && $data['table_app_id'] != $link->table_app_id) {
            $this->copyLinkParamsFromApp($link, $data['table_app_id']);
        }

        $new_ref = $data['table_ref_condition_id'] ?? null;
        if ($link->table_ref_condition_id != $new_ref) {
            $this->fillLinkColumns($link->id, $new_ref);
            $this->fillDrilledFields($link);
        }

        $link->update( array_merge($this->service->delSystemFields($data), $this->service->getModified()) );

        return $this->loadLink([$link->id]);
    }

    /**
     * Delete Group of Rows.
     *
     * @param int $link_id
     * @return mixed
     */
    public function deleteLink($link_id)
    {
        $link = TableFieldLink::where('id', $link_id)->first();
        return ['status' => $link->delete()];
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function loadLink(array $ids)
    {
        $links = TableFieldLink::whereIn('id', $ids)
            ->with([
                '_params',
                '_columns',
                '_eri_tables' => function ($q) {
                    $q->with(['_eri_fields' => function ($in) {
                        $in->with(['_conversions']);
                        $in->orderBy('row_order');
                    }]);
                    $q->orderBy('row_order');
                },
                '_eri_parts' => function ($q) {
                    $q->with(['_part_variables' => function ($in) {
                        $in->orderBy('row_order');
                    }]);
                    $q->orderBy('row_order');
                },
                '_link_app_correspondences',
            ])
            ->get();

        return $links->count() <= 1 ? $links->first() : $links;
    }

    /**
     * Copy Params from App settings.
     *
     * @param TableFieldLink $link
     * @param $new_app_id
     */
    protected function copyLinkParamsFromApp(TableFieldLink $link, $new_app_id)
    {
        $link->_params()->delete();

        $params = CorrespField::where('correspondence_app_id', $new_app_id)
            ->whereHas('_table', function ($q) {
                $q->where('app_table', 'CALLING_URL_PARAMETERS');
            })
            ->get();

        $mass = [];
        foreach ($params as $p) {
            $mass[] = [
                'table_field_link_id' => $link->id,
                'param' => $p->app_field,
            ];
        }

        TableFieldLinkParam::insert($mass);
    }

    /**
     * Add Link Param.
     *
     * @param $data
     * [
     *  +table_field_link_id: int,
     *  +param: string,
     *  -value: string,
     *  -column_id: int,
     * ]
     * @return mixed
     */
    public function addLinkParam($data)
    {
        return TableFieldLinkParam::create( $this->service->delSystemFields($data) );
    }

    /**
     * Update Link Param.
     *
     * @param int $link_id
     * @param $data
     * [
     *  +table_field_link_id: int,
     *  +param: string,
     *  -value: string,
     *  -column_id: int,
     * ]
     * @return array
     */
    public function updateLinkParam($link_id, $data)
    {
        return TableFieldLinkParam::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Link Param.
     *
     * @param int $link_id
     * @return mixed
     */
    public function deleteLinkParam($link_id)
    {
        return TableFieldLinkParam::where('id', '=', $link_id)->delete();
    }

    /**
     * Add Link To Dcr Limit.
     *
     * @param $data
     * @return mixed
     */
    public function addLinkToDcr($data)
    {
        $data['status'] = 1;
        $data['add_limit'] = $data['add_limit'] ?? 1;
        return TableFieldLinkToDcr::create( $this->service->delSystemFields($data) );
    }

    /**
     * Update Link To Dcr Limit.
     *
     * @param int $link_id
     * @param $data
     * @return array
     */
    public function updateLinkToDcr($link_id, $data)
    {
        return TableFieldLinkToDcr::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * Delete Link To Dcr Limit.
     *
     * @param int $link_id
     * @return mixed
     */
    public function deleteLinkToDcr($link_id)
    {
        return TableFieldLinkToDcr::where('id', '=', $link_id)->delete();
    }

    /**
     * @param int $link_id
     * @param int|null $ref_id
     * @return void
     * @throws \Exception
     */
    public function fillLinkColumns(int $link_id, int $ref_id = null)
    {
        $ref = (new TableRefConditionRepository())->getRefCondition($ref_id);
        $table = $ref ? $ref->_ref_table : null;
        if ($table && $table->_fields->count() != TableFieldLinkColumn::where('table_link_id', '=', $link_id)->count()) {
            foreach ($table->_fields as $field) {
                $this->createOrUpdateLinkColumn($link_id, $field->id, $field->field, 1, 1);
            }
        }
    }

    /**
     * @param int $link_id
     * @param int $field_id
     * @param string $field_db
     * @param int|null $in_popup
     * @param int|null $in_inline
     * @return void
     */
    public function createOrUpdateLinkColumn(int $link_id, int $field_id, string $field_db, int $in_popup = null, int $in_inline = null): void
    {
        $updater = [
            'table_link_id' => $link_id,
            'field_id' => $field_id,
            'field_db' => $field_db,
        ];
        if (! is_null($in_popup)) {
            $updater['in_popup_display'] = $in_popup;
        }
        if (! is_null($in_inline)) {
            $updater['in_inline_display'] = $in_inline;
        }

        TableFieldLinkColumn::updateOrCreate([
            'table_link_id' => $link_id,
            'field_id' => $field_id,
        ], $updater);
    }

    /**
     * @param int $link_id
     * @param array $fields_objects
     * @return void
     * @throws \Exception
     */
    public function setLinkColumns(int $link_id, array $fields_objects): void
    {
        foreach ($fields_objects as $object) {
            if (!empty($object['id']) && !empty($object['field'])) {
                $this->createOrUpdateLinkColumn(
                    $link_id,
                    $object['id'],
                    $object['field'],
                    $object['in_popup_display'],
                    $object['in_inline_display']
                );
            }
        }
    }

    /**
     * @param TableFieldLink $link
     * @param string $field_key
     * @return void
     */
    public function massSyncLinkColumns(TableFieldLink $link, string $field_key): void
    {
        $refCond = $link->_ref_condition;
        $refTable = $refCond ? $refCond->_ref_table : null;
        if ($refTable) {
            $status = TableStatuse::where('table_id', $refTable->id)
                ->where('user_id', $refTable->user_id)
                ->first();

            $hiddenColumns = $status && $status->status_data
                ? json_decode($status->status_data, true)
                : [];
            $hiddenColumns = $hiddenColumns['hidden_columns'] ?? [];

            foreach ($refTable->_fields as $field) {
                if ($field_key == 'in_popup_display') {
                    $this->createOrUpdateLinkColumn(
                        $link->id,
                        $field->id,
                        $field->field,
                        in_array($field->field, $hiddenColumns) ? 0 : 1,
                        null
                    );
                }
                if ($field_key == 'in_inline_display') {
                    $this->createOrUpdateLinkColumn(
                        $link->id,
                        $field->id,
                        $field->field,
                        null,
                        in_array($field->field, $hiddenColumns) ? 0 : 1
                    );
                }
            }
        }
    }

    /**
     * @param int $link_id
     * @param int $field_id
     * @return bool|int|null
     * @throws \Exception
     */
    public function deleteLinkColumn(int $link_id, int $field_id)
    {
        return TableFieldLinkColumn::where('table_link_id', '=', $link_id)
            ->where('field_id', '=', $field_id)
            ->delete();
    }

    /**
     * @param $ref_cond_id
     * @return mixed
     */
    public function syncRefCond($ref_cond_id)
    {
        return TableFieldLink::where('table_ref_condition_id', '=', $ref_cond_id)->update([
            'listing_field_id' => null,
            'address_field_id' => null,
        ]);
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriTable
     */
    public function addLinkEriTable($data)
    {
        $data['row_order'] = $data['row_order'] ?? TableFieldLinkEriTable::query()
                ->where('table_link_id', '=', $data['table_link_id'])
                ->count() + 1;
        $tb = TableFieldLinkEriTable::create( $this->service->delSystemFields($data) );

        $fields = (new TableFieldRepository())->getFieldsWithHeaders($tb->_eri_table, auth()->id(), true);
        foreach ($fields as $fld) {
            if (! in_array($fld->field, $this->service->system_fields)) {
                $this->addLinkEriField([
                    'table_link_eri_id' => $tb->id,
                    'eri_variable' => '',
                    'eri_field_id' => $fld->id,
                    'is_active' => 1,
                    'row_order' => $fld->order,
                ]);
            }
        }

        $tb->load('_eri_fields');
        return $tb;
    }

    /**
     * @param $link_id
     * @param $data
     * @return bool
     */
    public function updateLinkEriTable($link_id, $data)
    {
        return TableFieldLinkEriTable::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteLinkEriTable($link_id)
    {
        return TableFieldLinkEriTable::where('id', '=', $link_id)->delete();
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriField
     */
    public function addLinkEriField($data)
    {
        $data['eri_variable'] = $data['eri_variable'] ?? '';
        $data['eri_master_field_id'] = $data['eri_master_field_id'] ?? 0;
        $data['row_order'] = $data['row_order'] ?? TableFieldLinkEriField::query()
                ->where('table_link_eri_id', '=', $data['table_link_eri_id'])
                ->count() + 1;
        return TableFieldLinkEriField::create( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @param $data
     * @return bool
     */
    public function updateLinkEriField($link_id, $data)
    {
        $data['eri_variable'] = $data['eri_variable'] ?? '';
        $data['eri_master_field_id'] = $data['eri_master_field_id'] ?? 0;
        return TableFieldLinkEriField::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteLinkEriField($link_id)
    {
        return TableFieldLinkEriField::where('id', '=', $link_id)->delete();
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriField
     */
    public function addLinkEriFieldConversion($data)
    {
        return TableFieldLinkEriFieldConversion::create( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @param $data
     * @return bool
     */
    public function updateLinkEriFieldConversion($link_id, $data)
    {
        return TableFieldLinkEriFieldConversion::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteLinkEriFieldConversion($link_id)
    {
        return TableFieldLinkEriFieldConversion::where('id', '=', $link_id)->delete();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function eriPartArray(array $data): array
    {
        $data['part'] = $data['part'] ?? '';
        $data['type'] = $data['type'] ?? '';
        $data['section_q_identifier'] = $data['section_q_identifier'] ?? '';
        $data['section_r_identifier'] = $data['section_r_identifier'] ?? '';
        $data['row_order'] = $data['row_order'] ?? TableFieldLinkEriPart::query()
                ->where('table_link_id', '=', $data['table_link_id'])
                ->count() + 1;
        return $data;
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriPart
     */
    public function addLinkEriPart($data)
    {
        $data = $this->eriPartArray($data);
        $prt = TableFieldLinkEriPart::create( $this->service->delSystemFields($data) );

        $prt->load('_part_variables');
        return $prt;
    }

    /**
     * @param $link_id
     * @param $data
     * @return bool
     */
    public function updateLinkEriPart($link_id, $data)
    {
        $data = $this->eriPartArray($data);
        return TableFieldLinkEriPart::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteLinkEriPart($link_id)
    {
        return TableFieldLinkEriPart::where('id', '=', $link_id)->delete();
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriPartVariable
     */
    public function addLinkEriPartVariable($data)
    {
        $data['variable_name'] = $data['variable_name'] ?? '';
        $data['var_notes'] = $data['var_notes'] ?? '';
        $data['row_order'] = $data['row_order'] ?? TableFieldLinkEriPartVariable::query()
            ->where('table_link_eri_part_id', '=', $data['table_link_eri_part_id'])
            ->count() + 1;
        return TableFieldLinkEriPartVariable::create( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @param $data
     * @return bool
     */
    public function updateLinkEriPartVariable($link_id, $data)
    {
        $data['variable_name'] = $data['variable_name'] ?? '';
        $data['var_notes'] = $data['var_notes'] ?? '';
        return TableFieldLinkEriPartVariable::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteLinkEriPartVariable($link_id)
    {
        return TableFieldLinkEriPartVariable::where('id', '=', $link_id)->delete();
    }

    /**
     * @param TableFieldLinkEriPart $part
     * @return TableFieldLinkEriPartVariable[]
     */
    public function fillAllEriPartVariables(TableFieldLinkEriPart $part)
    {
        $variables = EriCollections::allVariables($part->part);

        foreach ($variables as $variable) {
            $this->addLinkEriPartVariable([
                'table_link_eri_part_id' => $part->id,
                'variable_name' => $variable,
            ]);
        }

        $part->load('_part_variables');
        return $part->_part_variables;
    }

    /**
     * @param TableFieldLinkEriPart $part
     * @param string $variables
     * @return TableFieldLinkEriPartVariable[]
     */
    public function pasteEriPartVariables(TableFieldLinkEriPart $part, string $variables)
    {
        $values = preg_split('/\r\n|\r|\n|;|,/', $variables);
        foreach ($values as $v) {
            $v = trim($v);
            $this->addLinkEriPartVariable([
                'table_link_eri_part_id' => $part->id,
                'variable_name' => $v,
            ]);
        }

        $part->load('_part_variables');
        return $part->_part_variables;
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|TableFieldLinkEriPart
     */
    public function addLinkDaLoading($data)
    {
        return TableFieldLinkDaLoading::create( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @param $data
     * @return bool
     */
    public function updateLinkDaLoading($link_id, $data)
    {
        return TableFieldLinkDaLoading::where('id', '=', $link_id)
            ->update( $this->service->delSystemFields($data) );
    }

    /**
     * @param $link_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteLinkDaLoading($link_id)
    {
        return TableFieldLinkDaLoading::where('id', '=', $link_id)->delete();
    }
}