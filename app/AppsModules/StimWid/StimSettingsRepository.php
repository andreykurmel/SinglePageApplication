<?php

namespace Vanguard\AppsModules\StimWid;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Correspondences\CorrespField;
use Vanguard\Models\Correspondences\CorrespStim3D;
use Vanguard\Models\Correspondences\CorrespTable;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\Singletones\AuthUserSingleton;

class StimSettingsRepository
{
    /**
     * @var Collection
     */
    protected static $settings;
    /**
     * @var Collection
     */
    protected static $inherits;
    /**
     * @var CorrespApp
     */
    protected static $app;

    /**
     * StimSettingsRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return Collection
     */
    public function get()
    {
        if (empty(self::$settings)) {
            self::$settings = $this->load();
        }
        return self::$settings;
    }

    /**
     * @param string $data_table
     * @return string
     */
    public function findAppTable(string $data_table)
    {
        $first = $this->inheritSettings()
            ->where('data_table', '=', $data_table)
            ->first();

        return $first ? $first->app_table : '';
    }

    /**
     * @param string $field
     * @param $search
     * @return mixed
     */
    public function getTableBy(string $field, $search)
    {
        return CorrespTable::where($field, '=', $search)->first();
    }

    /**
     * @param string $app_table
     * @param string $inherit_type
     * @param string $matcher
     * @param bool $full
     * @return mixed|string
     */
    public function findInheritTb(string $app_table, string $inherit_type, string $matcher = '3d', bool $full = false)
    {
        $inherit_type = strtolower($inherit_type);

        $db_table = $this->inheritSettings()
            ->filter(function ($item) use ($app_table) {
                return preg_match('/^'.$app_table.'$/i', $item->app_table);//case insensitive search
            })
            ->first();

        $first = $this->inheritSettings()
            ->filter(function ($item) use ($inherit_type, $matcher) {
                return preg_match('/'.$matcher.':'.$inherit_type.'/i',$item->link_field_type);
            });
        if ($db_table) {
            $first = $first->where('link_table_db', '=', $db_table->data_table);
        }
        $first = $first->first();

        return $full ? $first : ($first ? $first->app_table : '');
    }

    /**
     * @param string $matcher
     * @return string
     */
    public function findMasterTb(string $matcher)
    {
        $first = $this->inheritSettings()
            ->filter(function ($item) use ($matcher) {
                return preg_match('/'.$matcher.'/i',$item->link_field_type);
            })
            ->first();

        return $first ? $first->app_table : '';
    }

    /**
     * @param string $popup_type
     * @return string
     */
    public function findPopupTb(string $popup_type)
    {
        $popup_type = strtolower($popup_type);
        $first = $this->inheritSettings()
            ->filter(function ($item) use ($popup_type) {
                return preg_match('/popup:'.$popup_type.'/i',$item->link_field_type);
            })
            ->first();
        return $first ? $first->app_table : '';
    }

    /**
     * @return Collection
     */
    protected function inheritSettings()
    {
        if (empty(self::$inherits)) {
            $app = $this->curApp();
            self::$inherits = CorrespField::where('correspondence_fields.correspondence_app_id', $app->id)
                ->join('correspondence_tables as ct', 'ct.id', '=', 'correspondence_table_id')
                ->select([
                    'ct.app_table',
                    'ct.data_table',
                    'ct.options as table_options',
                    'correspondence_fields.options',
                    'correspondence_fields.link_table_db',
                    'correspondence_fields.link_field_db',
                    'correspondence_fields.link_field_type',
                    'correspondence_fields.id',
                ])
                ->get();
        }
        return self::$inherits;
    }

    /**
     * @return CorrespApp
     */
    protected function curApp()
    {
        if (empty(self::$app)) {
            self::$app = CorrespApp::where('code', '=', 'stim_3d')->first();
        }
        return self::$app;
    }

