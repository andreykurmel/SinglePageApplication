<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;

class PlansAndAddonsSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $service;
    private $viewRepository;

    /**
     * PlansAndAddonsSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new \Vanguard\Services\Tablda\Permissions\TablePermissionService();
        $this->permissionsRepository = new \Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository();
        $this->service = new \Vanguard\Services\Tablda\HelperService();
        $this->viewRepository = new \Vanguard\Repositories\Tablda\TableViewRepository();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Create fake table 'Fees' for Plans and Addons
        $user = User::where('role_id', '=', '1')->first();

        $table = Table::where('db_name', 'fees')->first();

        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'fees',
                'name' => 'Fees',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);

            //headers for 'Fees'
            $this->create('plan', 'Plan', $table->id, $user);
            $this->create('feature', 'Advanced Features', $table->id, $user);
            $this->create('per_month', 'Per Month', $table->id, $user, 'Currency');
            $this->create('per_year', 'Per Year', $table->id, $user, 'Currency');
            $this->create('notes', 'Notes', $table->id, $user);

            $this->create('created_by', 'Created By', $table->id, $user, 'User');
            $this->create('created_on', 'Created On', $table->id, $user, 'Date Time');
            $this->create('modified_by', 'Modified By', $table->id, $user, 'User');
            $this->create('modified_on', 'Modified On', $table->id, $user, 'Date Time');

            $seed_data = true;
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



        if (isset($seed_data)) {
            //Seed Plans
            $basic = \Vanguard\Models\User\Plan::create([
                'code' => 'basic',
                'name' => 'Basic',
                'per_month' => 0,
                'per_year' => 0,
                'notes' => 'Default plan for all registered users.',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            $standard = \Vanguard\Models\User\Plan::create([
                'code' => 'standard',
                'name' => 'Standard',
                'per_month' => 5,
                'per_year' => 50,
                'notes' => 'Addons are not available.',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            $advanced = \Vanguard\Models\User\Plan::create([
                'code' => 'advanced',
                'name' => 'Advanced',
                'per_month' => 20,
                'per_year' => 200,
                'notes' => 'Advanced features not included.',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            $enterprise = \Vanguard\Models\User\Plan::create([
                'code' => 'enterprise',
                'name' => 'Enterprise',
                'per_month' => 100,
                'per_year' => 1000,
                'notes' => 'All advanced features included.',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);


            //Seed FlanFeatures
            $f_basic = \Vanguard\Models\User\PlanFeature::create([
                'type' => 'plan',
                'object_id' => $basic->id,
                'q_tables' => 50,
                'row_table' => 1000,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            $f_std = \Vanguard\Models\User\PlanFeature::create([
                'type' => 'plan',
                'object_id' => $standard->id,
                'q_tables' => 200,
                'row_table' => 5000,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            $f_adv = \Vanguard\Models\User\PlanFeature::create([
                'type' => 'plan',
                'object_id' => $advanced->id,
                'q_tables' => 500,
                'row_table' => 50000,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            $f_ent = \Vanguard\Models\User\PlanFeature::create([
                'type' => 'plan',
                'object_id' => $enterprise->id,
                'q_tables' => 1000,
                'row_table' => 100000,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);


            //Link Plan Features
            $basic->plan_feature_id = $f_basic->id;
            $basic->save();
            $standard->plan_feature_id = $f_std->id;
            $standard->save();
            $advanced->plan_feature_id = $f_adv->id;
            $advanced->save();
            $enterprise->plan_feature_id = $f_ent->id;
            $enterprise->save();


            //Seed Addons
            \Vanguard\Models\User\Addon::create([
                'code' => 'all',
                'name' => 'All',
                'per_month' => 10,
                'per_year' => 100,
                'is_special' => 1,
                'rowpos' => 1,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'bi',
                'name' => 'Business Intelligence',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 2,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'alert',
                'name' => 'ANA (Alerts & Notifications)',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 2,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'map',
                'name' => 'Geospatial Information',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 3,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'kanban',
                'name' => 'Kanban View',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 3,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'request',
                'name' => 'Data Collection (Request)',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 4,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'gantt',
                'name' => 'Gantt View',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 4,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'email',
                'name' => 'Email',
                'per_month' => 3,
                'per_year' => 30,
                'rowpos' => 5,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
            \Vanguard\Models\User\Addon::create([
                'code' => 'calendar',
                'name' => 'Calendar',
                'per_month' => 0.3,
                'per_year' => 3,
                'rowpos' => 5,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }
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
