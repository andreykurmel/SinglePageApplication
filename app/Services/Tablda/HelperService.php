<?php


namespace Vanguard\Services\Tablda;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Ramsey\Uuid\Uuid;
use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\DataSetPermissions\TableColumnGroup;
use Vanguard\Models\DataSetPermissions\TablePermission;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\User\UserConnection;
use Vanguard\Modules\CloudBackup\GoogleApiModule;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\User;

class HelperService
{
    protected static $admin_ids = [];

    public $system_fields = [
        'id',
        'row_hash',
        'row_order',
        'refer_tb_id',
        'request_id',
        'created_on',
        'created_by',
        'modified_on',
        'modified_by'
    ];
    public $c2m2_fields = [
        'created_on',
        'created_by',
        'modified_on',
        'modified_by'
    ];

    public $system_tables_for_all = [
        'user_subscriptions',
        'sum_usages',
        'payments',
        'plan_features',
        'plans_view',
        'fees',
        'unit_conversion',
        'user_connections',
        'user_clouds',
        'units',
        'correspondence_apps',
        'correspondence_tables',
        'correspondence_fields',
        'correspondence_stim_3d',
    ];
    public $myaccount_tables = [
        'user_subscriptions',
        'sum_usages',
        'payments',
        'plan_features'
    ];
    public $info_tables = [
        'fees',
        'plans_view'
    ];
    public $support_tables = [
        'unit_conversion',
        'user_connections',
        'user_clouds',
        'units',
    ];
    public $correspondence_tables = [
        'correspondence_apps',
        'correspondence_tables',
        'correspondence_fields',
        'correspondence_stim_3d',
    ];
    public $stim_views = [
        'stim_app_views',
    ];

    public $sys_row_hash = [
        'cf_temp' => 'cf_temp',
    ];

    public $hidden_tablda_app = '/_tablda_apps/';
    public $def_col_width = 100;
    public $recalc_table_formulas_job = 5000;
    public $no_redirect_subdomains = ['public','blog'];
    public $public_subdomain = 'public';
    public $cur_subdomain = '';
    public $use_visitor_scope = true;

    /**
     * HelperService constructor.
     * @param string|null $host
     */
    public function __construct(string $host = null)
    {
        $host = $host ?: $_SERVER['HTTP_HOST'] ?? null;
        if (!empty($host)) {
            if( preg_match('/([^\/\.]*)\.'.config('app.url_domain').'/i', $host, $subdomain) && $subdomain[1] != 'www' ) {
                $this->cur_subdomain = $subdomain[1];
                $this->cur_subdomain = strtolower($this->cur_subdomain);

                //$this->use_visitor_scope = $this->cur_subdomain == 'public' || !auth()->id();
            }
        }
    }

    /**
     * @param $request
     * @return string
     */
    public static function webHashFromReq($request)
    {
        $reqparams = is_array($request) ? $request : $request->all();
        return ($reqparams['special_params']['view_hash'] ?? '')
            ?: ($reqparams['special_params']['is_folder_view'] ?? '')
                ?: ($reqparams['special_params']['dcr_hash'] ?? '');
    }

    /**
     * @return string
     */
    public static function viewPartsDef()
    {
        return '["tab-list-view","tab-settings","tab-map-add","tab-bi-add","tab-dcr-add"]';
    }

    /**
     * @return string
     */
    public function isVisitingUrl()
    {
        return $this->matchReferer('visiting');
    }

    /**
     * @param string $match
     * @param string $path
     * @return string
     */
    public function matchReferer(string $match, string $path = '')
    {
        $val = $path ?: ($_SERVER['HTTP_REFERER'] ?? '') ?: ($_SERVER['REQUEST_URI'] ?? '');
        return preg_match('/\/'.$match.'/i', $val) ? $val : '';
        //return preg_match('/'.$match.'/i', \Route::current()->uri());
    }

    /**
     * @param $input_type
     * @return bool
     */
    public static function isMSEL($input_type)
    {
        return in_array($input_type, ['M-Select','M-Search','M-SS']);
    }

