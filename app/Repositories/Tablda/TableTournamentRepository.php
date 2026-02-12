<?php

namespace Vanguard\Repositories\Tablda;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableTournament;
use Vanguard\Models\Table\TableTournamentRight;
use Vanguard\Services\Tablda\HelperService;

class TableTournamentRepository
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
     * @param Table $table
     */
    public function loadForTable(Table $table, int $user_id = null)
    {
        $table->load([
            '_tournaments' => function ($s) use ($table, $user_id) {
                $vPermisId = $this->service->viewPermissionId($table);
                if ($table->user_id != $user_id && $vPermisId != -1) {
                    //get only 'shared' tableCharts for regular User.
                    $s->whereHas('_table_permissions', function ($tp) use ($vPermisId) {
                        $tp->applyIsActiveForUserOrPermission($vPermisId);
                    });
                }
                $s->with('_tournament_rights');
            }
        ]);
    }

    /**
     * @param $table_tournament_id
     * @return TableTournament|null
     */
    public function getTableTournament($table_tournament_id)
    {
        return TableTournament::where('id', '=', $table_tournament_id)->first();
    }

    /**
     * @param int $table_id
     * @param array $data
     * @return Model|TableTournament
     */
    public function addTableTournament(int $table_id, array $data)
    {
        return TableTournament::create(array_merge($data, ['table_id' => $table_id]));
    }

    /**
     * @param int $table_tournament_id
     * @param array $data
     * @return bool|int
     */
    public function updateTableTournament(int $table_tournament_id, array $data)
    {
        return TableTournament::where('id', '=', $table_tournament_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_tournament_id
     * @return bool|int|null
     * @throws Exception
     */
    public function deleteTableTournament($table_tournament_id)
    {
        return TableTournament::where('id', '=', $table_tournament_id)->delete();
    }

    /**
     * @param TableTournament $tournament
     * @param int $table_permis_id
     * @param $can_edit
     * @return TableTournamentRight
     */
    public function toggleTournamentRight(TableTournament $tournament, int $table_permis_id, $can_edit)
    {
        $right = $tournament->_tournament_rights()
            ->where('table_permission_id', $table_permis_id)
            ->first();

        if (!$right) {
            $right = TableTournamentRight::create([
                'table_tournament_id' => $tournament->id,
                'table_permission_id' => $table_permis_id,
                'can_edit' => $can_edit,
            ]);
        } else {
            $right->update([
                'can_edit' => $can_edit
            ]);
        }

        return $right;
    }

    /**
     * @param TableTournament $tournament
     * @param int $table_permis_id
     * @return mixed
     */
    public function deleteTournamentRight(TableTournament $tournament, int $table_permis_id)
    {
        return $tournament->_tournament_rights()
            ->where('table_permission_id', $table_permis_id)
            ->delete();
    }
}