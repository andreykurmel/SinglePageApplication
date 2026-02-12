<?php

namespace Vanguard\Repositories\Tablda;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Vanguard\Models\AutomationHistory;
use Vanguard\Models\HistoryField;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class AutomationHistoryRepository
{
    protected $startTime;
    protected $user_id;
    protected $table_id;
    protected $service;

    /**
     * @param int $user_id
     * @param int $table_id
     */
    public function __construct(int $user_id, int $table_id)
    {
        $this->user_id = $user_id;
        $this->table_id = $table_id;
        $this->service = new HelperService();
    }

    /**
     * @return void
     */
    public function startTimer(): void
    {
        $this->startTime = microtime(true);
    }

    /**
     * @param string $function
     * @param string $name
     * @param string $component
     * @param string $part
     * @return AutomationHistory
     */
    public function stopTimerAndSave(string $function, string $name, string $component, string $part): AutomationHistory
    {
        $execTime = microtime(true) - $this->startTime;
        return AutomationHistory::create(array_merge(
            [
                'user_id' => $this->user_id,
                'table_id' => $this->table_id,
                'function' => $function,
                'name' => $name,
                'component' => $component,
                'part' => $part,
                'exec_time' => number_format($execTime, 2) . ' sec',
                'start_time' => now(),
                'year' => now()->year,
                'month' => now()->month,
                'week' => now()->weekOfYear,
                'day' => now()->day,
            ],
            $this->service->getModified(),
            $this->service->getCreated()
        ));
    }
}