    /**
     * @param TablePermission $permis
     * @param array $row
     * @param Table|null $table
     * @return string
     */
    public static function dcrStatus(TablePermission $permis, array $row, Table $table = null)
    {
        $fld = null;
        if ($permis->dcr_record_status_id && $permis->dcr_record_allow_unfinished) {
            $fld = $table
                ? $table->_fields->where('id', '=', $permis->dcr_record_status_id)->first()
                : (new TableFieldRepository())->getField($permis->dcr_record_status_id);
        }
        return $fld ? ($row[$fld->field] ?? 'Submitted') : 'Submitted';
    }

    /**
     * @param TablePermission $permis
     * @param array $row
     * @param Table|null $table
     * @return string
     */
    public static function dcrPref(TablePermission $permis, array $row, Table $table = null)
    {
        switch (self::dcrStatus($permis, $row, $table)) {
            case 'Saved': return 'dcr_save_';
            case 'Updated': return 'dcr_upd_';
            case 'Submitted':
            default: return 'dcr_';
        }
    }

    /**
     * @return string
     */
    public function subiconOnSubDomain()
    {
        $usr = $this->cur_subdomain ? User::where('subdomain', '=', $this->cur_subdomain)->first() : null;
        return $usr ? $usr->sub_icon : '';
    }

    /**
     * Set User as force guest if they view public table.
     *
     * @param int|null $user_id
     * @return int|null
     */
    public function forceGuestForPublic(int $user_id = null)
    {
        return ($this->cur_subdomain == $this->public_subdomain ? null : $user_id);
    }

    /**
     * Generate Transaction ID.
     *
     * @return string
     */
    public function genTransationId()
    {
        return date('Ymd') . '-' . Uuid::uuid4();
    }

    /**
     * Create object from table which is used in 'jstree'.
     *
     * @param Table $table
     * @param $link_type
     * @param string $folder_path
     * @param int $parent_folder_id
     * @param string $link_class
     * @return array
     */
    public function getTableObjectForMenuTree(Table $table, $link_type, $folder_path = '', $parent_folder_id = 0, $link_class = '') {
        return [
            'init_name' => $table->name,
            'text' => $table->name,
            'icon' => 'fa fa-'.($table->_in_apps ? 'cloud' : $link_type),
            'state' => [
                'selected' => false,
            ],
            'children' => [],
            'li_attr' => [
                'data-id' => $table->id,
                'data-type' => $link_type == 'table' ? 'table' : 'link',
                'data-object' => $table->toArray(),
                'data-user_id' => $table->user_id,
                'data-parent_id' => $parent_folder_id,
                'data-in_shared' => (int)$table->_in_shared,
                'data-copy-settings' => (object)[],
            ],
            'a_attr' => [
                'href' => $folder_path.$table->name,
                'class' => $link_class
            ]
        ];
    }

    /**
     * Create object from folder which is used in 'jstree'.
     *
     * @param Folder $folder
     * @param array $children
     * @param string $path
     * @param bool $only_name
     * @param string $folder_class
     * @return array
     */
    public function getFolderObjectForMenuTree(Folder $folder, $children = [], $path = '', $only_name = false, $folder_class = '') {
        if (empty($children)) {
            $children = [
                'sub_tables' => [],
                'tree' => [],
                'folders' => 0,
                'tables' => 0
            ];
        }
        $URL = URL::getRequest()->ajax() ? URL::previous() : URL::current();
        $is_opened = preg_match('#'.$path.'#i', $URL) ?: !!$folder->is_opened;
        
        $obj = [
            'init_name' => $folder->name,
            'text' => $folder->name . ($only_name ? '' : ' ('.$children['folders'].'/'.$children['tables'].')'),
            'icon' => $this->getFolderIcon($folder),
            'state' => [
                'opened' => $is_opened,
                'selected' => false,
            ],
            'children' => $children['sub_tables'],
            'li_attr' => [
                'data-id' => $folder->id,
                'data-type' => 'folder',
                'data-object' => $folder->toArray(),
                'data-user_id' => $folder->user_id,
                'data-parent_id' => $folder->parent_id,
                'data-for_shared_user_id' => $folder->for_shared_user_id,
                'data-in_shared' => (int)$folder->in_shared,
            ],
            'a_attr' => [
                'href' => $path,
                'class' => $folder_class
            ]
        ];

        if (!empty($children['tree'])) {
            $obj['children'] = array_merge($obj['children'], $children['tree']);
        }

        return $obj;
    }

