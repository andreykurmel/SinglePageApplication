<?php

namespace Vanguard\Repositories\Tablda;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Vanguard\Models\Table\AlertAnrTable;
use Vanguard\Models\Table\AlertAnrTableField;
use Vanguard\Models\Table\AlertClickUpdate;
use Vanguard\Models\Table\AlertUfvTable;
use Vanguard\Models\Table\AlertUfvTableField;
use Vanguard\Models\Table\TableAlert;
use Vanguard\Models\Table\TableAlertCondition;
use Vanguard\Models\Table\TableAlertRight;
use Vanguard\Models\Table\TableAlertSnapshotField;
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
     * @param array $alert_ids
     */
    public function rememberArray(array $alert_ids)
    {
        $not_cached = collect($alert_ids)->filter(function ($id) {
            return ! \Cache::store('redis')->has('table.alert_anr_ufv.'.$id);
        });

        if ($not_cached) {
            $alerts = TableAlert::whereIn('id', $not_cached)
                ->with($this->AnrUfvRelations())
                ->get();

            foreach ($alerts as $alert) {
                \Cache::store('redis')->forever('table.alert_anr_ufv.' . $alert->id, $alert);
            }
        }
    }

    /**
     * Get Table Alert.
     *
     * @param $table_alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function getAlertAnrUfv($table_alert_id)
    {
        return \Cache::store('redis')->rememberForever('table.alert_anr_ufv.'.$table_alert_id, function () use ($table_alert_id) {
            return TableAlert::where('id', '=', $table_alert_id)
                ->with($this->AnrUfvRelations())
                ->first();
        });
    }

    /**
     * @return array
     */
    protected function AnrUfvRelations()
    {
        return [
            '_anr_tables' => function ($q) {
                $q->with([
                    '_temp_table',
                    '_anr_fields' => function ($q) {
                        $q->with('_temp_field:id,field,input_type', '_temp_inherit_field:id,field,input_type');
                    },
                ]);
            },
            '_ufv_tables' => function ($q) {
                $q->with([
                    '_ref_cond',
                    '_ufv_fields' => function ($q) {
                        $q->with('_field:id,field,input_type', '_inherit_field:id,field,input_type');
                    },
                ]);
            },
        ];
    }

    /**
     * @param int $table_id
     * @param int|null $not_owner_user_id
     * @return Collection
     */
    public function findActiveAlerts(int $table_id, int $not_owner_user_id = null)
    {
        return TableAlert::where('table_id', '=', $table_id)
            ->where('is_active', '=', 1)
            ->isAvailForUser($not_owner_user_id)
            ->with([
                '_table',
                '_conditions' => function ($q) {
                    $q->with('_field');
                },
                '_col_group' => function ($q) {
                    $q->with('_fields');
                },
                '_ufv_tables',
                '_anr_tables' => function ($q) {
                    $q->with('_anr_fields');
                },
                '_row_sms_field',
                '_row_mail_field',
                '_cc_row_mail_field',
                '_bcc_row_mail_field',
                '_added_row_group',
                '_updated_row_group',
                '_deleted_row_group',
                '_click_updates' => function ($q) {
                    $q->with('_field');
                },
            ])
            ->get();
    }

    /**
     * Insert Table Alert.
     *
     * @param array $data
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function insertAlert(array $data)
    {
        switch ($data['mail_format'] ?? 'table') {
            case 'list':
                $data['mail_format'] = 'list';
                break;
            case 'vertical':
                $data['mail_format'] = 'vertical';
                break;
            default:
                $data['mail_format'] = 'table';
                break;
        }
        $data['user_id'] = auth()->id();
        $data['ask_anr_confirmation'] = 0;
        $data['notif_email_add_tabledata'] = 1;
        $data['notif_email_add_clicklink'] = 1;
        $data['click_introduction'] = 'Click the following link to update (confirm, etc.):';

        $saved = TableAlert::create($this->service->delSystemFields($data));
        return $this->getAlert($saved->id);
    }

    /**
     * @param $table_alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function justAlert($table_alert_id)
    {
        return TableAlert::where('id', '=', $table_alert_id)->first();
    }

    /**
     * Get Table Alert.
     *
     * @param $table_alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function getAlert($table_alert_id)
    {
        return TableAlert::where('id', '=', $table_alert_id)
            ->with(self::relations())
            ->first();
    }

    /**
     * @return array
     */
    public static function relations(): array
    {
        return [
            '_alert_rights',
            '_conditions',
            '_snapshot_fields',
            '_ufv_tables' => function($q) {
                $q->with('_ufv_fields');
            },
            '_anr_tables' => function ($q) {
                $q->with('_anr_fields');
            },
            '_click_updates' => function ($q) {
                $q->with('_field');
            },
        ];
    }

    /**
     * Update Table Alert.
     *
     * @param array $data
     * @param $alert_id
     * @return \Vanguard\Models\Table\TableAlert
     */
    public function updateAlert($alert_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        switch ($data['mail_format'] ?? 'table') {
            case 'list':
                $data['mail_format'] = 'list';
                break;
            case 'vertical':
                $data['mail_format'] = 'vertical';
                break;
            default:
                $data['mail_format'] = 'table';
                break;
        }
        $data['mail_delay_hour'] = !empty($data['mail_delay_hour']) ? intval($data['mail_delay_hour']) : '';
        $data['mail_delay_min'] = !empty($data['mail_delay_min']) ? intval($data['mail_delay_min']) : '';
        $data['mail_delay_sec'] = !empty($data['mail_delay_sec']) ? intval($data['mail_delay_sec']) : '';
        if (isset($data['snapshot_day_freq'])) {
            $data['snapshot_day_freq'] = json_encode($data['snapshot_day_freq'] ?: []);
        }

        TableAlert::where('id', '=', $alert_id)
            ->update($this->service->delSystemFields($data));

        return $this->getAlert($alert_id);
    }

    /**
     * Delete Table Alert.
     *
     * @param int $table_alert_id
     * @return mixed
     */
    public function deleteAlert($table_alert_id)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$table_alert_id);

        return TableAlert::where('id', $table_alert_id)
            ->delete();
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertAlertCond($alert_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $init_logic = $data['logic'] ?? '';
        $init_cond = $this->getAlert($alert_id)->_conditions->first();
        $def_logic = $init_cond ? $init_cond->logic : 'and';

        $data['is_active'] = 1;
        $data['table_alert_id'] = $alert_id;
        $data['logic'] = $data['logic'] ?? $def_logic;
        $data['condition'] = $data['condition'] ?? '=';

        TableAlertCondition::create($this->service->delSystemFields($data));

        if ($init_logic) {
            //sync 'logic' to all
            TableAlertCondition::where('table_alert_id', '=', $alert_id)
                ->update(['logic' => $init_logic]);
        }

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function updateAlertCond($alert_id, $cond_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;
        $data['logic'] = $data['logic'] ?? 'and';
        $data['condition'] = $data['condition'] ?? '=';

        TableAlertCondition::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $cond_id)
            ->update($this->service->delSystemFields($data));

        //sync 'logic' to all
        TableAlertCondition::where('table_alert_id', '=', $alert_id)
            ->update( [ 'logic' => $data['logic'] ] );

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $cond_id
     * @return TableAlert
     */
    public function deleteAlertCond($alert_id, $cond_id)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        TableAlertCondition::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $cond_id)
            ->delete();
        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertAnrTable($alert_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;
        $data['is_active'] = 1;
        $data['temp_is_active'] = 1;
        $data['qty'] = intval($data['qty'] ?? 1);
        $data['temp_qty'] = intval($data['temp_qty'] ?? 1);

        AlertAnrTable::create($this->service->delSystemFields($data));

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $id
     * @param array $data
     * @param bool $empty
     * @return TableAlert
     */
    public function updateAnrTable($alert_id, $id, array $data, bool $empty = false)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;

        AlertAnrTable::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->update($this->service->delSystemFields($data));

        return $empty ? new TableAlert() : $this->getAlert($alert_id);
    }

    /**
     * @param array $anr_table_ids
     * @param array $data
     * @return mixed
     */
    public function massUpdateAnrFields(array $anr_table_ids, array $data)
    {
        foreach ($anr_table_ids as $alert_id) {
            \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);
        }

        return AlertAnrTableField::whereIn('anr_table_id', $anr_table_ids)
            ->update($this->service->delSystemFields($data));
    }

    /**
     *
     */
    public function clearAddedForTemp()
    {
        AlertAnrTable::whereNull('name')->delete();
        AlertAnrTableField::whereNull('table_field_id')->delete();
    }

    /**
     * @param $alert_id
     * @param $id
     * @return TableAlert
     */
    public function deleteAnrTable($alert_id, $id)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        AlertAnrTable::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->delete();
        return $this->getAlert($alert_id);
    }

    /**
     * @param $table_id
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertUfvTable($table_id, $alert_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;
        $data['table_id'] = $table_id;
        $data['is_active'] = 1;

        AlertUfvTable::create($this->service->delSystemFields($data));

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $id
     * @param array $data
     * @param bool $empty
     * @return TableAlert
     */
    public function updateUfvTable($alert_id, $id, array $data, bool $empty = false)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;

        AlertUfvTable::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->update($this->service->delSystemFields($data));

        return $empty ? new TableAlert() : $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $id
     * @return TableAlert
     */
    public function deleteUfvTable($alert_id, $id)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        AlertUfvTable::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->delete();
        return $this->getAlert($alert_id);
    }

    /**
     * @param $table_id
     * @param $alert_id
     * @param array $data
     * @return TableAlert
     */
    public function insertSnpFieldTable($table_id, $alert_id, array $data)
    {
        $data['table_alert_id'] = $alert_id;
        TableAlertSnapshotField::create($this->service->delSystemFields($data));
        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $id
     * @param array $data
     * @return TableAlert
     */
    public function updateSnpFieldTable($alert_id, $id, array $data)
    {
        $data['table_alert_id'] = $alert_id;

        TableAlertSnapshotField::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->update($this->service->delSystemFields($data));

        return $this->getAlert($alert_id);
    }

    /**
     * @param $alert_id
     * @param $id
     * @return TableAlert
     */
    public function deleteSnpFieldTable($alert_id, $id)
    {
        TableAlertSnapshotField::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->delete();
        return $this->getAlert($alert_id);
    }

    public function activeSnapshots()
    {
        $now = now();
        return TableAlert::where('on_snapshot', '=', 1)
            ->where('is_active', '=', 1)
            ->where(function ($a) use ($now) {
                $a->where(function ($o1) use ($now) {
                    $o1->where('snapshot_type', '=', 'one_time');
                    $o1->where('snapshot_onetime_datetime', 'like', $now->format('Y-m-d H:i').'%');
                });
                $a->orWhere(function ($a1) use ($now) {
                    $a1->where('snapshot_type', '=', 'recurring');
                    $a1->where('snapshot_frequency', '=', 'hourly');
                    $a1->where('snapshot_hourly_freq', '=', abs($now->format('i')));
                });
                $a->orWhere(function ($b1) use ($now) {
                    $b1->where('snapshot_type', '=', 'recurring');
                    $b1->where('snapshot_time', 'like', $now->format('H:i').'%');
                    $b1->where(function ($b2) use ($now) {
                        $b2->where('snapshot_frequency', '=', 'daily');
                        $b2->orWhere(function ($b3) use ($now) {
                            $b3->where('snapshot_frequency', '=', 'weekly');
                            $b3->where('snapshot_day_freq', 'like', '%'.$now->format('l').'%');
                        });
                        $b2->orWhere(function ($c3) use ($now) {
                            $c3->where('snapshot_frequency', '=', 'monthly');
                            $c3->where(function ($c4) use ($now) {
                                if ($now->format('d') == '01') {
                                    $c4->orWhere('snapshot_month_freq', '=', 'first');
                                }
                                if ($now->format('d') == now()->lastOfMonth()->format('d')) {
                                    $c4->orWhere('snapshot_month_freq', '=', 'last');
                                }
                                $c4->orWhere(function ($c5) use ($now) {
                                    $c5->where('snapshot_month_freq', '=', 'day');
                                    $c5->where('snapshot_month_day', '=', $now->format('j'));
                                });
                                $c4->orWhere(function ($d5) use ($now) {
                                    $d5->where('snapshot_month_freq', '=', 'date');
                                    $d5->where('snapshot_month_date', '=', $now->format('Y-m-d'));
                                });
                            });
                        });
                    });
                });
            })
            ->with([
                '_table',
                '_snp_source_table',
                '_snapshot_fields' => function ($sf) {
                    $sf->with(['_cur_field', '_source_field']);
                },
            ])
            ->get();
    }

    /**
     * @param int $table_id
     * @return mixed
     */
    public function getOnce_WaitForApproveAnrTables(int $table_id)
    {
        $uid = auth()->id();
        $approve = TableAlert::where('table_id', '=', $table_id)
            ->whereHas('_anr_tables', function ($al) use ($uid) {
                $al->where('need_approve', '=', '1');
                $al->where('approve_user', '=', $uid);
            })
            ->with([
                '_anr_tables' => function ($q) {
                    $q->with('_anr_fields');
                },
                '_table_permissions' => function ($tp) {
                    $tp->isActiveForUserOrVisitor();
                },
            ])
            ->first();

        if ($approve) {
            $this->massUpdateAnrTable($approve->id, ['need_approve' => 0]);

            $approve->_can_edit = (auth()->id() == $approve->user_id) || ($approve->_table_permissions->where('_right.can_edit', '=', 1)->count());
            unset($approve->_table_permissions);
        }

        return $approve;
    }

    /**
     * @param int $alert_id
     * @param array $data
     * @return mixed
     */
    public function massUpdateAnrTable(int $alert_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        return AlertAnrTable::where('table_alert_id', '=', $alert_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param int $to_anr_tb_id
     * @param int $from_anr_tb_id
     * @return AlertAnrTable
     */
    public function copyAnrFields(int $to_anr_tb_id, int $from_anr_tb_id)
    {
        $anr = $this->getAnrTable($from_anr_tb_id);
        $to = $this->getAnrTable($to_anr_tb_id);

        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$anr->table_alert_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$to->table_alert_id);

        foreach ($anr->_anr_fields as $anr_fld) {
            $this->insertAnrField($to_anr_tb_id, $anr_fld->toArray());
        }

        return $this->getAnrTable($to_anr_tb_id);
    }

    /**
     * @param int $to_ufv_tb_id
     * @param int $from_ufv_tb_id
     * @return AlertUfvTable
     */
    public function copyUfvFields(int $to_ufv_tb_id, int $from_ufv_tb_id)
    {
        $ufv = $this->getUfvTable($from_ufv_tb_id);
        $to = $this->getUfvTable($to_ufv_tb_id);

        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$ufv->table_alert_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$to->table_alert_id);

        foreach ($ufv->_ufv_fields as $ufv_fld) {
            $this->insertUfvField($to_ufv_tb_id, $ufv_fld->toArray());
        }

        return $this->getUfvTable($to_ufv_tb_id);
    }

    /**
     * @param $anr_id
     * @param array $data
     * @return AlertAnrTable
     */
    public function insertAnrField($anr_id, array $data)
    {
        $anr = $this->getAnrTable($anr_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$anr->table_alert_id);

        $data['anr_table_id'] = $anr_id;
        $data['source'] = ($data['source']??'') == 'Inherit' ? 'Inherit' : 'Input';
        $data['temp_source'] = ($data['temp_source']??'') == 'Inherit' ? 'Inherit' : 'Input';

        AlertAnrTableField::create($this->service->delSystemFields($data));

        return $this->getAnrTable($anr_id);
    }

    /**
     * @param $id
     * @return AlertAnrTable
     */
    public function getAnrTable($id)
    {
        return AlertAnrTable::where('id', '=', $id)
            ->with('_anr_fields')
            ->first();
    }

    /**
     * @param $anr_id
     * @param $id
     * @param array $data
     * @param bool $empty
     * @return AlertAnrTable
     */
    public function updateAnrField($anr_id, $id, array $data, bool $empty = false)
    {
        $anr = $this->getAnrTable($anr_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$anr->table_alert_id);

        $data['anr_table_id'] = $anr_id;

        AlertAnrTableField::where('anr_table_id', '=', $anr_id)
            ->where('id', '=', $id)
            ->update($this->service->delSystemFields($data));

        return $empty ? new AlertAnrTable() : $this->getAnrTable($anr_id);
    }

    /**
     * @param $anr_id
     * @param $id
     * @return AlertAnrTable
     */
    public function deleteAnrField($anr_id, $id)
    {
        $anr = $this->getAnrTable($anr_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$anr->table_alert_id);

        AlertAnrTableField::where('anr_table_id', '=', $anr_id)
            ->where('id', '=', $id)
            ->delete();
        return $this->getAnrTable($anr_id);
    }

    /**
     * @param $ufv_id
     * @param array $data
     * @return AlertUfvTable
     */
    public function insertUfvField($ufv_id, array $data)
    {
        $ufv = $this->getUfvTable($ufv_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$ufv->table_alert_id);

        $data['ufv_table_id'] = $ufv_id;
        $data['source'] = ($data['source']??'') == 'Inherit' ? 'Inherit' : 'Input';

        AlertUfvTableField::create($this->service->delSystemFields($data));

        return $this->getUfvTable($ufv_id);
    }

    /**
     * @param $id
     * @return AlertUfvTable
     */
    public function getUfvTable($id)
    {
        return AlertUfvTable::where('id', '=', $id)
            ->with('_ufv_fields')
            ->first();
    }

    /**
     * @param $ufv_id
     * @param $id
     * @param array $data
     * @param bool $empty
     * @return AlertUfvTable
     */
    public function updateUfvField($ufv_id, $id, array $data, bool $empty = false)
    {
        $ufv = $this->getUfvTable($ufv_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$ufv->table_alert_id);

        $data['ufv_table_id'] = $ufv_id;

        AlertUfvTableField::where('ufv_table_id', '=', $ufv_id)
            ->where('id', '=', $id)
            ->update($this->service->delSystemFields($data));

        return $empty ? new AlertUfvTable() : $this->getUfvTable($ufv_id);
    }

    /**
     * @param $ufv_id
     * @param $id
     * @return AlertUfvTable
     */
    public function deleteUfvField($ufv_id, $id)
    {
        $ufv = $this->getUfvTable($ufv_id);
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$ufv->table_alert_id);

        AlertUfvTableField::where('ufv_table_id', '=', $ufv_id)
            ->where('id', '=', $id)
            ->delete();
        return $this->getUfvTable($ufv_id);
    }

    /**
     * @param $alert_id
     * @param array $data
     * @return AlertClickUpdate[]
     */
    public function insertClickUpdate($alert_id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;
        AlertClickUpdate::create($this->service->delSystemFields($data));

        return AlertClickUpdate::where('table_alert_id', $alert_id)->get();
    }

    /**
     * @param $alert_id
     * @param $id
     * @param array $data
     * @return AlertClickUpdate[]
     */
    public function updateClickUpdate($alert_id, $id, array $data)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        $data['table_alert_id'] = $alert_id;
        AlertClickUpdate::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->update($this->service->delSystemFields($data));

        return AlertClickUpdate::where('table_alert_id', $alert_id)->get();
    }

    /**
     * @param $alert_id
     * @param $id
     * @return AlertClickUpdate[]
     */
    public function deleteClickUpdate($alert_id, $id)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert_id);

        AlertClickUpdate::where('table_alert_id', '=', $alert_id)
            ->where('id', '=', $id)
            ->delete();
        return AlertClickUpdate::where('table_alert_id', $alert_id)->get();
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
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert->id);

        $right = $alert->_alert_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableAlertRight::create([
                'table_alert_id' => $alert->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
                'can_activate' => $can_activate,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit,
                'can_activate' => $can_activate,
            ]);
        }

        return $right;
    }

    /**
     * @param TableAlert $alert
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteAlertRight(TableAlert $alert, int $table_permis_id)
    {
        \Cache::store('redis')->forget('table.alert_anr_ufv.'.$alert->id);

        return $alert->_alert_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}