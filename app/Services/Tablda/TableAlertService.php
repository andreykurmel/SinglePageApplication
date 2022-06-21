<?php


namespace Vanguard\Services\Tablda;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Vanguard\Jobs\AlertAutomationUpdateJob;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableAlertRight;
use Vanguard\Repositories\Tablda\Permissions\UserGroupRepository;
use Vanguard\Repositories\Tablda\TableAlertsRepository;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;

class TableAlertService
{
    protected $alertsRepository;
    protected $userGroupRepository;
    protected $service;
    protected $alertFunctions;

    /**
     * TableAlertService constructor.
     */
    public function __construct()
    {
        $this->alertsRepository = new TableAlertsRepository();
        $this->userGroupRepository = new UserGroupRepository();
        $this->service = new HelperService();
        $this->alertFunctions = new AlertFunctionsService();
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
     * @param $id
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function getAnrTable($id)
    {
        return $this->alertsRepository->getAnrTable($id);
    }

    /**
     * @param $id
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function getUfvTable($id)
    {
        return $this->alertsRepository->getUfvTable($id);
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
            $recs = $this->service->parseRecipients($data['recipients'] ?? '');
            $data['recipients'] = implode("; ", $recs);
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
     * @param $alert_id
     * @param array $data
     * @param int $table_id
     * @return TableAlert
     */
    public function insertAnrTable($alert_id, Array $data, int $table_id)
    {
        $data['table_id'] = $data['table_id'] ?? $table_id;
        return $this->alertsRepository->insertAnrTable($alert_id, $data);
    }

    /**
     * @param $alert_id
     * @param $id
     * @param array $data
     * @return TableAlert
     */
    public function updateAnrTable($alert_id, $id, Array $data)
    {
        return $this->alertsRepository->updateAnrTable($alert_id, $id, $data);
    }

    /**
     * @param $alert_id
     * @param $id
     * @return TableAlert
     */
    public function deleteAnrTable($alert_id, $id)
    {
        return $this->alertsRepository->deleteAnrTable($alert_id, $id);
    }

    /**
     * @param int $to_anr_tb_id
     * @param int $from_anr_tb_id
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function copyAnrFields(int $to_anr_tb_id, int $from_anr_tb_id)
    {
        return $this->alertsRepository->copyAnrFields($to_anr_tb_id, $from_anr_tb_id);
    }

    /**
     * @param $anr_id
     * @param array $data
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function insertAnrField($anr_id, Array $data)
    {
        return $this->alertsRepository->insertAnrField($anr_id, $data);
    }

    /**
     * @param $anr_id
     * @param $id
     * @param array $data
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function updateAnrField($anr_id, $id, Array $data)
    {
        return $this->alertsRepository->updateAnrField($anr_id, $id, $data);
    }

    /**
     * @param $anr_id
     * @param $id
     * @return \Vanguard\Models\Table\AlertAnrTable
     */
    public function deleteAnrField($anr_id, $id)
    {
        return $this->alertsRepository->deleteAnrField($anr_id, $id);
    }

    /**
     * @param $table_id
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertUfvTable($table_id, $alert_id, Array $data)
    {
        return $this->alertsRepository->insertUfvTable($table_id, $alert_id, $data);
    }

    /**
     * @param $alert_id
     * @param $id
     * @param array $data
     * @return TableAlert
     */
    public function updateUfvTable($alert_id, $id, Array $data)
    {
        return $this->alertsRepository->updateUfvTable($alert_id, $id, $data);
    }

    /**
     * @param $alert_id
     * @param $id
     * @return TableAlert
     */
    public function deleteUfvTable($alert_id, $id)
    {
        return $this->alertsRepository->deleteUfvTable($alert_id, $id);
    }

    /**
     * @param int $to_ufv_tb_id
     * @param int $from_ufv_tb_id
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function copyUfvFields(int $to_ufv_tb_id, int $from_ufv_tb_id)
    {
        return $this->alertsRepository->copyUfvFields($to_ufv_tb_id, $from_ufv_tb_id);
    }

    /**
     * @param $ufv_id
     * @param array $data
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function insertUfvField($ufv_id, Array $data)
    {
        return $this->alertsRepository->insertUfvField($ufv_id, $data);
    }

    /**
     * @param $ufv_id
     * @param $id
     * @param array $data
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function updateUfvField($ufv_id, $id, Array $data)
    {
        return $this->alertsRepository->updateUfvField($ufv_id, $id, $data);
    }

    /**
     * @param $ufv_id
     * @param $id
     * @return \Vanguard\Models\Table\AlertUfvTable
     */
    public function deleteUfvField($ufv_id, $id)
    {
        return $this->alertsRepository->deleteUfvField($ufv_id, $id);
    }

    /**
     * @param TableAlert $alert
     * @param int $table_permis_id
     * @param $can_edit
     * @param $can_activate
     * @return mixed
     */
    public function toggleAlertRight(TableAlert $alert, int $table_permis_id, $can_edit, $can_activate)
    {
        return $this->alertsRepository->toggleAlertRight($alert, $table_permis_id, $can_edit, $can_activate);
    }

    /**
     * @param TableAlert $alert
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteAlertRight(TableAlert $alert, int $table_permis_id)
    {
        return $this->alertsRepository->deleteAlertRight($alert, $table_permis_id);
    }

    /**
     * Send emails as separate files if we have a few rows.
     *
     * @param Table $table
     * @param string $type
     * @param array $all_rows_arr
     * @param array $changed_fields
     * @param array $extra
     */
    public function checkAndSendNotifArray(Table $table, string $type, array $all_rows_arr = [], array $changed_fields = [], array $extra = [])
    {
        //Addon inactive
        if (!$table->add_alert) {
            return;
        }

        $alerts = $this->alertsRepository->findActiveAlerts($table->id, $extra['user_id'] ?? null);
        //No active Alerts
        if (!$alerts->count()) {
            return;
        }

        //Cache alerts
        $this->alertsRepository->rememberArray( $alerts->pluck('id')->toArray() );

        //Show independent email for each row (if<10) or all rows in one email.
        if (count($all_rows_arr) < 10) {
            foreach ($all_rows_arr as $rows_arr) {
                $this->checkAndSendNotifications($alerts, $table, $type, [$rows_arr], $changed_fields);
            }
        } else {
            $this->checkAndSendNotifications($alerts, $table, $type, $all_rows_arr, $changed_fields);
        }
    }

    /**
     * Check changed row and send notifications.
     *
     * @param Collection $alerts
     * @param Table $table
     * @param string $type
     * @param array $all_rows_arr
     * @param array $changed_fields
     */
    public function checkAndSendNotifications(Collection $alerts, Table $table, string $type, array $all_rows_arr = [], array $changed_fields = [])
    {
        $changed_fields = array_diff($changed_fields, $this->service->system_fields);//ignore system fields
        foreach ($alerts as $alert) {
            $recipients = [ 'to'=>[], 'cc'=>[], 'bcc'=>[] ];

            $active_rows = [];
            $record_specific_recipients = [];
            $limited_row_ids = $this->getLimitedByRG($alert, array_first($all_rows_arr) ?? []);
            foreach ($all_rows_arr as $row_arr) {
                if ($this->checkAlertType($alert, $type, $row_arr, $changed_fields, $limited_row_ids)) {

                    $active_rows[] = $row_arr;

                    if ($alert->_row_mail_field) {
                        $recipients['to'] = $this->service->addRecipientsEmails($recipients['to'], $row_arr[$alert->_row_mail_field->field] ?? '');
                    }
                    if ($alert->_cc_row_mail_field) {
                        $recipients['cc'] = $this->service->addRecipientsEmails($recipients['cc'], $row_arr[$alert->_cc_row_mail_field->field] ?? '');
                    }
                    if ($alert->_bcc_row_mail_field) {
                        $recipients['bcc'] = $this->service->addRecipientsEmails($recipients['bcc'], $row_arr[$alert->_bcc_row_mail_field->field] ?? '');
                    }

                }
            }

            $recipients['to'] = $this->service->addRecipientsEmails($recipients['to'], $alert->recipients ?? '', true);
            $recipients['cc'] = $this->service->addRecipientsEmails($recipients['cc'], $alert->cc_recipients ?? '', true);
            $recipients['bcc'] = $this->service->addRecipientsEmails($recipients['bcc'], $alert->bcc_recipients ?? '', true);

            if ($active_rows && $recipients['to']) {
                foreach ($active_rows as $act_row) {
                    $this->alertFunctions->sendEmail($alert, [$act_row], $recipients, $changed_fields, $type);
                }
            }

            $trigger = array_first($active_rows);

            if ($active_rows && $alert->_ufv_tables->count()) {
                AlertAutomationUpdateJob::dispatch($alert->id, $trigger);
            }

            if ($active_rows && $alert->_anr_tables->count()) {
                $this->alertFunctions->doANR($alert, $trigger);
            }
        }
    }

    /**
     * @param TableAlert $alert
     * @param array $row
     * @return array
     */
    protected function getLimitedByRG(TableAlert $alert, array $row)
    {
        return [
            'added' => $this->RGids($alert, '_added_ref_cond', $row),
            'updated' => $this->RGids($alert, '_updated_ref_cond', $row),
            'deleted' => $this->RGids($alert, '_deleted_ref_cond', $row),
        ];
    }

    /**
     * @param TableAlert $alert
     * @param string $relation
     * @param array $row
     * @return array
     */
    protected function RGids(TableAlert $alert, string $relation, array $row)
    {
        if ($alert->{$relation}) {
            $applied_ids = new TableDataQuery( $alert->_table );
            $applied_ids->applyRefConditionRow($alert->{$relation}, $row);
            return $applied_ids->getQuery()
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
        }
        return [];
    }

    /**
     * Check that Alert is active.
     *
     * @param TableAlert $alert
     * @param string $type
     * @param array $row
     * @param array $changed_fields
     * @param array $limited_row_ids
     * @return bool
     */
    protected function checkAlertType(TableAlert $alert, string $type, array $row = [], array $changed_fields = [], array $limited_row_ids = [])
    {
        if ($type == 'added') {
            return !!$alert->on_added
                && (!$limited_row_ids['added'] || in_array($row['id']??'', $limited_row_ids['added']));
        }

        if ($type == 'deleted') {
            return !!$alert->on_deleted
                && (!$limited_row_ids['deleted'] || in_array($row['id']??'', $limited_row_ids['deleted']));
        }

        if ($type == 'updated') {
            $col_group = $alert->_col_group ? $alert->_col_group->_fields->pluck('field')->toArray() : [];
            $filtered_changed = $col_group ? array_intersect($changed_fields, $col_group) : $changed_fields; //Filter: only showed in email

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
                        in_array($cond['_field']['field'], $filtered_changed)
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
                (!$limited_row_ids['updated'] || in_array($row['id']??'', $limited_row_ids['updated']))
                &&
                $filtered_changed
                &&
                $reduced;
        }

        return true;
    }
}