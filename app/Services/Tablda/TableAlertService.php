<?php


namespace Vanguard\Services\Tablda;


use Illuminate\Support\Facades\Mail;
use function Sodium\compare;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;

class TableAlertService
{
    protected $alertsRepository;
    protected $userGroupRepository;
    protected $service;

    /**
     * TableAlertService constructor.
     */
    public function __construct()
    {
        $this->alertsRepository = new TableAlertsRepository();
        $this->userGroupRepository = new UserGroupRepository();
        $this->service = new HelperService();
    }

    /**
     * Get Table Alert.
     *
     * @param $table_alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function getAlert($table_alert_id)
    {
        return $this->alertsRepository->getAlert($table_alert_id);
    }

    /**
     * Insert Table Alert.
     *
     * @param array $data
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function insertAlert(Array $data)
    {
        return $this->alertsRepository->insertAlert($data);
    }

    /**
     * Update Table Alert.
     *
     * @param array $data
     * @param $alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function updateAlert($alert_id, Array $data)
    {
        if (!empty($data['recipients'])) {
            $recs = $this->getRecipients($data['recipients']);
            $data['recipients'] = implode(";\n", $recs);
        }
        return $this->alertsRepository->updateAlert($alert_id, $data);
    }

    /**
     * Delete Table Alert.
     *
     * @param int $table_alert_id
     * @return mixed
     */
    public function deleteAlert($table_alert_id)
    {
        return $this->alertsRepository->deleteAlert($table_alert_id);
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertAlertCond($alert_id, Array $data)
    {
        return $this->alertsRepository->insertAlertCond($alert_id, $data);
    }

    /**
     * @param $alert_id
     * @param $cond_id
     * @param array $data
     * @return TableAlert
     */
    public function updateAlertCond($alert_id, $cond_id, Array $data)
    {
        return $this->alertsRepository->updateAlertCond($alert_id, $cond_id, $data);
    }

    /**
     * @param $alert_id
     * @param $cond_id
     * @return TableAlert
     */
    public function deleteAlertCond($alert_id, $cond_id)
    {
        return $this->alertsRepository->deleteAlertCond($alert_id, $cond_id);
    }

    /**
     * Check changed row and send notifications.
     *
     * @param Table $table
     * @param string $type
     * @param array $all_rows_arr
     * @param array $changed_fields
     */
    public function checkAndSendNotifications(Table $table, string $type, array $all_rows_arr = [], array $changed_fields = [])
    {
        $changed_fields = array_diff($changed_fields, $this->service->system_fields);//ignore system fields
        if (!$table->add_alert) {
            return;
        }

        $_h_fields = $table->_fields()->joinOwnerHeader()->get();
        $alerts = $this->alertsRepository->findActiveAlerts($table->id, $type);
        $formula_parser = new FormulaEvaluatorRepository($table, $table->user_id, true);

        foreach ($alerts as $alert) {

            $active_rows = [];
            $record_specific_recipients = [];
            foreach ($all_rows_arr as $row_arr) {
                if ($this->checkAlertType($alert, $type, $row_arr, $changed_fields)) {

                    $active_rows[] = $row_arr;

                    if ($alert->_row_mail_field) {
                        $record_specific_recipients = array_merge(
                            $record_specific_recipients,
                            $this->getRecipients($row_arr[$alert->_row_mail_field->field] ?? '')
                        );
                    }

                }
            }

            $recipients = $this->getRecipients($alert->recipients, true);
            $recipients = array_unique( array_merge($recipients, $record_specific_recipients) );//add emails from rows and unique them

            if ($active_rows && $recipients) {

                $row = array_first($active_rows) ?: [];

                $toname = $alert->mail_addressee ? $formula_parser->formulaReplaceVars($row, $alert->mail_addressee, true) : '';
                $subject = $alert->mail_subject
                    ? $formula_parser->formulaReplaceVars($row, $alert->mail_subject, true)
                    : $this->getSubject($type, $_h_fields, $row, $alert);

                $fields_arr = $this->service->getFieldsArrayForNotification($table, $alert->_col_group);

                $params = [
                    'from.name' => config('app.name').' ANA',
                    'from.account' => 'noreply',
                    'subject' => $subject,
                    'to.address' => $recipients,
                    'to.name' => $toname,
                ];
                $data = [
                    'replace_main_message' => $alert->mail_message ? $formula_parser->formulaReplaceVars($row, $alert->mail_message, true) : '',
                    'table_arr' => $table->getAttributes(),
                    'fields_arr' => $fields_arr,
                    'all_rows_arr' => $active_rows,
                    'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                    'changed_fields' => $changed_fields,
                    'alert_arr' => $alert->getAttributes(),
                    'type' => $type,
                ];

                Mail::to($recipients)->send( new TabldaMail('tablda.emails.row_changed', $data, $params) );

            }
        }
    }

    /**
     * Get type params for Alert Email.
     *
     * @param $mode
     * @param $_h_fields
     * @param array $row
     * @param TableAlert $alert
     * @return string
     */
    private function getSubject($mode, $_h_fields, array $row = [], TableAlert $alert)
    {
        switch ($mode) {
            case 'added':   $subject = 'New Record Added';
                            break;
            case 'updated': $subject = 'Existing Record Updated'
                            . ($alert->_field ? ': '.$alert->_field->name : '')
                            . ($alert->new_value ? ': '.$alert->new_value : '');
                            break;
            case 'deleted': $subject = 'Record Deleted';
                            break;
            default:        $subject = '';
                            break;
        }

        $shown_in_header = [];
        foreach ($_h_fields as $fld) {
            if ($fld->popup_header) {
                $shown_in_header[] = $fld->name . ': ' . ($row[$fld->field] ?? '');
            }
        }
        if ($shown_in_header && $subject) {
            $subject .= ' (' . implode(', ', $shown_in_header) . ')';
        }

        return $subject;
    }

    /**
     * Get Array of recipient emails.
     *
     * @param string $recipients
     * @param bool $convert_groups
     * @return array
     */
    private function getRecipients(string $recipients, $convert_groups = false)
    {
        $recipients = preg_replace('/[\s,]+/i', ';', $recipients);
        $recipients = preg_replace('/;+/i', ';', $recipients);
        $recipients = explode(';', $recipients);
        $emails = [];
        foreach ($recipients as $elem) {
            $res = [];
            preg_match('/\(Group\[(\d+)\]\)/i', $elem, $res);
            //if is UserGroup
            if (!empty($res[1])) {
                if ($convert_groups) {
                    $emails = array_merge($emails, $this->userGroupRepository->getGroupEmails($res[1]));
                } else {
                    $emails[] = $elem;
                }
            }
            else
            //if is correct email
            if ($elem && filter_var($elem, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $elem;
            }
        }
        return $emails;
    }

    /**
     * Check that Alert is active.
     *
     * @param TableAlert $alert
     * @param string $type
     * @param array $row
     * @param array $changed_fields
     * @return bool
     */
    private function checkAlertType(TableAlert $alert, string $type, array $row = [], array $changed_fields = [])
    {
        if ($type == 'added') {
            return !!$alert->on_added;
        }

        if ($type == 'deleted') {
            return !!$alert->on_deleted;
        }

        if ($type == 'updated') {
            $col_group = $alert->_col_group ? $alert->_col_group->_fields->pluck('field')->toArray() : [];
            $changed_fields = $col_group ? array_intersect($changed_fields, $col_group) : $changed_fields; //Filter: only showed in email

            $reduced = true;
            $ar = [];
            foreach ($alert->_conditions as $cond) {

                if (!$cond['is_active']) {
                    continue;
                }

                $rowval = ($row[$cond['_field']['field'] ?? ''] ?? null);
                switch ($cond['condition']) {
                    case '>': $compare = $rowval > $cond['new_value']; break;
                    case '<': $compare = $rowval < $cond['new_value']; break;
                    case '!=': $compare = $rowval != $cond['new_value']; break;
                    default: $compare = $rowval == $cond['new_value']; break;
                }

                $avail = !$cond['_field']
                    ||
                    (
                        in_array($cond['_field']['field'], $changed_fields)
                        &&
                        (
                            !$cond['new_value']
                            ||
                            $compare
                        )
                    );

                $ar[] = strtolower($cond['logic']);

                $reduced = strtolower($cond['logic']) == 'or'
                    ? $reduced || $avail
                    : $reduced && $avail;
            }

            return !!$alert->on_updated
                &&
                $alert->_conditions->count()
                &&
                $changed_fields
                &&
                $reduced;
        }

        return true;
    }
}