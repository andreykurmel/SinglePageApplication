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
    private $DDLRepository;
    private $refConditionRepository;
    private $viewRepository;

    /**
     * UsagesAndPaymentsSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     * @param DDLRepository $DDLRepository
     * @param TableRefConditionRepository $refConditionRepository
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService,
        DDLRepository $DDLRepository,
        TableRefConditionRepository $refConditionRepository,
        \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
    )
    {
        $this->permissionsService = $permissionsService;
        $this->DDLRepository = $DDLRepository;
        $this->refConditionRepository = $refConditionRepository;
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
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now(),
            ]);

            $this->create('user_id', 'User', $table->id, $user, 'User');
            $this->create('table_id', 'Table', $table->id, $user);
            $type_field = $this->create('usage_type', 'Type', $table->id, $user);
            $this->create('size', 'Size (MB)', $table->id, $user);
            $this->create('num_rows', 'Number of,Rows', $table->id, $user);
            $this->create('num_columns', 'Number of,Columns', $table->id, $user);
            $this->create('num_collaborators', 'Number of,Collaborators', $table->id, $user);
            $this->create('host', 'Host', $table->id, $user);
            $this->create('database', 'Database', $table->id, $user);
            $this->create('db_name', 'DB Name', $table->id, $user);

            $this->create('created_by', 'Created By', $table->id, $user, 'User');
            $this->create('created_on', 'Created On', $table->id, $user, 'Date Time');
            $this->create('modified_by', 'Modified By', $table->id, $user, 'User');
            $this->create('modified_on', 'Modified On', $table->id, $user, 'Date Time');

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

            $type_field->input_type = 'Selection';
            $type_field->ddl_id = $ddl->id;
            $type_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 0, ['user_id', 'host']);
        $this->viewRepository->addSys($table);



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
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
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
    }

    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $inpType = 'Input') {
        $present = $table->_fields->where('field', $field)->first();
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
