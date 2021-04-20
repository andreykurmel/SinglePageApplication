<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class AdditionalFieldsSeeder extends Seeder
{
    private $permissionsService;
    private $DDLRepository;
    private $service;
    private $viewRepository;

    /**
     * AdditionalFieldsSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     * @param \Vanguard\Repositories\Tablda\DDLRepository $DDLRepository
     * @param \Vanguard\Services\Tablda\HelperService $service
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService,
        \Vanguard\Repositories\Tablda\DDLRepository $DDLRepository,
        \Vanguard\Services\Tablda\HelperService $service,
        \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
    )
    {
        $this->permissionsService = $permissionsService;
        $this->DDLRepository = $DDLRepository;
        $this->service = $service;
        $this->viewRepository = $tableViewRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
            $this->create('add_request','Add-on Features,DC', $table_sub->id, $user, 'Boolean');
            $this->create('add_alert','Add-on Features,ANA', $table_sub->id, $user, 'Boolean');
            $this->create('add_kanban','Add-on Features,Kanban', $table_sub->id, $user, 'Boolean');
            $this->create('add_gantt','Add-on Features,Gantt', $table_sub->id, $user, 'Boolean');
            $this->create('add_email','Add-on Features,Email', $table_sub->id, $user, 'Boolean');
            $this->create('add_calendar','Add-on Features,Calendar', $table_sub->id, $user, 'Boolean');
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

            $renew_field->input_type = 'Selection';
            $renew_field->ddl_id = $ddl->id;
            $renew_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 0, ['user_id']);
        $this->viewRepository->addSys($table_sub);
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
