<?php

namespace Vanguard\Repositories\Tablda;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Vanguard\Models\AppTheme;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\Folder2Table;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableChart;
use Vanguard\Models\Table\TableMapIcon;
use Vanguard\Models\Table\TableNote;
use Vanguard\Models\Table\TableSharedName;
use Vanguard\Models\Table\TableStatuse;
use Vanguard\Models\Table\TableUserSetting;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\Communication;
use Vanguard\Models\User\Plan;
use Vanguard\Modules\Permissions\PermissionObject;
use Vanguard\Modules\Permissions\TableRights;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class TableRepository
{
    protected $service;

    /**
     * TableRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Save Table Status for User.
     *
     * @param int $table_id
     * @param int $user_id
     * @param string $data
     * @return mixed
     */
    public function saveStatuse(int $table_id, int $user_id, string $data)
    {
        return TableStatuse::updateOrCreate([
            'table_id' => $table_id,
            'user_id' => $user_id,
        ], [
            'status_data' => $data
        ]);
    }

    /**
     * Add record into the 'tables' for just created table
     *
     * @param array $data :
     * [
     *  +name: string,
     *  +db_name: string,
     *  +user_id: int,
     *  -rows_per_page: int,
     *  -notes: string
     * ]
     * @return mixed
     */
    public function addTable(Array $data)
    {
        //defaults
        $data['hash'] = Uuid::uuid4();
        $data['enabled_activities'] = $data['enabled_activities'] ?? 0;
        $data['autoload_new_data'] = $data['autoload_new_data'] ?? 1;
        $data['is_public'] = $data['is_public'] ?? 0;

        $tb_data = collect($data)->only((new Table)->getFillable())->toArray();
        $tb = Table::create(array_merge($tb_data, $this->service->getCreated(), $this->service->getModified()));
        $this->saveUserSettings($tb->id, $data['_cur_settings'] ?? [], true);

        AppTheme::create($this->appThemeArr($tb->id, $data['_theme'] ?? []));

        return $tb;
    }

    /**
     * @param $tb_id
     * @param $data
     * @param bool $can_sync
     */
    public function saveUserSettings($tb_id, $data, $can_sync = false)
    {
        if (auth()->id()) {
            $data['initial_view_id'] = $data['initial_view_id'] ?? -1;
            $data['table_id'] = $tb_id;
            $data['user_id'] = auth()->id();
            TableUserSetting::updateOrCreate([
                'table_id' => $tb_id,
                'user_id' => auth()->id()
            ], $data);

            if ($can_sync) {
                $user_shows = collect($data)->only(TableUserSetting::$user_show_fields)->toArray();
                if (count($user_shows)) {
                    TableUserSetting::where('table_id', $tb_id)->update($user_shows);
                }
            }
        }
    }

    /**
     * @param int $table_id
     * @return mixed
     */
    public function getUserSettings(int $table_id)
    {
        return TableUserSetting::where('table_id', '=', $table_id)
            ->where('user_id', '=', auth()
                ->id())->first();
    }

    /**
     * @param $tb_id
     * @param $data
     * @return array
     */
    private function appThemeArr(int $tb_id, array $data)
    {
        $arr = ['obj_type' => 'table', 'obj_id' => $tb_id];
        foreach ((new AppTheme())->getFillable() as $key) {
            if (array_key_exists($key, $data)) {
                $arr[$key] = $data[$key];
            }
        }
        return $arr;
    }

    /**
     * @param int $table_id
     * @param $is_public
     * @return mixed
     */
    public function updateIsPublic(int $table_id, $is_public)
    {
        return Table::where('id', $table_id)
            ->update(['is_public' => $is_public ? 1 : 0]);
    }

    /**
     * Update table data.
     *
     * @param $table_id
     * @param array $data
     * @return mixed
     */
    public function updateTableSettings($table_id, Array $data)
    {
        if (empty($data['unit_conv_table_id'])) {
            $data['unit_conv_table_id'] = null;
        }

        $this->saveUserSettings($table_id, $data['_cur_settings'] ?? [], true);

        $appTheme = AppTheme::where('obj_type', 'table')->where('obj_id', $table_id)->first();
        if ($appTheme) {
            $appTheme->update($this->appThemeArr($table_id, $data['_theme'] ?? []));
        }

        if (!empty($data['initial_view_id']) && $data['initial_view_id'] < 0) {
            TableStatuse::where('table_id', $table_id)
                ->where('user_id', auth()->id())
                ->delete();
        }

        return $this->onlyUpdateTable($table_id, $data);
    }

    /**
     * Update table data.
     *
     * @param $table_id
     * @param array $data
     * @return mixed
     */
    public function onlyUpdateTable($table_id, Array $data)
    {
        $tb_data = collect($data)
            ->only( (new Table)->getFillable() )
            ->toArray();

        return Table::where('id', '=', $table_id)
            ->update($this->service->delSystemFields($tb_data));
    }

    /**
     * Get tables available for user (is_owner or has permissions)
     *
     * @param $user_id
     * @return mixed
     */
    public function getAvailableTables($user_id)
    {
        return Table::where(function ($q) use ($user_id) {
                $q->where(function ($in) use ($user_id) {
                    $in->where('tables.user_id', $user_id);
                });
                $q->orWhereHas('_table_permissions', function ($tp) {
                    $tp->isActiveForUserOrVisitor();
                    $tp->where('can_reference', 1);
                });
            })
            ->select(['id', 'name', 'db_name', 'user_id'])
            ->get();
    }

    /**
     * Get tables and it links from folder tree.
     *
     * @param $tree_elements
     * @return array
     */
    public function getTablesFromTree($tree_elements)
    {
        $tables = [];

        foreach ($tree_elements as $element) {
            if ($element['li_attr']['data-type'] !== 'folder') {
                $tables = array_merge($tables, [
                    ['name' => $element['text'], 'link' => $element['a_attr']['href']]
                ]);
            } else {
                $tables = array_merge($tables, $this->getTablesFromTree($element['children']));
            }
        }

        return $tables;
    }

    /**
     * Get Table IDs.
     *
     * @param $folder_ids
     * @return array
     */
    public function getIdsFromFolders($folder_ids)
    {
        return Folder2Table::whereIn('folder_id', $folder_ids)
            ->get(['table_id'])
            ->pluck('table_id')
            ->toArray();
    }

    /**
     * Get folder by name.
     *
     * @param $name
     * @return mixed
     */
    public function getTableByName($name)
    {
        return Table::where('name', '=', $name)->first();
    }

    /**
     * Get only table info
     *
     * @param $table_id
     * @return Table
     */
    public function getTable($table_id)
    {
        return Table::where('id', '=', $table_id)->first();
    }

    /**
     * @param $value
     * @return Table|null
     */
    public function findByDbOrId($value)
    {
        return Table::where('db_name', '=', $value)
            ->orWhere('id', '=', $value)
            ->first();
    }

    /**
     * Get only table info
     *
     * @param $table_id
     * @return Table
     */
    public function getTableByIdOrDB($table_id)
    {
        if (is_numeric($table_id)) {
            return $this->getTable($table_id);
        } else {
            return $this->getTableByDB($table_id);
        }
    }

    /**
     * Get only table info
     *
     * @param $hash
     * @return Table
     */
    public function getTableByHash($hash)
    {
        return Table::where('hash', $hash)->first();
    }

    /**
     * Get only table info
     *
     * @param $hash
     * @return mixed
     */
    public function getTableByViewHash($hash)
    {
        return Table::whereHas('_views', function ($q) use ($hash) {
            $q->where('hash', $hash);
        })->first();
    }

    /**
     * Get only table info
     *
     * @param $db_name
     * @return Table
     */
    public function getTableByDB($db_name)
    {
        return Table::where('db_name', '=', $db_name)->first();
    }

    /**
     * Get many tables info
     *
     * @param array $table_ids
     * @return mixed
     */
    public function getTables(Array $table_ids)
    {
        return Table::whereIn('id', $table_ids)->get();
    }

    /**
     * Get many tables info
     *
     * @param array $table_dbs
     * @return mixed
     */
    public function getTablesFromDB(Array $table_dbs)
    {
        return Table::whereIn('db_name', $table_dbs)->get();
    }

    /**
     * Get all tables present in provided folders
     *
     * @param array $folder_ids
     * @param string $type
     * @return mixed
     */
    public function getTablesInFolders(Array $folder_ids, $type = 'table')
    {
        return Table::whereHas('_folder_links', function ($q) use ($folder_ids, $type) {
            $q->whereIn('folder_id', $folder_ids);
            $q->where('type', '=', $type);
        })->get();
    }

    /**
     * Test that table is linked to favorite tab for provided user.
     *
     * @param $table_id
     * @param $user_id
     * @return bool
     */
    public function isFavorite($table_id, $user_id)
    {
        return Folder2Table::where('table_id', '=', $table_id)
                ->where('user_id', '=', $user_id)
                ->where('structure', '=', 'favorite')
                ->count() > 0;
    }

    /**
     * @param Table $table
     * @param int|null $user_id
     * @return PermissionObject
     */
    public function loadCurrentRight(Table $table, int $user_id = null)
    {
        $permis = TableRights::permissions($table);
        $table->_is_owner = !!$permis->is_owner;
        $table->_current_right = $permis;
        return $permis;
    }

    /**
     * @param Table $table
     * @param array $row_gr_ids
     * @return bool|Collection
     */
    public function canDelRows(Table $table, array $row_gr_ids)
    {
        $permis = TableRights::permissions($table);
        return $permis->is_owner
            ? true
            : $permis->delete_row_groups->intersect($row_gr_ids);
    }

    /**
     * Get system tables ids
     *
     * @return Collection
     */
    public function getSystemTables()
    {
        $main = Table::where('is_system', '=', 1)
            ->whereNotIn('db_name', $this->service->myaccount_tables)
            ->get();
        return $main->merge( Table::whereIn('db_name', $this->service->stim_views)->get() );
    }

    /**
     * Delete user`s table
     *
     * @param int $table_id
     * @return array|bool|null
     */
    public function deleteTable($table_id)
    {
        return Table::where('id', '=', $table_id)->delete();
    }

    /**
     * Delete table link from folder.
     *
     * @param int $link_id
     * @return array|bool|null
     */
    public function deleteLink($link_id)
    {
        return Folder2Table::where('id', '=', $link_id)->delete();
    }

    /**
     * @param int $table_id
     * @param int $folder_id
     * @param string $type
     * @param string $structure
     * @return bool
     */
    public function insertLink(int $table_id, int $folder_id, string $type = 'table', string $structure = 'private')
    {
        return Folder2Table::insert([
            'table_id' => $table_id,
            'user_id' => auth()->id(),
            'folder_id' => $folder_id,
            'type' => $type,
            'structure' => $structure,
        ]);
    }

    /**
     * Update link data.
     *
     * @param $link_id
     * @param array $data
     * @return mixed
     */
    public function updateLink($link_id, Array $data)
    {
        return Folder2Table::where('id', '=', $link_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * Update Links positions.
     *
     * @param Table $table
     * @param $user_id
     * @param $folder_id
     * @param $position
     */
    public function updatePosition(Table $table, $user_id, $folder_id, $position)
    {
        $table->menutree_order = $position;
        $table->save();

        $links = Table::whereHas('_folder_links', function ($q) use ($user_id, $folder_id) {
                $q->where('user_id', '=', $user_id);
                $q->where('folder_id', '=', $folder_id);
            })
            ->where('id', '!=', $table->id)
            ->orderBy('menutree_order')
            ->get();
        $idx = -1;
        foreach ($links as $link) {
            $idx++;
            if ($idx == $position) {
                $idx++;
            }
            $link->menutree_order = $idx;
            $link->save();
        }
    }

    /**
     * Delete shared links for all other Users (which were not changed by other Users)
     * to sync their structure with owner structure.
     *
     * @param array $table_ids
     * @param int $user_id
     * @return mixed
     */
    public function syncStructureOfShared(array $table_ids, int $user_id)
    {
        return Folder2Table::whereIn('table_id', $table_ids)
            ->where('type', 'share-alt')
            ->where('user_id', '!=', $user_id)
            ->whereHas('_folder', function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })
            ->delete();
    }

    /**
     * Transfer table from user to another user.
     *
     * @param \Vanguard\Models\Table\Table $table
     * @param int $new_folder_id
     * @param int $new_user_id
     * @return array|bool|null
     */
    public function transferTable(Table $table, $new_user_id, $new_folder_id = null)
    {
        //delete permissions
        $table->_table_permissions()->where('is_system', 0)->delete();
        //delete links
        $table->_folder_links()->where('type', '!=', 'table')->delete();

        //transfer table to folder link
        Folder2Table::where('table_id', '=', $table->id)
            ->update(array_merge(
                ['user_id' => $new_user_id],
                ($new_folder_id ? ['folder_id' => $new_folder_id] : []),
                $this->service->getModified()
            ));
        //transfer Conditional Formattings
        $table->_cond_formats()->update(['user_id' => $new_user_id]);
        //transfer Views
        $table->_views()->update(['user_id' => $new_user_id]);

        //transfer table
        return $table->update(array_merge(['user_id' => $new_user_id], $this->service->getModified()));
    }

    /**
     * Add Message to Table from the user to another user.
     *
     * @param Int $table_id
     * @param Int $from_user_id
     * @param Int $to_user_id
     * @param Int $to_user_group_id - nullable
     * @param String $message
     * @return mixed
     */
    public function addMessage(Int $table_id, Int $from_user_id, Int $to_user_id, $to_user_group_id, String $message)
    {
        return Communication::create(array_merge([
            'table_id' => $table_id,
            'from_user_id' => $from_user_id,
            'to_user_id' => $to_user_id,
            'to_user_group_id' => $to_user_group_id,
            'date' => Carbon::now()->toDateTimeString(),
            'message' => $message
        ], $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * Get Messages by id/ids.
     *
     * @param $ids
     * @return mixed
     */
    public function getMessage($ids)
    {
        return Communication::find($ids);
    }

    /**
     * Delete Message.
     *
     * @param Int $message_id
     * @return mixed
     */
    public function deleteMessage(Int $message_id)
    {
        return Communication::where('id', '=', $message_id)->delete();
    }

    /**
     * Toggle table in favorite for user.
     *
     * @param int $table_id
     * @param int $user_id
     * @param bool $favorite
     * @return bool
     */
    public function favoriteToggle($table_id, $user_id, $favorite)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        if ($favorite) {

            $link = Folder2Table::where('table_id', '=', $table_id)
                ->where('user_id', '=', $user_id)
                ->where('type', '=', 'link')
                ->where('structure', '=', 'favorite')
                ->first();

            if (!$link) {
                $link = Folder2Table::create(array_merge([
                    'table_id' => $table_id,
                    'user_id' => $user_id,
                    'type' => 'link',
                    'structure' => 'favorite'
                ], $this->service->getModified(), $this->service->getCreated()));
            }

            return $link;

        } else {

            return Folder2Table::where('table_id', '=', $table_id)
                ->where('user_id', '=', $user_id)
                ->where('type', '=', 'link')
                ->where('structure', '=', 'favorite')
                ->delete();

        }
    }

    /**
     * Update table note for user.
     *
     * @param int $table_id
     * @param int $user_id
     * @param string $notes
     * @return mixed
     */
    public function updateUserNote($table_id, $user_id, $notes)
    {
        $tb_note = TableNote::where('table_id', '=', $table_id)->where('user_id', '=', $user_id)->first();
        if ($tb_note) {
            $tb_note->notes = $notes;
            return $tb_note->save();
        } else {
            return TableNote::create(array_merge([
                'table_id' => $table_id,
                'user_id' => $user_id,
                'notes' => $notes
            ], $this->service->getModified(), $this->service->getCreated()));
        }
    }

    /**
     * Test that table with provided name already exists.
     *
     * @param $table_name
     * @param $folder_id
     * @param $user_id
     * @return mixed
     */
    public function testNameOnLvl($table_name, $folder_id, $user_id)
    {
        return Table::where('name', '=', $table_name)->whereHas('_folder_links', function ($q) use ($folder_id, $user_id) {
            $q->where('folder_id', '=', $folder_id);
            $q->where('user_id', '=', $user_id);
        })->first();
    }

    /**
     * Get all tables for 'Sum Usage'.
     *
     * @param User $user
     * @return mixed
     */
    public function getForSumUsages(User $user)
    {
        $sum_usages = Table::with(['_user']);
        if ($user->role_id != 1) {
            $sum_usages->where('user_id', '=', $user->id);
        }
        $sum_usages->where('is_system', '=', 0);
        return $sum_usages->get();
    }

    /**
     * Get Plans and Addons for 'Fees'.
     *
     * @param User $user
     * @return mixed
     */
    public function getForFees(User $user)
    {
        $columns = [
            'per_month',
            'per_year',
            'notes',
            'created_by',
            'created_on',
            'modified_by',
            'modified_on'
        ];

        $plans = Plan::select($columns)
            ->selectRaw('`name` as `plan`, NULL as `feature`')
            ->get();

        $addons = Addon::select($columns)
            ->selectRaw('NULL as `plan`, `name` as `feature`')
            ->get();

        return $plans->merge($addons);
    }

    /**
     * Get folder to which connected Table.
     *
     * @param $tb_id
     * @return mixed
     */
    public function getInitialFolder($tb_id)
    {
        $f2t = Folder2Table::where('table_id', $tb_id)
            ->where('type', '=', 'table')
            ->where('structure', '=', 'private')
            ->first();
        return Folder::where('id', '=', ($f2t ? $f2t->folder_id : null))->first();
    }

    /**
     * Add Map Icon
     *
     * @param $data : [
     *  table_field_id: int,
     *  row_val: string,
     *  icon_path: string,
     *  height: int
     * ]
     * @return mixed
     */
    public function addMapIcon(array $data)
    {
        TableMapIcon::where('table_field_id', '=', $data['table_field_id'])
            ->where('row_val', '=', $data['row_val'])
            ->delete();

        return TableMapIcon::create(array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * Update Map Icon
     *
     * @param $data : [
     *  table_field_id: int,
     *  row_val: string,
     *  icon_path: string,
     *  height: int
     * ]
     * @return mixed
     */
    public function updateMapIcon(array $data)
    {
        return TableMapIcon::where('table_field_id', '=', $data['table_field_id'])
            ->where('row_val', '=', $data['row_val'])
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));
    }

    /**
     * Delete Map Icon
     *
     * @param $field_id
     * @param $row_val
     * @return mixed
     */
    public function delMapIcon($field_id, $row_val)
    {
        return TableMapIcon::where('table_field_id', '=', $field_id)
            ->where('row_val', '=', $row_val)
            ->delete();
    }

    /**
     * Update or Create shared table alias for selected user.
     *
     * @param int $table_id
     * @param int $user_id
     * @param string $name
     * @return mixed
     */
    public function renameSharedTable(int $table_id, int $user_id, string $name)
    {
        //menutree is changed
        (new UserRepository())->newMenutreeHash($user_id);

        return TableSharedName::updateOrCreate(
            [
                'table_id' => $table_id,
                'user_id' => $user_id,
            ], [
                'table_id' => $table_id,
                'user_id' => $user_id,
                'name' => $name,
            ]
        );
    }

    /**
     * @param Table $table
     * @param int|null $user_id
     */
    public function loadCondFormats(Table $table, int $user_id = null)
    {
        $table->load([
            '_cond_formats' => function ($cf) use ($table, $user_id)
            {
                $cf->orderBy('row_order');

                $cf->with('_user_settings', '_table_permissions');

                //all CF for DCR or apply rules
                $cf->where('user_id', $user_id);
                if ($user_id != $table->user_id && $table->__data_permission_id != -1) {
                    //get only 'shared' condFormats for regular User.
                    $cf->orWhereHas('_table_permissions', function ($tp) use ($table) {
                        $tp->applyIsActiveForUserOrPermission($table->__data_permission_id);
                        //all cond formats for DCR
                        //TODO: DCR moved to another table, use PermissionModule.
                        //$tp->orWhere('is_request', '>', 0);
                    });
                }
            }
        ]);
    }

    /**
     * @param Table $table
     */
    public function loadOwnerSettings(Table $table)
    {
        $_owner_settings = $table->_owner_settings()->first();
        if (!$_owner_settings) {
            $_owner_settings = TableUserSetting::create([
                'table_id' => $table->id,
                'user_id' => $table->user_id,
            ]);
        }
        $table->setRelation('_owner_settings', $_owner_settings);
    }
}