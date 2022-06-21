<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableBackupsSeeder extends Seeder
{
    private $permissionsService;
    private $service;
    private $viewRepository;

    /**
     * TableBackupsSeeder constructor.
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     * @param \Vanguard\Services\Tablda\HelperService $service
     * @param \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService,
        \Vanguard\Services\Tablda\HelperService $service,
        \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
    )
    {
        $this->permissionsService = $permissionsService;
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

        $table_sub = Table::where('db_name', 'table_backups')->first();

        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'table_backups',
                'name' => 'Table Backups',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);
        }

        //headers for 'User Connections'
        $this->create('name', 'Name', $table_sub, $user, 'String', 1);
        $this->create('user_cloud_id', 'Cloud', $table_sub, $user, 'String', 1);
        $this->create('is_active', 'Status', $table_sub, $user, 'Boolean');
        $this->create('day', 'Frequency', $table_sub, $user, 'String');
        $this->create('overwrite', 'Overwritten', $table_sub, $user, 'Boolean', 0, '', '* If turned OFF, a "_yyyymmdd" will be added to the end of saved files.');
        $this->create('root_folder', 'Subfolder', $table_sub, $user, 'String', 0, 'TablDA_AutoBackup', '* A subfolder will be added under "Dropbox/ Apps/ TablDA_AutoBackup" for Storage & Backup. If left empty,  the account`s username will be used for the subfolder.');
        $this->create('timezone', 'Timezone', $table_sub, $user, 'Timezone');
        $this->create('time', 'Time', $table_sub, $user, 'Time');
        $this->create('mysql', 'MySQL', $table_sub, $user, 'Boolean');
        $this->create('csv', 'CSV', $table_sub, $user, 'Boolean');
        $this->create('attach', 'Attachments', $table_sub, $user, 'Boolean');
        $this->create('ddl_attach', 'DDL Images', $table_sub, $user, 'Boolean', 0, '', '* If turned ON, the images for individual DDL options will be saved in the backup.');
        $this->create('notes', 'Note', $table_sub, $user, 'String');

        $this->create('bkp_email_field_id','Recipients', $table_sub, $user);
        $this->create('bkp_email_field_static','And', $table_sub, $user);
        $this->create('bkp_email_subject','Subject', $table_sub, $user);
        $this->create('bkp_addressee_field_id','Addressee', $table_sub, $user);
        $this->create('bkp_addressee_txt','Addressee', $table_sub, $user);
        $this->create('bkp_email_message','Opening Message', $table_sub, $user);

        $this->create('created_by', 'Created By', $table_sub, $user, 'User');
        $this->create('created_on', 'Created On', $table_sub, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table_sub, $user, 'User');
        $this->create('modified_on', 'Modified On', $table_sub, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id);
        $this->viewRepository->addSys($table_sub);
    }

    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $tooltip = '') {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'tooltip' => $tooltip,
                'tooltip_show' => $tooltip ? 1 : 0,
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
            $present->tooltip = $tooltip;
            $present->tooltip_show = $tooltip ? 1 : 0;
            $present->save();
        }
    }
}
