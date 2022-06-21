<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;

class UsagesAndPaymentsSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $DDLRepository;
    private $refConditionRepository;
    private $viewRepository;

    /**
     * UsagesAndPaymentsSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new \Vanguard\Services\Tablda\Permissions\TablePermissionService();
        $this->permissionsRepository = new \Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository();
        $this->DDLRepository = new \Vanguard\Repositories\Tablda\DDLRepository();
        $this->refConditionRepository = new TableRefConditionRepository();
        $this->viewRepository = new \Vanguard\Repositories\Tablda\TableViewRepository();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('role_id', '=', '1')->first();

        //headers for 'Usages'

        $table = Table::where('db_name', 'sum_usages')->first();

        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'sum_usages',
                'name' => 'Usage',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);

            $this->create('user_id', 'User', $table, $user, 'User');
            $this->create('table_id', 'Table', $table, $user);
            $type_field = $this->create('usage_type', 'Type', $table, $user);
            $this->create('size', 'Size (MB)', $table, $user);
            $this->create('attachments_size', 'Attachments Size', $table, $user);
            $this->create('num_rows', 'Number of,Rows', $table, $user);
            $this->create('num_columns', 'Number of,Columns', $table, $user);
            $this->create('num_collaborators', 'Number of,Collaborators', $table, $user);
            $this->create('host', 'Host', $table, $user);
            $this->create('database', 'Database', $table, $user);
            $this->create('db_name', 'DB Name', $table, $user);

            $this->create('created_by', 'Created By', $table, $user, 'User');
            $this->create('created_on', 'Created On', $table, $user, 'Date Time');
            $this->create('modified_by', 'Modified By', $table, $user, 'User');
            $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

            //DDL for 'Usage'
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table->id,
                'name' => 'UsageDDL',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Private',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Semi-Private',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Public',
            ]);

            if ($type_field) {
                $type_field->input_type = 'S-Select';
                $type_field->ddl_id = $ddl->id;
                $type_field->save();
            }
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 0, ['user_id', 'host', 'database', 'db_name']);
        $this->viewRepository->addSys($table);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



        //headers for 'Payments'

        $table = Table::where('db_name', 'payments')->first();

        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'payments',
                'name' => 'Transactions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);
        }

        $this->create('user_id', 'User', $table, $user, 'User');
        $this->create('transaction_id', 'Transaction ID', $table, $user);
        $this->create('type', 'Type', $table, $user);
        $this->create('amount', 'Amount', $table, $user, 'Currency');
        $this->create('from', 'From', $table, $user);
        $this->create('from_details', 'From Details', $table, $user);
        $this->create('to', 'To', $table, $user);
        $this->create('due_date', 'Date', $table, $user, 'Date');
        $this->create('notes', 'Notes', $table, $user);

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 1, ['user_id']);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);
    }

    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $inpType = 'Input') {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inpType,
                'f_type' => $type,
                'f_required' => $required,
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now()
            ]);
        } else {
            $present->input_type = $inpType;
            $present->f_type = $type;
            $present->f_required = $required;
            $present->save();
        }
    }
}
