<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableTournament;
use Vanguard\Models\Table\TableTournamentRight;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\TableTournamentRepository;

class TableTournamentController extends Controller
{
    use CanEditAddon;

    protected $tableRepository;
    protected $repoTournament;

    /**
     * TableBackupsController constructor.
     *
     * @param TableRepository $tableRepository
     */
    public function __construct(TableRepository $tableRepository)
    {
        $this->tableRepository = new TableRepository();
        $this->repoTournament = new TableTournamentRepository();
    }

    /**
     * @param Request $request
     * @return TableTournament[]
     * @throws AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = $this->tableRepository->getTable($request->table_id);
        $this->canEditAddon($table, 'tournament');
        $this->repoTournament->addTableTournament($table->id, $request->fields);
        return $table->_tournaments;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function update(Request $request)
    {
        $table_tournament = $this->repoTournament->getTableTournament($request->table_tournament_id);
        $table = $table_tournament->_table;
        $this->canEditAddonItem($table_tournament->_table, $table_tournament->_tournament_rights());
        $this->repoTournament->updateTableTournament($table_tournament->id, array_merge($request->fields, ['table_id' => $table->id]));
        return $table->_tournaments;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delete(Request $request)
    {
        $table_tournament = $this->repoTournament->getTableTournament($request->table_tournament_id);
        $table = $table_tournament->_table;
        $this->canEditAddonItem($table_tournament->_table, $table_tournament->_tournament_rights());
        $this->repoTournament->deleteTableTournament($table_tournament->id);
        return $table->_tournaments;
    }

    /**
     * @param Request $request
     * @return TableTournamentRight
     * @throws AuthorizationException
     */
    public function toggleTournamentRight(Request $request)
    {
        $tournament = $this->repoTournament->getTableTournament($request->tournament_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $tournament->_table]);
        return $this->repoTournament->toggleTournamentRight($tournament, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delTournamentRight(Request $request)
    {
        $tournament = $this->repoTournament->getTableTournament($request->tournament_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $tournament->_table]);
        return $this->repoTournament->deleteTournamentRight($tournament, $request->permission_id);
    }
}
