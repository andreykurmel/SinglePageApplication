<?php

namespace Vanguard\Services\Tablda;


use Illuminate\Database\Eloquent\Collection;
use Vanguard\Mail\TabldaMail;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableBackup;
use Vanguard\Repositories\Tablda\TableBackupRepository;
use Vanguard\Repositories\Tablda\TableData\FormulaEvaluatorRepository;
use Vanguard\Repositories\Tablda\TableRepository;

class TableBackupService
{
    protected $service;
    protected $repo;

    /**
     * TableService constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->repo = new TableBackupRepository();
    }


    /**
     * Get only table backup info
     *
     * @param $table_backup_id
     * @return mixed
     */
    public function getTableBackup($table_backup_id)
    {
        return $this->repo->getTableBackup($table_backup_id);
    }

    /**
     * @param string $day
     * @param string $time
     * @return Collection
     */
    public function getbyTime(string $day, string $time)
    {
        return $this->repo->getbyTime($day, $time);
    }

    /**
     * Add Backup row to store Table
     *
     * @param array $data
     * @return mixed
     */
    public function addTableBackup(Array $data)
    {
        return $this->repo->addTableBackup($data);
    }

    /**
     * Update Backup row.
     *
     * @param $table_backup_id
     * @param array $data
     * @return mixed
     */
    public function updateTableBackup($table_backup_id, Array $data)
    {
        return $this->repo->updateTableBackup($table_backup_id, $data);
    }

    /**
     * Delete Backup row.
     *
     * @param $table_backup_id
     * @return mixed
     */
    public function deleteTableBackup($table_backup_id)
    {
        return $this->repo->deleteTableBackup($table_backup_id);
    }

    /**
     * Notify users about created Backup.
     *
     * @param TableBackup $bkp
     */
    public function notifyUser(TableBackup $bkp)
    {
        $row = $bkp->toArray();
        $table = (new TableRepository())->getTableByDB('table_backups');

        $formula_parser = new FormulaEvaluatorRepository($table, $table->user_id, true);

        /*$email_field = $bkp ? $bkp->_bkp_email_field : null;
        $email_field = $email_field ? $email_field->field : '';*/
        $static_emails = $bkp ? $bkp['bkp_email_field_static'] : '';

        $recipients = $this->service->parseRecipients($static_emails ?? '');
        if ($recipients) {

            $fields_arr = $this->service->getFieldsArrayForNotification($table);
            //convert
            $row['user_cloud_id'] = $bkp->_cloud ? $bkp->_cloud->name : '';
            $row['time'] = HelperService::timeToLocal('', $row['time']??'00:00', $row['timezone']??'UTC', 'H:i');

            $toname = $bkp['bkp_addressee_txt'] ? $formula_parser->formulaReplaceVars($row, $bkp['bkp_addressee_txt'], true) : '';
            $subject = $bkp['bkp_email_subject'] ? $formula_parser->formulaReplaceVars($row, $bkp['bkp_email_subject'], true) : 'Thanks for submission';

            $usr = auth()->user();
            $user_str = $usr ? ($usr->first_name ? $usr->first_name . ' ' . $usr->last_name : $usr->username) : '';
            $greeting = $usr || $toname
                ? 'Hello, '.($toname ?: $user_str).'!'
                : 'Hello!';

            $rows_arr = $this->service->prepareRowVals($table, $row);

            $params = [
                'from.name' => config('app.name'),
                'from.account' => 'noreply',
                'subject' =>  $subject,
                'to.address' => $recipients,
                'to.name' => $toname,
            ];
            $data = [
                'greeting' => $greeting,
                'replace_main_message' => $bkp['bkp_email_message'] ? $formula_parser->formulaReplaceVars($row, $bkp['bkp_email_message'], true) : '',
                'table_arr' => $table->getAttributes(),
                'fields_arr' => $fields_arr,
                'has_unit' => $this->service->fldsArrHasUnit($fields_arr),
                'all_rows_arr' => $rows_arr,
                'changed_fields' => [],
                'alert_arr' => ['mail_format' => 'table'],
                'type' => 'created',
            ];

            \Mail::to($recipients)->send( new TabldaMail('tablda.emails.backup_created', $data, $params) );

        }
    }
}