<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableFieldLinksSeeder extends Seeder
{
    private $permissionsService;

    /**
     * TableFieldLinksSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
    )
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('role_id', '=', '1')->first();

        $table = Table::where('db_name', 'table_field_links')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_links',
                'name' => 'Table Field Links',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('icon', 'Icon', $table, $user);
        $this->create('link_pos', 'Position', $table, $user);
        $this->create('link_type', 'Link Type', $table, $user, 'String', 1);

        $this->create('link_display', 'Window', $table, $user);
        $this->create('popup_display', 'Default Display', $table, $user, 'String', 0);
        $this->create('show_sum', 'Show STATS', $table, $user, 'Boolean');
        $this->create('tooltip', 'Tooltip', $table, $user);
        $this->create('table_ref_condition_id', 'RC Name', $table, $user);
        $this->create('listing_field_id', 'Listing Field', $table, $user);
        $this->create('web_prefix', 'URL Prefix', $table, $user);
        $this->create('address_field_id', 'Web Address', $table, $user);
        $this->create('hide_empty_web', 'Hide icon if the cell in a row of the selected field for "Web Address" is empty', $table, $user, 'Boolean');
        $this->create('table_app_id', 'Linked App', $table, $user);
        $this->create('link_field_lat', ',Latitude', $table, $user);
        $this->create('link_field_lng', ',Longitude', $table, $user);
        $this->create('link_field_address', 'OR,Address', $table, $user);
        $this->create('always_available', 'Works w/o linked table sharing', $table, $user, 'Boolean');
        $this->create('add_record_limit', 'Max. number of records allowed to be added to the linked table through this link', $table, $user, 'Integer');
        $this->create('link_preview_fields', 'Info panel fields upon mouseover', $table, $user, 'String', 0, '', 'M-Select', 3);
        $this->create('link_preview_show_flds', 'Show field name(s)', $table, $user, 'Boolean');
        $this->create('email_addon_fields', 'Fields for emailing', $table, $user, 'String', 0, '', 'M-Select', 3);

        $this->create('payment_method_fld_id', 'Payment method', $table, $user, 'String');
        $this->create('payment_paypal_keys_id', 'Payment connections,PayPal', $table, $user, 'String');
        $this->create('payment_stripe_keys_id', 'Payment connections,Stripe', $table, $user, 'String');
        $this->create('payment_description_fld_id', 'Services / Goods', $table, $user, 'Integer');
        $this->create('payment_amount_fld_id', 'Amount to pay', $table, $user, 'Integer');
        $this->create('payment_customer_fld_id', 'Payer / Customer', $table, $user, 'Integer');
        $this->create('payment_history_payee_fld_id', 'Payment confirmation:,Payee', $table, $user, 'Integer');
        $this->create('payment_history_amount_fld_id', 'Payment confirmation:,Amount paid', $table, $user, 'Integer');
        $this->create('payment_history_date_fld_id', 'Payment confirmation:,Date', $table, $user, 'Integer');

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        $this->update($table, 'payment_paypal_keys_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'payment_stripe_keys_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_description_fld_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'payment_amount_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_customer_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_history_payee_fld_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'payment_history_amount_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_history_date_fld_id', ['is_table_field_in_popup'=>1]);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_params')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_params',
                'name' => 'Table Field Link Params',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('param', 'App,Parameter', $table, $user, 'String', 1);
        $this->create('value', 'Or,Value', $table, $user);
        $this->create('column_id', 'Or,Column', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_to_dcr')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_to_dcr',
                'name' => 'Table Field Link To Dcr',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('table_field_link_id', 'Item', $table, $user, 'String', 1);
        $this->create('status', 'Set Limit', $table, $user, 'Boolean');
        $this->create('add_limit', 'Number', $table, $user, 'Integer');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
    }

    /**
     * @param $field
     * @param $name
     * @param $table
     * @param $user
     * @param string $type
     * @param int $required
     * @param string $default
     * @param string $inp_type
     * @param int $max_rows
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $inp_type = 'Input', $max_rows = 1) {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inp_type,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'verttb_cell_height' => $max_rows,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        } else {
            $present->name = $name;
            $present->input_type = $inp_type;
            $present->f_type = $type;
            $present->f_default = $default;
            $present->f_required = $required;
            $present->verttb_cell_height = $max_rows;
            $present->save();
        }
    }

    /**
     * @param Table $table
     * @param string $field
     * @param array $params
     */
    private function update(Table $table, string $field, array $params)
    {
        $present = $table->_fields()->where('field', '=', $field)->first();
        if ($present) {
            $present->update($params);
        }
    }
}
