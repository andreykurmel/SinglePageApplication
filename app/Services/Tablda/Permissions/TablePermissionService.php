<?php

namespace Vanguard\Services\Tablda\Permissions;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\DataSetPermissions\TablePermissionColumn;
use Vanguard\Models\DataSetPermissions\TablePermissionRow;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\Permissions\TableColGroupRepository;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\User;

class TablePermissionService
{
    protected $service;
    protected $permissionRepository;
    protected $tableFieldRepository;
    protected $colGroupRepository;

    /**
     * TablePermissionService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->permissionRepository = new TablePermissionRepository();
        $this->tableFieldRepository = new TableFieldRepository();
        $this->colGroupRepository = new TableColGroupRepository();
    }

    /**
     * Get Permission.
     *
     * @param $table_permission_id
     * @return mixed
     */
    public function getPermission($table_permission_id)
    {
        return $this->permissionRepository->getPermission($table_permission_id);
    }

    /**
     * Change Default Value for Field for provided Table Permission.
     *
     * @param Int $table_permission_id
     * @param Int $user_group_id (nullable)
     * @param Int $table_field_id
     * @param $def_val
     * @return int|mixed
     */
    public function defaultField(Int $table_permission_id, $user_group_id, Int $table_field_id, $def_val) {
        if ($this->permissionRepository->getDefField($table_permission_id, $user_group_id, $table_field_id)) {
            return $this->permissionRepository->updateDefField($table_permission_id, $user_group_id, $table_field_id, $def_val);
        } else {
            return $def_val ?
                $this->permissionRepository->insertDefField([
                    'table_permission_id' => $table_permission_id,
                    'user_group_id' => $user_group_id,
                    'table_field_id' => $table_field_id,
                    'default' => $def_val
                ])
                :
                1;
        }
    }

    /**
     * Add Visitor Permission (needs to be added to each table).
     *
     * @param $table_id - int
     * @param $can_edit - int
     * @param $except_columns - array
     * @return void
     */
    public function addSystemRights($table_id, $can_edit = 0, $except_columns = []) {
        $standard_system_rights = [
            1 => $this->service->getVisitorRightName(),
        ];

        foreach ($standard_system_rights as $type => $right) {
            $this->addSysRight($table_id, $type, $right, $can_edit, $except_columns);
        }
    }

    /**
     * Add Visitor Permission (needs to be added to each table).
     *
     * @param $table_id - int
     * @param $type - int [1 = Visitor; 2 = ViaFolder]
     * @param $right_name - string
     * @param $can_edit - int
     * @param $except_columns - array
     * @return void
     */
    public function addSysRight($table_id, $type, $right_name, $can_edit = 0, $except_columns = []) {
        if ($this->permissionRepository->getSysPermission($table_id, $type)) {
            return;
        }

        $table_permission = $this->permissionRepository->addPermission([
            'is_system' => $type,
            'table_id' => $table_id,
            'name' => $right_name
        ]);
        $tableFields = $this->tableFieldRepository->getFieldsForTable($table_id);

        $table_col_group = $this->colGroupRepository->addColGroup([
            'is_system' => $type,
            'table_id' => $table_id,
            'name' => $right_name
        ]);
        $this->colGroupRepository->checkAllColFields($table_col_group, $tableFields, $except_columns);

        $this->permissionRepository->updateTableColPermission($table_permission->id, $table_col_group->id, 1, $can_edit);
    }

    /**
     * Notify users from DCR row.
     *
     * @param Table $table
     * @param int $dcr_id
     * @param array $submitted_rows
     */
    public function sendRequestEmails(Table $table, int $dcr_id, array $submitted_rows) {
        $tbServ = new TableDataService();
        $formula_parser = new FormulaEvaluatorRepository($table, $table->user_id, true);
        $dcr = $this->permissionRepository->getPermission($dcr_id);
        if ($dcr) {
            //$dcr->load('_link_limits');
            foreach ($submitted_rows as $row) {
                $pref = HelperService::dcrPref($dcr, $row);
                $rel = '_'.$pref;

                $email_field = $dcr ? $dcr[$rel.'email_field'] : null;
                $email_field = $email_field ? $email_field->field : '';
                $static_emails = $dcr ? $dcr[$pref.'email_field_static'] : '';
                //$addresse_field = $dcr ? $dcr[$rel.'addressee_field'] : null;

                $recipients = array_merge(
                    $this->service->parseRecipients($row[$email_field] ?? ''),
                    $this->service->parseRecipients($static_emails ?? '')
                );
                if ($recipients) {

                    $fields_arr = $this->service->getFieldsArrayForNotification($table, $dcr[$rel.'email_col_group']);

                    //$toname = $addresse_field ? $row[$addresse_field->field] ?? '' : '';
                    $toname = $dcr[$pref.'addressee_txt'] ? $formula_parser->formulaReplaceVars($row, $dcr[$pref.'addressee_txt'], true) : '';
                    $subject = $dcr[$pref.'email_subject'] ? $formula_parser->formulaReplaceVars($row, $dcr[$pref.'email_subject'], true) : 'Thanks for submission';

                    $usr = auth()->user();
                    $user_str = $usr ? ($usr->first_name ? $usr->first_name . ' ' . $usr->last_name : $usr->username) : '';
                    $greeting = $usr || $toname
                        ? 'Hello, '.($toname ?: $user_str).'!'
                        : 'Hello!';

                    $rows_arr = $this->service->prepareRowVals($table, $row);

                    $params = [
                        'from.name' => config('app.name').' DCR',
                        'from.account' => 'noreply',
                        'subject' =>  $subject,
                        'to.address' => $recipients,
                        'to.name' => $toname,
                    ];
                    $data = [
                        'greeting' => $greeting,
                        'replace_main_message' => $dcr[$pref.'email_message'] ? $formula_parser->formulaReplaceVars($row, $dcr[$pref.'email_message'], true) : '',
                        'table_arr' => $table->getAttributes(),
                        'fields_arr' => $fields_arr,
                        'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                        'all_rows_arr' => $rows_arr,
                        'changed_fields' => [],
                        'alert_arr' => ['mail_format' => $dcr[$pref.'email_format'] ?: 'table'],
                        'type' => 'added',
                    ];

                    //Add Linked Records (which are present in DCR limits).
                    $field_ids = $table->_fields->pluck('id')->toArray();
                    $links_objs = (new TableFieldLinkRepository())->getRortForFields($field_ids);
                    if ($links_objs && $links_objs->count()) { //if ($dcr->_link_limits && $dcr->_link_limits->count()) {
                        $links_objs->load(['_ref_condition' => function($rc) {
                            $rc->with('_ref_table');
                        }]);
                        $link_tables = [];
                        foreach ($links_objs as $i=>$link) {
                            $reftable = $link->_ref_condition->_ref_table;
                            [$lnk_rows_count, $lnk_rows] = $tbServ->getFieldRows($reftable, $link->toArray(), $row, 1, 10);
                            $lnk_fields = $this->service->getFieldsArrayForNotification( $reftable );
                            $link_tables[] = [
                                'name' => $reftable->name,
                                'fields' => $lnk_fields,
                                'all_rows' => $lnk_rows,
                                'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                            ];
                        }
                        $data['link_tables'] = $link_tables;
                    }

                    Mail::to($recipients)->send( new TabldaMail('tablda.emails.row_changed', $data, $params) );

                }
            }
        }
    }
}