    /**
     * getFolderIcon
     *
     * @param Folder $folder
     * @return string
     */
    private function getFolderIcon(Folder $folder) {
        if ($folder->is_system) {
            switch ($folder->name) {
                case 'SHARED': $link = 'fa fa-share-alt-square';
                    break;
                case 'TRANSFERRED': $link = 'fas fa-sync-alt';
                    break;
                case 'APPs': $link = 'fas fa-th';
                    break;
                default: $link = $folder->is_opened ? 'fa fa-folder-open' : 'fa fa-folder';
            }
        } else {
            $link = $folder->is_opened ? 'fa fa-folder-open' : 'fa fa-folder';
        }
        return $link;
    }

    /**
     * Get AppUrl with user's subdomain.
     *
     * @param $user
     * @param $cur_subdomain
     * @return mixed|string
     */
    public function getUsersUrl($user, $cur_subdomain) {
        $user_subdomain = $user && $user->subdomain ? $user->subdomain : '';
        $result_subdomain = $cur_subdomain == 'public' ? $this->public_subdomain : $user_subdomain;

        //ignore redirect for 'data request' urls
        if (preg_match('/^\/dcr\//i', $_SERVER['REQUEST_URI'])) {
            $result_subdomain = '';
        }

        $url = strtolower(config('app.url'));
        return $this->getUrlWithSubdomain($result_subdomain);
    }

    /**
     * Get AppUrl with selected subdomain.
     *
     * @param $subdomain
     * @return string
     */
    public function getUrlWithSubdomain($subdomain) {
        $url = strtolower(config('app.url'));
        $res = preg_replace('/\/\/(www\.)?/i','//$1'.$subdomain.'.', $url);
        return $subdomain ? $res : $url;
    }

    /**
     * Remove system fields from array (all fields start from '_' symbol)
     *
     * @param array $data
     * @param bool $low
     * @return array
     */
    public function delSystemFields(Array $data, bool $low = false) {
        foreach ($data as $key => $val) {
            if (preg_match('/[_]/i', $key[0])) {
                unset($data[$key]);
            }
            elseif (!$low && in_array($key, $this->system_fields)) {
                unset($data[$key]);
            }
            elseif (!$low && $key == 'user_id' && empty($data[$key])) {
                $data[$key] = auth()->id();
            }
        }
        return $data;
    }

