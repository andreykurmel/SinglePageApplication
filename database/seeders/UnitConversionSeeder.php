<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;

class UnitConversionSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $DDLRepository;
    private $service;
    private $viewRepository;

    /**
     * UnitConversionSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new \Vanguard\Services\Tablda\Permissions\TablePermissionService();
        $this->permissionsRepository = new \Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository();
        $this->DDLRepository = new \Vanguard\Repositories\Tablda\DDLRepository();
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
        $user = User::where('role_id', '=', '1')->first();

        $table = Table::where('db_name', 'unit_conversion')->first();

        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'unit_conversion',
                'name' => 'Unit Conversions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);

            //headers for 'Fees'

            $op_field = $this->create('operator', 'Operator', $table, $user);

            //DDL for 'Type'
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table->id,
                'name' => 'OperatorDDL',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Multiply',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Divide',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Add',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Substract',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Square',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Square Root',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Formula',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Formula Reverse',
            ]);

            $op_field->input_type = 'S-Select';
            $op_field->ddl_id = $ddl->id;
            $op_field->save();

            //Seed Data

            \Illuminate\Support\Facades\DB::table('unit_conversion')->insert([
                'from_unit' => 'Km',
                'to_unit' => 'Miles',
                'operator' => 'Multiple',
                'factor' => 1.6,
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now()
            ]);
            \Illuminate\Support\Facades\DB::table('unit_conversion')->insert([
                'from_unit' => 'Miles',
                'to_unit' => 'Km',
                'operator' => 'Divide',
                'factor' => 1.6,
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now()
            ]);
        }

        $this->create('property', 'Property', $table, $user);
        $this->create('from_unit', 'From Unit', $table, $user);
        $this->create('to_unit', 'To Unit', $table, $user);
        $this->create('operator', 'Operator', $table, $user);
        $this->create('factor', 'Factor', $table, $user);
        $this->create('formula', 'UC Formula,Forward', $table, $user, 'String', 0, '', 'Formula');
        $this->create('formula_reverse', 'UC Formula,Reverse', $table, $user, 'String', 0, '', 'Formula');

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);
    }

    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $inp_type = 'Input') {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            $present = TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inp_type,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        } else {
            $present->name = $name;
            $present->f_type = $type;
            $present->f_default = $default;
            $present->f_required = $required;
            $present->input_type = $inp_type;
            $present->save();
        }
        return $present;
    }
}
