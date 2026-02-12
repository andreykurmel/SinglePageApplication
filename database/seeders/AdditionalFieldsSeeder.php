<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class AdditionalFieldsSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $DDLRepository;
    private $viewRepository;

    /**
     * AdditionalFieldsSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new \Vanguard\Services\Tablda\Permissions\TablePermissionService();
        $this->permissionsRepository = new \Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository();
        $this->DDLRepository = new \Vanguard\Repositories\Tablda\DDLRepository();
        $this->viewRepository = new \Vanguard\Repositories\Tablda\TableViewRepository();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()//DELETE FROM `table_fields` WHERE table_id=67
    {
        $user = User::where('role_id', '=', '1')->first();

        $table_sub = Table::where('db_name', 'user_subscriptions')->first();

        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'user_subscriptions',
                'name' => 'Subscriptions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);

            //headers for 'Subscriptions'

            $this->create('user_id','User', $table_sub->id, $user, 'User');
            $this->create('plan_id','Plan', $table_sub->id, $user, 'Integer');
            $this->create('add_bi','Add-on Features,BI', $table_sub->id, $user, 'Boolean');
            $this->create('add_map','Add-on Features,GSI', $table_sub->id, $user, 'Boolean');
            $this->create('add_request','Add-on Features,DCR', $table_sub->id, $user, 'Boolean');
            $this->create('add_alert','Add-on Features,ANA', $table_sub->id, $user, 'Boolean');
            $this->create('add_kanban','Add-on Features,Kanban', $table_sub->id, $user, 'Boolean');
            $this->create('add_gantt','Add-on Features,Gantt', $table_sub->id, $user, 'Boolean');
            $this->create('add_email','Add-on Features,Email', $table_sub->id, $user, 'Boolean');
            $this->create('add_calendar','Add-on Features,Calendar', $table_sub->id, $user, 'Boolean');
            $this->create('add_twilio','Add-on Features,Twilio', $table_sub->id, $user, 'Boolean');
            $this->create('add_tournament','Add-on Features,Tournament', $table_sub->id, $user, 'Boolean');
            $this->create('add_simplemap','Add-on Features,Thematic Maps', $table_sub->id, $user, 'Boolean');
            $this->create('add_grouping','Add-on Features,Grouping', $table_sub->id, $user, 'Boolean');
            $this->create('add_report','Add-on Features,Report', $table_sub->id, $user, 'Boolean');
            $this->create('add_ai','Add-on Features,AI', $table_sub->id, $user, 'Boolean');
            $this->create('cost','Total Cost', $table_sub->id, $user, 'Currency');
            $this->create('avail_credit','Credit', $table_sub->id, $user, 'Currency');
            $renew_field = $this->create('renew','Contract Cycle', $table_sub->id, $user);
            $this->create('recurrent_pay','Auto Renew', $table_sub->id, $user, 'Boolean');
            $this->create('notes','Notes', $table_sub->id, $user);

            $this->create('created_by','Created By', $table_sub->id, $user, 'User');
            $this->create('created_on','Created On', $table_sub->id, $user, 'Date Time');
            $this->create('modified_by','Modified By', $table_sub->id, $user, 'User');
            $this->create('modified_on','Modified On', $table_sub->id, $user, 'Date Time');

            //DDL for 'Renew'
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table_sub->id,
                'name' => 'RenewDDL',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Yearly',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Monthly',
            ]);

            $renew_field->input_type = 'S-Select';
            $renew_field->ddl_id = $ddl->id;
            $renew_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 0, ['user_id']);
        $this->viewRepository->addSys($table_sub);
        $permis = $this->permissionsRepository->getSysPermission($table_sub->id, 1);
        $permis->update(['can_edit_tb' => 1]);
    }

    private function create($field, $name, $table_id, $user, $type = 'String') {
        return TableField::create([
            'table_id' => $table_id,
            'field' => $field,
            'name' => $name,
            'f_type' => $type,
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);
    }
}
