<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class UserInvitationsSeeder extends Seeder
{
    private $permissionsService;
    private $service;
    private $viewRepository;

    /**
     * UserInvitationsSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     * @param \Vanguard\Services\Tablda\HelperService $service
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

        $table = Table::where('db_name', 'user_invitations')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'user_invitations',
                'name' => 'User Invitations',
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

        //headers
        $this->create('email', 'Email', $table, $user);
        $this->create('date_send', 'Date,Sent', $table, $user, 'Date');
        $this->create('date_accept', 'Date,Accepted', $table, $user, 'Date');
        $this->create('rewarded', 'Rewarded credit', $table, $user, 'Currency');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
    }

    private function create($field, $name, $table, $user, $type = 'String') {
        if (!$table->_fields->where('field', $field)->count()) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'f_type' => $type,
                'created_by' => $user->id,
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now()
            ]);
        }
    }
}