    /**
     * @return Collection
     */
    protected function load()
    {
        $auth = app()->make(AuthUserSingleton::class);
        $user_id_and_groups = $auth->getUserIdAndUnderscoreGroups(true);
        if (!$auth->user->id) {
            $user_id_and_groups[] = '{$visitor}';
        }
        $user_id_and_groups[] = '';

        $rows = CorrespStim3D::where('row_hash', '!=', (new HelperService())->sys_row_hash['cf_temp'])
            ->where(function (Builder $query) use ($user_id_and_groups) {
                $query->whereNull('avail_to_user');
                $query->orWhereIn('avail_to_user', $user_id_and_groups);
            })
            ->orderBy('row_order')
            ->get();

        $help = new HelperService();
        foreach ($rows as $row) {
            $row->top_tab_low = $help->todb($row->top_tab ?: '');
            $row->select_low = $help->todb($row->select ?: '');
            $row->accordion_low = $help->todb($row->accordion ?: '');
            $row->horizontal_low = $help->todb($row->horizontal ?: '');
            $row->vertical_low = $help->todb($row->vertical ?: '');

            $row->table = $help->todb($row->db_table ?: '');
            $row->inheritance_3d = $help->todb($row->inheritance_3d ?: '');
            $row->type_tablda = strtolower($row->type_tablda);

            $row->model_3d = in_array($row->model_3d, ['2d:canvas','3d:equipment','3d:structure','3d:ma']) ? strtolower($row->model_3d) : '';
        }

        return $rows;
    }

    /**
     * @param string $app_table
     * @param string $use_filter
     * @return array
     */
    public function getAllInherits(string $app_table, string $use_filter = '')
    {
        $tr = $this->inheritSettings()->where('app_table', '=', $app_table)->first();
        $inherits = collect([]);
        if ($tr) {
            $settings = $this->inheritSettings();
            $inherits = $this->recursiveInh($tr->data_table, $settings, $use_filter);
        }
        return $inherits->unique()->values()->toArray();
    }

    /**
     * @param string $data_table
     * @param Collection $settings
     * @param string $use_filter
     * @return Collection
     */
    protected function recursiveInh(string $data_table, Collection $settings, string $use_filter)
    {
        $rev = preg_match("/false/i", $use_filter);

        $inherits = $settings->filter(function ($item) use ($data_table, $use_filter, $rev) {

            $fnd = preg_match("/$use_filter/i", $item->table_options)
                || preg_match("/$use_filter/i", $item->options);

            return $item->link_field_db && $item->link_table_db == $data_table
                && (!$use_filter || ($rev ? !$fnd : $fnd));
        });

        $results = $inherits->pluck('app_table');
        if ($inherits->count()) {
            $settings = $settings->whereNotIn( 'id', $inherits->pluck('id') );//anti infinite loop
            foreach ($inherits as $inh) {
                $results = $results->merge( $this->recursiveInh($inh->data_table, $settings, $use_filter) );
            }
        }
        return $results;
    }

    /**
     * @param Collection $app_fields
     * @param string $match
     * @param bool $no_pluck
     * @param bool $first
     * @return Collection
     */
    public function getDataFields(Collection $app_fields, string $match, bool $no_pluck = false, bool $first = false)
    {
        $col = $app_fields->filter(function ($fld) use ($match) {
                return preg_match("/$match/i", $fld->options);
            })
            ->values();

        if (!$no_pluck) {
            $col = $col->pluck('data_field');
        }

        if ($first) {
            $col = $col->first();
        }

        return $col;
    }

    /**
     * @param array $maps
     * @param array $row
     * @return array
     */
    public function convertReceiverToTablda(array $maps, array $row)
    {
        $converted = [];
        foreach ($maps as $model => $tablda) {
            if (isset($row[$model])) {
                $converted[$tablda] = $row[$model];
            }
        }
        return $converted;
    }

    /**
     * @param string $tab
     * @param string $select
     * @return Collection
     */
    public function stimTables(string $tab, string $select): Collection
    {
        return $this->get()
            ->where('top_tab_low', '=', strtolower($tab))
            ->where('select_low', '=', strtolower($select));
    }

    /**
     * @param string $tab
     * @param string $select
     * @return Collection
     */
    public function tabsTree(string $tab, string $select): Collection
    {
        $data = $this->stimTables($tab, $select);
        return $data->groupBy('horizontal');
    }
}