    /**
     * Remove null fields from array
     *
     * @param array $data
     * @return array
     */
    public function delNullFields(Array $data) {
        foreach ($data as $key => $val) {
            if (is_null($val)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * Get fields as only names for User.
     *
     * @param bool $as_string
     * @param bool $short
     * @param string $relTable
     * @return array|string
     */
    public function onlyNames($as_string = true, $short = false, $relTable = 'users') {
        $arr = [
            'email',
            'username',
            'first_name',
            'last_name',
        ];
        if (!$short) {
            $arr = array_merge($arr, [
                'id',
                'avatar',
                'avail_credit',
                'recurrent_pay',
                'renew',
            ]);
        }

        if ($relTable) {
            foreach ($arr as &$i) {
                $i = $relTable . '.' . $i;
            }
        }

        if ($as_string) {
            return implode(',', $arr);
        } else {
            return $arr;
        }
    }

    /**
     * @param Table|null $table
     * @return array
     */
    public function getModified(Table $table = null){
        if ($table && $table->is_system == 2) {
            return [];
        } else {
            return [
                'modified_by' => auth()->id(),
                'modified_on' => now()->toDateTimeString()
            ];
        }
    }

    /**
     * @param Table|null $table
     * @return array
     */
    public function getCreated(Table $table = null){
        if ($table && $table->is_system == 2) {
            return [];
        } else {
            return [
                'created_by' => auth()->id(),
                'created_on' => now()->toDateTimeString()
            ];
        }
    }

    /**
     * @param array $from
     * @param array $to
     * @return array
     */
    public function setCreatedFromModif(array $from, array $to){
        return array_merge($from, [
            'created_by' => $to['modified_by'] ?? null,
            'created_on' => $to['modified_on'] ?? null
        ]);
    }

    /**
     * Config connection for remote databases.
     *
     * @param $conn
     * @param string $connName
     * @return mixed
     */
    public function configRemoteConnection($conn, $connName = 'mysql_remote') {
        if (!is_array($conn)) {
            $conn = (new UserConnRepository())->getUserConn($conn);
        }
        Config::set('database.connections.'.$connName.'.host', $conn['host']);
        Config::set('database.connections.'.$connName.'.username', $conn['login']);
        Config::set('database.connections.'.$connName.'.password', $conn['pass']);
        Config::set('database.connections.'.$connName.'.database', $conn['db']);
        return $conn;
    }

    /**
     * Get DB Connection for Table.
     *
     * @param Table $table
     * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface
     */
    public function getConnectionForTable(Table $table) {
        if ($table->source == 'remote') {
            $conn = $this->configRemoteConnection($table->connection_id);
        }
        return DB::connection(($table->source == 'remote' ? 'mysql_remote' : ($table->is_system ? 'mysql' : 'mysql_data')));
    }

    /**
     * Merge 2 byte string ('00101' and '10011' => '10111')
     *
     * @param $str
     * @param $add
     * @return string
     */
    public function mergeByteStrings(String $str, String $add) {
        for ($i = 0; $i < strlen($str); $i++) {
            $str[$i] = (int)($str[$i] || $add[$i]);
        }
        return $str;
    }

    /**
     * get Name for UserGroup which will applied for Visitors.
     *
     * @return string
     */
    public function getVisitorRightName() {
        return 'Visiting';
    }

    /**
     * get Name for UserGroup which will applied for Visitors.
     *
     * @return string
     */
    public function getTableViewSysName() {
        return 'Visiting';
    }

    /**
     * Get Plan Level by code.
     *
     * @param string $code
     * @return int
     */
    public function planLvL(string $code) {
        switch ($code) {
            case 'basic': return 1;
            case 'advanced': return 2;
            case 'enterprise': return 3;
        }
        return 0;
    }

    /**
     * Get Query STRING With Bindings
     *
     * @param $query
     * @return \Illuminate\Database\Query\Expression
     */
    public function getQueryWithBindings($query) {
        $res = vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
        return DB::raw($res);
    }

    /**
     * Parse Pasted Data Row (String) to Row Cells (Array).
     *
     * @param string $row
     * @return array
     */
    public function pastedDataParser(string $row) {
        $row_cells = [''];
        $cell_idx = 0;
        $locked = '';
        for ($i = 0; $i < strlen($row); $i++) {

            //if we get ' or " in string -> then all symbols is one cell until we get another ' or "
            if (!$locked && ($row[$i] == "'" || $row[$i] == '"')) {
                $locked = $row[$i];
                continue;
            }
            //get another ' or "
            if ($row[$i] == $locked) {
                $locked = '';
                continue;
            }

            //if locked or char is not divider -> add to cell
            if ($locked || !preg_match('/[\s,;]/', $row[$i])) {
                $row_cells[$cell_idx] .= $row[$i];
            }
            //if char is divider:
            else {
                //if we have data in cell -> start new cell
                if ($row_cells[$cell_idx] != '') {
                    $cell_idx++;
                    $row_cells[$cell_idx] = '';
                }
                //else just skip all dividers
            }
        }

        //remove last col if it is empty
        if ($row_cells[$cell_idx] == '') {
            unset($row_cells[$cell_idx]);
        }

        return $row_cells;
    }

    /**
     * Parse Google Sheet.
     *
     * @param string $g_sheet_link
     * @param string $g_sheet_name
     * @return array
     */
    public function parseGoogleSheet(string $g_sheet_link, string $g_sheet_name, string $token_json = null)
    {
        $client = (new GoogleApiModule())->clientWithCredentialsOrPublic($token_json);
        $service = new \Google_Service_Sheets($client);

        $arr = [];
        preg_match('#/d/([^/]+)/#i', $g_sheet_link, $arr);
        $spreadsheetId = $arr[1] ?? null;

        $response = $service->spreadsheets_values->get($spreadsheetId, $g_sheet_name);
        return $response->getValues();
    }

    /**
     * convertSysField
     *
     * @param Table $table
     * @param $field
     * @param bool $for_graph
     * @return string
     */
    public function convertSysField(Table $table, $field, $for_graph = false) {
        if ($table->db_name == 'sum_usages') {
            switch ($field) {
                case 'user_id': $field = ($for_graph ? 'u.email' : 'user_id');
                    break;
                case 'table_id': $field = 'name';
                    break;
                case 'size': $field = ($for_graph ? '(u.num_rows * u.num_columns * u.avg_row_length)/(1024*1024)' : 'num_rows');
                    break;
                case 'host':
                case 'database': $field = 'source';
                    break;
            }
        }
        if ($table->db_name == 'user_subscriptions') {
            switch ($field) {
                case 'plan_id': $field = 'plan_code';
                    break;
                case 'add_bi':
                case 'add_map':
                case 'add_alert':
                case 'add_kanban':
                case 'add_gantt':
                case 'add_email':
                case 'add_calendar':
                case 'add_request': $field = 'plan_code';
                    break;
                case 'avail_credit': $field = 'avail_credit';
                    break;
                case 'renew': $field = 'renew';
                    break;
                case 'recurrent_pay': $field = 'recurrent_pay';
                    break;
            }
        }
        if ($table->db_name == 'correspondence_tables') {
            switch ($field) {
                case 'correspondence_app_id': $field = 'name';
                    break;
            }
        }
        if ($table->db_name == 'correspondence_fields') {
            switch ($field) {
                case 'correspondence_app_id': $field = 'name';
                    break;
                case 'correspondence_table_id': $field = 'app_table';
                    break;
                case '_data_table_id': $field = 'data_table';
                    break;
            }
        }
        return $field;
    }

    /**
     * Get Fields for showing in emails and filter them if present ColGroup.
     *
     * @param Table $table
     * @param TableColumnGroup $col_group
     * @return mixed
     */
    public function getFieldsArrayForNotification(Table $table, TableColumnGroup $col_group = null) {
        $tbcolgroup = $col_group && $col_group->table_id == $table->id ? $col_group : $table->_visitor_column_group;
        $fldtb = (new TableField())->getTable();

        return $tbcolgroup->_fields()
            ->whereNotIn('field', $this->system_fields)
            ->whereNotIn('f_type', ['Attachment'])
            ->orderBy($fldtb.'.order')
            ->orderBy($fldtb.'.id')
            ->select([$fldtb.'.id', $fldtb.'.name', $fldtb.'.field', $fldtb.'.unit_display', $fldtb.'.unit'])
            ->get()
            ->toArray();
    }

    /**
     * @param array $fields_arr
     * @return int
     */
    public function fldsArrHasUnit(array $fields_arr) {
        return collect($fields_arr)->filter(function ($arr) {
            return $arr['unit'] || $arr['unit_display'];
        })->count();
    }

    /**
     * @param $arr
     * @param $key
     * @param $def
     * @return mixed
     */
    public function arrayDef($arr, $key, $def)
    {
        if (isset($arr[$key]) && strlen($arr[$key])) {
            return $arr[$key];
        } else {
            return $def;
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isNumberType(string $type) : bool
    {
        return in_array($type, ['Decimal','Currency','Percentage','Duration','Integer']);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function zeroIsNull(string $type) : bool
    {
        return in_array($type, ['Boolean','Date','Date Time','User']);
    }

    /**
     * Null return is IMPORTANT.
     *
     * @param string $type
     * @return mixed
     */
    public function getDefaultOnType(string $type)
    {
        switch ($type) {
            //Strings
            case 'Attachment':
            case 'Formula':
            case 'Address':
            case 'String':
            case 'Color':
            case 'App':
            case 'User':
            case 'RefTable':
            case 'RefField':
                return '';

            //Dates
            case 'Date':
                return '0000-00-00';
            //Date Times
            case 'Date Time':
                return '0000-00-00 00:00:00';

            //Numbers
            case 'Decimal':
            case 'Currency':
            case 'Percentage':
            case 'Duration':
            case 'Integer':
                return null;

            //Specials
            case 'Auto Number':
            case 'Star Rating':
            case 'Progress Bar':
            case 'Boolean':
                return 0;

            default:
                return '';
        }
    }

    /**
     * @param string $str
     * @param string $rep
     * @return mixed
     */
    public function todb(string $str, string $rep = '')
    {
        $fixed = preg_replace('/[^\w\d]/i', $rep, $str);
        return strtolower( $fixed );
    }

    /**
     * @param array $arr
     * @param array $keys
     * @return array
     */
    public function filter_keys(array $arr, array $keys)
    {
        return array_intersect_key( $arr, array_flip( $keys ) );
    }

    /**
     * Parse recipients from row
     * @param string $recipients
     * @return array
     */
    public function parseRecipients(string $recipients = null) {
        $recipients = preg_replace('/[\s,]+/i', ';', $recipients);
        $recipients = preg_replace('/;+/i', ';', $recipients);
        $recipients = explode(';', $recipients);
        $emails = [];
        foreach ($recipients as $elem) {
            //if is correct email
            if ($elem && filter_var($elem, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $elem;
            }
        }
        return $emails;
    }

    /**
     * @param Table $table
     * @param array $row
     * @return array
     */
    public function prepareRowVals(Table $table, array $row)
    {
        foreach ($table->_fields as $hdr) {
            if (isset($row[$hdr->field])) {
                if ($hdr->f_type == 'Boolean') {
                    $row[$hdr->field] = $row[$hdr->field] ? 'Yes' : 'No';
                }
            }
        }
        return [$row];
    }

    /**
     * @return array
     */
    public static function adminIds()
    {
        if (!self::$admin_ids) {
            self::$admin_ids = User::where('role_id', '=', 1)
                ->get()
                ->pluck('id')
                ->toArray();
        }
        return self::$admin_ids;
    }

    /**
     * @param string $date
     * @param string $time
     * @param string $timezone
     * @param string $format
     * @return string
     */
    public static function timeToUTC(string $date, string $time, string $timezone, string $format = '')
    {
        $tmz = self::getTMZ($date, $time, $timezone);
        $tmz->setTimezone( new \DateTimeZone("UTC") );
        return $tmz->format($format ?: "Y-m-d H:i:s");
    }

    /**
     * @param string $date
     * @param string $time
     * @param string $timezone
     * @param string $format
     * @return string
     */
    public static function timeToLocal(string $date, string $time, string $timezone, string $format = '')
    {
        $tmz = self::getTMZ($date, $time, "UTC");
        $tmz->setTimezone( new \DateTimeZone($timezone) );
        return $tmz->format($format ?: "Y-m-d H:i:s");
    }

    /**
     * @param string $date
     * @param string $time
     * @param string $timezone
     * @return \DateTime
     */
    protected static function getTMZ(string $date, string $time, string $timezone)
    {
        $date = $date ? explode('-', $date) : [];
        $time = $time ? explode(':', $time) : [];
        $tmz = new \DateTime();
        $tmz->setTimezone( new \DateTimeZone($timezone) );
        $date ? $tmz->setDate($date[0] ?? 0,$date[1] ?? 0, $date[2] ?? 0) : null;
        $time ? $tmz->setTime($time[0] ?? '00',$time[1] ?? '00') : null;
        return $tmz;
    }

    /**
     * @param $val
     * @return null
     */
    public static function sanitizeNull($val)
    {
        switch (strtolower($val)) {
            case 'null': return null;
            case 'undefined': return null;
            default: return $val;
        }
    }
}