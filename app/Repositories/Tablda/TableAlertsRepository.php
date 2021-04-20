<?php

namespace Vanguard\Repositories\Tablda;

use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableAlertCondition;
use Vanguard\Services\Tablda\HelperService;

class TableAlertsRepository
{
    protected $service;

    /**
     * TableAlertRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Get Table Alert.
     *
     * @param $table_alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function getAlert($table_alert_id) {
        return TableAlert::where('id', '=', $table_alert_id)
            ->with('_conditions')
            ->first();
    }

    /**
     * Find Active Alerts for selected Table.
     *
     * @param $table_id
     * @param $type
     * @return mixed
     */
    public function findActiveAlerts($table_id, $type) {
        return TableAlert::where('table_id', $table_id)
            ->where('is_active', 1)
            ->with([
                '_conditions' => function($q) {
                    $q->with('_field');
                },
                '_col_group' => function($q) {
                    $q->with('_fields');
                },
                '_row_mail_field'
            ])
            ->get();
    }

    /**
     * Insert Table Alert.
     *
     * @param array $data
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function insertAlert(Array $data) {
        switch ($data['mail_format'] ?? 'table') {
            case 'list': $data['mail_format'] = 'list'; break;
            case 'vertical': $data['mail_format'] = 'vertical'; break;
            default: $data['mail_format'] = 'table'; break;
        }

        $saved = TableAlert::create( $this->service->delSystemFields($data) );
        return $this->getAlert($saved->id);
    }

    /**
     * Update Table Alert.
     *
     * @param array $data
     * @param $alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function updateAlert($alert_id, Array $data) {
        switch ($data['mail_format'] ?? 'table') {
            case 'list': $data['mail_format'] = 'list'; break;
            case 'vertical': $data['mail_format'] = 'vertical'; break;
            default: $data['mail_format'] = 'table'; break;
        }

        TableAlert::where('id', '=', $alert_id)
            ->update( $this->service->delSystemFields($data) );

        return $this->getAlert($alert_id);
    }

    /**
     * Delete Table Alert.
     *
     * @param int $table_alert_id
     * @return mixed
     */
    public function deleteAlert($table_alert_id) {
        return TableAlert::where('id', $table_alert_id)
            ->delete();
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertAlertCond($alert_id, Array $data) {
        $data['is_active'] = 1;
        $data['table_alert_id'] = $alert_id;
        $data['logic'] = $data['logic'] ?? 'and';
        $data['condition'] = $data['condition'] ?? '=';

        TableAlertCondition::create( $this->service->delSystemFields($data) );

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function updateAlertCond($alert_id, $cond_id, Array $data) {
        $data['table_alert_id'] = $alert_id;
        $data['logic'] = $data['logic'] ?? 'and';
        $data['condition'] = $data['condition'] ?? '=';

        TableAlertCondition::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $cond_id)
            ->update( $this->service->delSystemFields($data) );

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $cond_id
     * @return TableAlert
     */
    public function deleteAlertCond($alert_id, $cond_id) {
        TableAlertCondition::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $cond_id)
            ->delete();
        return $this->getAlert($alert_id);
    }
}