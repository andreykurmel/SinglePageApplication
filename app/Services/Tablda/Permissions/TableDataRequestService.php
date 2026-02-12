<?php

namespace Vanguard\Services\Tablda\Permissions;


use Exception;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Models\Dcr\DcrLinkedTable;
use Vanguard\Models\Dcr\TableDataRequest;
use Vanguard\Models\Table\Table;
use Vanguard\Repositories\Tablda\AutomationHistoryRepository;
use Vanguard\Repositories\Tablda\Permissions\TableDataRequestRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableFieldLinkRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Services\Tablda\TableDataService;

class TableDataRequestService
{
    protected $service;
    protected $dataReqRepo;

    /**
     * TableDataRequestService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->dataReqRepo = new TableDataRequestRepository();
    }

    /**
     * Get DataRequest.
     *
     * @param $table_data_request_id
     * @return mixed
     */
    public function getDataRequest($table_data_request_id)
    {
        return $this->dataReqRepo->getDataRequest($table_data_request_id);
    }

    /**
     * Change Default Value for Field for provided Table DataRequest.
     *
     * @param int $table_data_request_id
     * @param int $table_field_id
     * @param $def_val
     * @return int|mixed
     */
    public function defaultField(int $table_data_request_id, int $table_field_id, $def_val)
    {
        if ($this->dataReqRepo->getDefField($table_data_request_id, $table_field_id)) {
            return $this->dataReqRepo->updateDefField($table_data_request_id, $table_field_id, $def_val);
        } else {
            return $def_val ?
                $this->dataReqRepo->insertDefField([
                    'table_data_requests_id' => $table_data_request_id,
                    'table_field_id' => $table_field_id,
                    'default' => $def_val
                ])
                :
                1;
        }
    }

    /**
     * Notify users from DCR row.
     *
     * @param Table $table
     * @param int $dcr_id
     * @param array $html_row
     */
    public function sendRequestEmails(Table $table, int $dcr_id, array $html_row)
    {
        $tbServ = new TableDataService();
        $formula_parser = new FormulaEvaluatorRepository($table, $table->user_id, true);
        $dcr = $this->dataReqRepo->getDataRequest($dcr_id);
        $pref = $dcr ? HelperService::dcrPref($dcr, $html_row) : '';
        if ($dcr && !empty($dcr[$pref . 'active_notif'])) {
            $rel = '_' . $pref;

            $email_field = $dcr ? $dcr[$rel . 'email_field'] : null;
            $email_field = $email_field ? $email_field->field : '';
            $static_emails = $dcr ? $dcr[$pref . 'email_field_static'] : '';
            $cc_email_field = $dcr ? $dcr[$rel . 'cc_email_field'] : null;
            $cc_email_field = $cc_email_field ? $cc_email_field->field : '';
            $cc_static_emails = $dcr ? $dcr[$pref . 'cc_email_field_static'] : '';
            $bcc_email_field = $dcr ? $dcr[$rel . 'bcc_email_field'] : null;
            $bcc_email_field = $bcc_email_field ? $bcc_email_field->field : '';
            $bcc_static_emails = $dcr ? $dcr[$pref . 'bcc_email_field_static'] : '';
            //$addresse_field = $dcr ? $dcr[$rel.'addressee_field'] : null;

            $recipients = ['to' => [], 'cc' => [], 'bcc' => []];

            $recipients['to'] = $this->service->addRecipientsEmails($recipients['to'], $html_row[$email_field] ?? '');
            $recipients['to'] = $this->service->addRecipientsEmails($recipients['to'], $static_emails ?? '', true);
            $recipients['cc'] = $this->service->addRecipientsEmails($recipients['cc'], $html_row[$cc_email_field] ?? '');
            $recipients['cc'] = $this->service->addRecipientsEmails($recipients['cc'], $cc_static_emails ?? '', true);
            $recipients['bcc'] = $this->service->addRecipientsEmails($recipients['bcc'], $html_row[$bcc_email_field] ?? '');
            $recipients['bcc'] = $this->service->addRecipientsEmails($recipients['bcc'], $bcc_static_emails ?? '', true);

            if ($recipients['to']) {
                $automationHistory = new AutomationHistoryRepository($dcr->user_id, $dcr->table_id);
                $automationHistory->startTimer();

                $fields_arr = $this->service->getFieldsArrayForNotification($table, $dcr[$rel . 'email_col_group']);

                //$toname = $addresse_field ? $html_row[$addresse_field->field] ?? '' : '';
                $toname = $dcr[$pref . 'addressee_txt'] ? $formula_parser->formulaReplaceVars($html_row, $dcr[$pref . 'addressee_txt']) : '';
                $subject = $dcr[$pref . 'email_subject'] ? $formula_parser->formulaReplaceVars($html_row, $dcr[$pref . 'email_subject']) : 'Thanks for submission';

                $usr = auth()->user();
                $user_str = $usr ? ($usr->first_name ? $usr->first_name . ' ' . $usr->last_name : $usr->username) : '';
                $greeting = $usr || $toname
                    ? ($toname ?: $user_str) . ':'
                    : 'Hello, there:';

                $rows_arr = $this->service->prepareRowVals($table, $html_row);

                $params = [
                    'from.name' => config('app.name') . ' DCR',
                    'from.account' => 'noreply',
                    'subject' => $subject,
                    'to.address' => $recipients['to'],
                    'to.name' => $toname,
                ];
                $data = [
                    'greeting' => $greeting,
                    'replace_main_message' => $dcr[$pref . 'email_message'] ? $formula_parser->formulaReplaceVars($html_row, $dcr[$pref . 'email_message']) : '',
                    'table_arr' => $table->getAttributes(),
                    'fields_arr' => $fields_arr,
                    'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                    'all_rows_arr' => $rows_arr,
                    'changed_fields' => [],
                    'alert_arr' => ['mail_format' => $dcr[$pref . 'email_format'] ?: 'table'],
                    'type' => 'added',
                    'custom_salutation' => $dcr[$pref . 'signature_txt'],
                ];

                //Add Linked Records (which are present in DCR limits).
                $field_ids = $table->_fields->pluck('id')->toArray();
                $links_objs = (new TableFieldLinkRepository())->getRortForFields($field_ids);
                $notifs_linked = $dcr[$rel . 'linked_notifs'];
                if ($links_objs && $links_objs->count() && $notifs_linked->count()) {
                    $links_objs->load(['_ref_condition' => function ($rc) {
                        $rc->with('_ref_table');
                    }]);
                    $link_tables = [];
                    foreach ($links_objs as $link) {
                        $activeLink = $notifs_linked->where('link_id', '=', $link->id)->where('is_active', '=', 1)->first();
                        if (! $activeLink || ! $link->_ref_condition) {
                            continue;
                        }
                        $reftable = $link->_ref_condition->_ref_table;
                        [$lnk_rows_count, $lnk_rows] = $tbServ->getFieldRows($reftable, $link->toArray(), $html_row, 1, 10);
                        $lnk_fields = $this->service->getFieldsArrayForNotification($reftable, $activeLink->_col_group);
                        $link_tables[] = [
                            'name' => $reftable->name,
                            'fields' => $lnk_fields,
                            'all_rows' => $lnk_rows,
                            'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                            'mail_format' => $activeLink->related_format ?: 'table',
                        ];
                    }
                    $data['link_tables'] = $link_tables;
                }

                $mailer = new EmailWithSettings('dcr_notification', $recipients['to'], $recipients['cc'] ?? [], $recipients['bcc'] ?? []);
                $mailer->send($params, $data);

                $automationHistory->stopTimerAndSave('DCR', $dcr->name, 'Notification', 'Email');
//                return $mailer->getMailable($params, $data);
            }
        }
    }

