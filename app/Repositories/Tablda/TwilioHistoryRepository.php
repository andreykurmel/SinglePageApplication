<?php

namespace Vanguard\Repositories\Tablda;

use Illuminate\Support\Collection;
use Vanguard\Models\TwilioHistory;
use Vanguard\Services\Tablda\HelperService;

class TwilioHistoryRepository
{
    protected $service;

    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * @param int $user_id
     * @param string $receiver
     * @param string $type
     * @param array $content
     * @param int|null $table_id
     * @param int|null $field_id
     * @param int|null $row_id
     * @param string|null $incoming_app_id
     * @param int $missed
     * @return TwilioHistory
     */
    public function store(
        int $user_id,
        string $receiver,
        string $type,
        array $content,
        int $table_id = null,
        int $field_id = null,
        int $row_id = null,
        string $incoming_app_id = null,
        int $missed = 0
    ): TwilioHistory
    {
        return TwilioHistory::create(array_merge([
            'user_id' => $user_id,
            'table_id' => $table_id,
            'table_field_id' => $field_id,
            'row_id' => $row_id,
            'type' => $type,
            'receiver' => $receiver,
            'incoming_app_id' => $incoming_app_id,
            'content' => $content,
            'missed' => $missed,
        ], $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * @param int $table_id
     * @param int $field_id
     * @param array $row_ids
     * @return Collection
     */
    public function getForRows(int $table_id, int $field_id, array $row_ids): Collection
    {
        return TwilioHistory::where('table_id', '=', $table_id)
            ->where('table_field_id', '=', $field_id)
            ->whereIn('row_id', $row_ids)
            ->orderBy('created_on', 'desc')
            ->get();
    }

    /**
     * @param string $incoming_app_id
     * @param string $from_date
     * @return Collection
     */
    public function getIncomingSms(string $incoming_app_id, string $from_date): Collection
    {
        return TwilioHistory::where('incoming_app_id', '=', $incoming_app_id)
            ->where('created_on', '>', $from_date)
            ->orderBy('created_on', 'desc')
            ->get();
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getForUser(int $user_id): array
    {
        $histories = TwilioHistory::where('user_id', '=', $user_id)
            ->whereNull('table_id')
            ->whereNull('table_field_id')
            ->whereNull('row_id')
            ->orderBy('created_on', 'desc')
            ->get();

        if ($histories->where('missed', '=', 1)->count()) {
            TwilioHistory::whereIn('id', $histories->pluck('id'))
                ->update(['missed' => 0]);
        }

        $histories = $histories->groupBy('type')->toArray();

        return $this->allTypes($histories);
    }

    /**
     * @param array $histories
     * @return array
     */
    public function allTypes(array $histories): array
    {
        foreach ([TwilioHistory::$EMAIL_TYPE, TwilioHistory::$SMS_TYPE, TwilioHistory::$CALL_TYPE] as $type) {
            if (empty($histories[$type])) {
                $histories[$type] = [];
            }
        }
        return $histories;
    }

    /**
     * @param int $history_id
     * @param int $user_id
     * @return bool
     */
    public function ownerOf(int $history_id, int $user_id): bool
    {
        return !!TwilioHistory::where('user_id', '=', $user_id)
            ->where('id', '=', $history_id)
            ->first();
    }

    /**
     * @param int $history_id
     * @return bool
     * @throws \Exception
     */
    public function remove(int $history_id): bool
    {
        return !!TwilioHistory::where('id', '=', $history_id)->delete();
    }
}