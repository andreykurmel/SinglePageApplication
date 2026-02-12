<?php

namespace Vanguard\Repositories\Activity;

use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Services\Logging\UserActivity\Activity;
use Carbon\Carbon;
use DB;
use Vanguard\Services\Tablda\TableAlertService;
use Vanguard\Services\Tablda\TableDataService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class EloquentActivity implements ActivityRepository
{
    /**
     * {@inheritdoc}
     */
    public function log($data)
    {
        $activ = Activity::create($data);

        //check ANA addon
        $table = (new TableRepository())->getTableByDB('user_activity');
        $datas = (new TableDataService())->getRows([
            'table_id' => $table->id,
            'page' => 1,
            'rows_per_page' => 0,
            'row_id' => $activ->id
        ], $activ->user_id);
        $special = ['user_id' => $table->user_id, 'permission_id' => null];
        (new TableAlertService())->checkAndSendNotifArray($table, 'added', $datas['rows'], [], $special);

        return $activ;
    }

    /**
     * {@inheritdoc}
     */
    public function logWhere($data, $find)
    {
        return Activity::where($find)->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $where, bool $first = true)
    {
        $sql = Activity::where($where);
        return $first ? $sql->first() : $sql->get();
    }

    /**
     * {@inheritdoc}
     */
    public function paginateActivitiesForUser($userId, $perPage = 20, $search = null)
    {
        $query = Activity::where('user_id', $userId);

        return $this->paginateAndFilterResults($perPage, $search, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestActivitiesForUser($userId, $activitiesCount = 10)
    {
        return Activity::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($activitiesCount)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function paginateActivities($perPage = 20, $search = null)
    {
        $query = Activity::with('user');

        return $this->paginateAndFilterResults($perPage, $search, $query);
    }

    /**
     * @param $perPage
     * @param $search
     * @param $query
     * @return mixed
     */
    private function paginateAndFilterResults($perPage, $search, $query)
    {
        if ($search) {
            $query->where('description', 'LIKE', "%$search%");
        }

        $result = $query->orderBy('created_at', 'DESC')
            ->paginate($perPage);

        if ($search) {
            $result->appends(['search' => $search]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function userActivityForPeriod($userId, Carbon $from, Carbon $to)
    {
        $result = Activity::select([
            DB::raw("DATE(created_at) as day"),
            DB::raw('count(id) as count')
        ])
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->pluck('count', 'day');

        while (! $from->isSameDay($to)) {
            if (! $result->has($from->toDateString())) {
                $result->put($from->toDateString(), 0);
            }
            $from->addDay();
        }

        return $result->sortBy(function ($value, $key) {
            return strtotime($key);
        });
    }
}