    /**
     * @param TableDataRequest $dcr
     * @param array $dcr_linked_rows ['table_id' => [array of rows], ...]
     * @param array $dcr_parent_row
     * @throws Exception
     */
    public function storeLinkedRows(TableDataRequest $dcr, array $dcr_linked_rows, array $dcr_parent_row)
    {
        $dcr->load([
            '_dcr_linked_tables' => function ($q) {
                $q->with('_linked_table');
            }
        ]);

        foreach ($dcr->_dcr_linked_tables as $dcr_linked) {
            $table = $dcr_linked->_linked_table;
            $linked_rows = $dcr_linked_rows[$table->id] ?? [];
            $this->removeForgottenLinkedRecords($dcr_linked, $linked_rows, $dcr_parent_row);
            foreach ($linked_rows as $link_row) {
                $ready_row = $this->fillLinkedRow($dcr_linked, $link_row, $dcr_parent_row);
                $this->insertOrUpdateLinkedRecord($dcr_linked, $ready_row);
            }
        }
    }

    /**
     * @param DcrLinkedTable $dcrLinkedTable
     * @param array $all_linked_rows
     * @param array $dcr_parent_row
     */
    protected function removeForgottenLinkedRecords(DcrLinkedTable $dcrLinkedTable, array $all_linked_rows, array $dcr_parent_row)
    {
        $table = $dcrLinkedTable->_linked_table;
        $data = new TableDataService();
        $request_id = HelperService::dcr_id_linked_row($dcrLinkedTable->table_request_id, ($dcr_parent_row['id'] ?? null));
        $ids_for_dcr = $data->getRowIdsForField($table, 'request_id', $request_id);
        $ids_for_dcr = $ids_for_dcr->diff(collect($all_linked_rows)->pluck('id'));
        $data->deleteSelectedRows($table, $ids_for_dcr->toArray(), true);
    }

    /**
     * @param DcrLinkedTable $linked_table
     * @param array $linked_row
     * @param array $dcr_parent_row
     * @return array
     */
    public function fillLinkedRow(DcrLinkedTable $linked_table, array $linked_row, array $dcr_parent_row): array
    {
        $request_id = HelperService::dcr_id_linked_row($linked_table->table_request_id, ($dcr_parent_row['id'] ?? null));
        $linked_row['request_id'] = $request_id;
        $ref_cond = $linked_table->_passed_ref_cond;
        if ($ref_cond && $ref_cond->ref_table_id == $linked_table->linked_table_id) {
            $ref_cond->load(['_items' => function ($i) {
                $i->with('_field', '_compared_field');
            }]);

            foreach ($ref_cond->_items as $ref_item) {
                if ($ref_item->_compared_field) {
                    $linked_row[$ref_item->_compared_field->field] = $ref_item->_field
                        ? ($dcr_parent_row[$ref_item->_field->field] ?? null)
                        : $ref_item->compared_value;
                }
            }
        }
        return $linked_row;
    }

    /**
     * @param DcrLinkedTable $dcrLinkedTable
     * @param array $row
     * @throws Exception
     */
    protected function insertOrUpdateLinkedRecord(DcrLinkedTable $dcrLinkedTable, array $row)
    {
        $table = $dcrLinkedTable->_linked_table;
        if ($row['id']) {
            (new TableDataService())->updateRow($table, $row['id'], $row, null);
        } else {
            (new TableDataService())->insertRow($table, $row);
        }
    }

    /**
     * @param int $linked_id
     * @return DcrLinkedTable
     */
    public function getLinkedTable(int $linked_id): DcrLinkedTable
    {
        return $this->dataReqRepo->getLinkedTable($linked_id);
    }
}