<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class UnitsSeeder extends Seeder
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

        $table_sub = Table::where('db_name', 'units')->first();

        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'units',
                'name' => 'Units',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);

            //headers for 'Units'

            $prop_field = $this->create('property','Property', $table_sub->id, $user, 'String', 1);
            $type_field = $this->create('type','Type', $table_sub->id, $user, 'String', 1);
            $this->create('name','Name', $table_sub->id, $user, 'String', 1);
            $this->create('notes','Notes', $table_sub->id, $user);

            $this->create('created_by','Created By', $table_sub->id, $user, 'User');
            $this->create('created_on','Created On', $table_sub->id, $user, 'Date Time');
            $this->create('modified_by','Modified By', $table_sub->id, $user, 'User');
            $this->create('modified_on','Modified On', $table_sub->id, $user, 'Date Time');

            //DDL for 'Property'
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table_sub->id,
                'name' => 'Property',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Length',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Force',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Mass',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Speed',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Work',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Stress',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Density',
            ]);

            $prop_field->input_type = 'Selection';
            $prop_field->ddl_id = $ddl->id;
            $prop_field->save();



            //DDL for 'Type'
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table_sub->id,
                'name' => 'Type',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Imperial',
            ]);
            $this->DDLRepository->addDDLItem([
                'ddl_id' => $ddl->id,
                'option' => 'Metric',
            ]);

            $type_field->input_type = 'Selection';
            $type_field->ddl_id = $ddl->id;
            $type_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);




        //Seed Data

        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Stress',
            'type' => 'Imperial',
            'name' => 'psf',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Stress',
            'type' => 'Imperial',
            'name' => 'ksi',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Length',
            'type' => 'Imperial',
            'name' => 'in.',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Length',
            'type' => 'Imperial',
            'name' => 'inch',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Length',
            'type' => 'Imperial',
            'name' => 'ft',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Length',
            'type' => 'Imperial',
            'name' => 'miles',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Length',
            'type' => 'Imperial',
            'name' => 'foot',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Length',
            'type' => 'Metric',
            'name' => 'km',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Force',
            'type' => 'Imperial',
            'name' => 'kips',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Force',
            'type' => 'Imperial',
            'name' => 'lbs',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Density',
            'type' => 'Imperial',
            'name' => 'pcf',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
        \Illuminate\Support\Facades\DB::table('units')->insert([
            'property' => 'Density',
            'type' => 'Imperial',
            'name' => 'pci',
            'created_by' => $user->id,
            'created_name' => $user->first_name . ' ' . $user->last_name,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_name' => $user->first_name . ' ' . $user->last_name,
            'modified_on' => now()
        ]);
    }

    private function create($field, $name, $table_id, $user, $type = 'String', $required = 0) {
        return TableField::create([
            'table_id' => $table_id,
            'field' => $field,
            'name' => $name,
            'f_type' => $type,
            'f_required' => $required,
            'created_by' => $user->id,

            'created_on' => now(),
            'modified_by' => $user->id,

            'modified_on' => now()
        ]);
    }
}
