<?php

namespace Vanguard\Repositories\Tablda;


use Carbon\Carbon;
use Vanguard\Models\Table\Table;
use Vanguard\Models\User\Addon;
use Vanguard\Models\User\Payment;
use Vanguard\Models\User\Plan;
use Vanguard\Models\User\PlanFeature;
use Vanguard\Repositories\Tablda\TableData\TableDataQuery;
use Vanguard\Repositories\Tablda\TableData\TableDataRepository;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

class PlanRepository
{
    protected $service;
    protected $dataRepository;

    /**
     * PlanRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->dataRepository = new TableDataRepository();
    }

    /**
     * Get Plan By Code
     *
     * @param $code
     * @return mixed
     */
    public function getPlanByCode($code)
    {
        return Plan::where('code', '=', $code)->first();
    }

    /**
     * Get Plan By Id
     *
     * @param $id
     * @return mixed
     */
    public function getPlan($id)
    {
        return Plan::where('id', '=', $id)->first();
    }

    /**
     * Get Addon By Code
     *
     * @param $code
     * @return mixed
     */
    public function getAddonByCode($code)
    {
        return Addon::where('code', '=', $code)->first();
    }

    /**
     * Get Addon By $id
     *
     * @param $id
     * @return mixed
     */
    public function getAddon($id)
    {
        return Addon::where('id', '=', $id)->first();
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @return bool
     */
    public function renameAddon(int $id, string $name, string $description): bool
    {
        return Addon::where('id', '=', $id)
            ->update([
                'name' => $name,
                'description' => $description,
            ]);
    }

    /**
     * Copy Plan Features For User.
     *
     * @param \Vanguard\Models\User\Plan $plan
     * @param $user_id
     * @return mixed
     */
    public function copyPlanFeaturesForUser(Plan $plan, $user_id)
    {
        if (!$plan->plan_feature_id) {
            $plan_feature = $this->createFeaturesForPlan($plan->id);

            $plan->plan_feature_id = $plan_feature->id;
            $plan->save();
        } else {
            $plan_feature = $plan->_available_features;
        }

        return PlanFeature::create(array_merge(
            $plan_feature->toArray(),
            [
                'type' => 'user',
                'object_id' => $user_id
            ],
            $this->service->getModified(),
            $this->service->getCreated()
        ));
    }

    /**
     * Create Features For Plan.
     *
     * @param $plan_id
     * @return mixed
     */
    private function createFeaturesForPlan($plan_id)
    {
        return PlanFeature::create(array_merge(
            [
                'type' => 'plan',
                'object_id' => $plan_id
            ],
            $this->service->getModified(),
            $this->service->getCreated()
        ));
    }

    /**
     * Remove old Features For User.
     *
     * @param $user_id
     * @return mixed
     */
    public function removePlanFeaturesForUser($user_id)
    {
        return PlanFeature::where('type', '=', 'user')
            ->where('object_id', '=', $user_id)
            ->delete();
    }

    /**
     * Update all PlanFeatures for all rows in PlansView table.
     *
     * @param Table $table_info
     * @return mixed
     */
    public function updateAllFeaturesForAllRows(Table $table_info)
    {
        $all_rows = (new TableDataQuery($table_info))->getQuery()->get();
        foreach ($all_rows as $row) {
            $this->updateAllFeatures($row->toArray());
        }
        return true;
    }

    /**
     * Update all PlanFeatures if Admin has changed some in 'plans_view' table.
     *
     * @param array $plans_view_row
     * @return mixed
     */
    public function updateAllFeatures(Array $plans_view_row)
    {
        $plans = Plan::all();
        foreach ($plans as $pln) {
            $this->planFeatureChanged($pln, $plans_view_row['code'], $plans_view_row['plan_' . $pln->code]);
        }
        return true;
    }

    /**
     * Update all user's and plan's PlanFeatures if changed PlanFeature for some plan.
     *
     * @param Plan $plan
     * @param string $col
     * @param string $val
     * @return mixed
     */
    public function planFeatureChanged(Plan $plan, $col, $val)
    {
        $user_ids = User::whereHas('_all_subs', function ($q) use ($plan) {
                $q->where('plan_code', '=', $plan->code);
            })
            ->get()
            ->pluck('id');

        if (!in_array($col, ['q_tables', 'row_table'])) {
            $val = $val ? 1 : null;
        }

        //update plan itself
        PlanFeature::where('type', '=', 'plan')
            ->where('object_id', '=', $plan->id)
            ->update([$col => $val]);

        //update features for all users
        return PlanFeature::where('type', '=', 'user')
            ->whereIn('object_id', $user_ids)
            ->update([$col => $val]);
    }

    /**
     * Update Plans and Addons from Fees table.
     *
     * @param $fields
     * @param $id
     * @return mixed
     */
    public function updatePlansAndAddons($fields, $id)
    {
        if (!empty($fields['plan_feature_id'])) {
            return Plan::where('id', '=', $id)->update(array_merge([
                'name' => $fields['plan'],
                'per_month' => $fields['per_month'],
                'per_year' => $fields['per_year'],
                'notes' => $fields['notes'],
            ], $this->service->getModified()));
        } else {
            return Addon::where('id', '=', $id)->update(array_merge([
                'name' => $fields['feature'],
                'per_month' => $fields['per_month'],
                'per_year' => $fields['per_year'],
                'notes' => $fields['notes'],
            ], $this->service->getModified()));
        }
    }


    /**
     * Add Plan.
     *
     * @param $data
     * [
     *  +per_month: int,
     *  +per_year: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function addPlan($data)
    {
        if (empty($data['per_month'])) {
            $data['per_month'] = 0;
        }
        if (empty($data['per_year'])) {
            $data['per_year'] = 0;
        }
        return Plan::create(array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * Update Plan.
     *
     * @param $data
     * [
     *  +plan_id: int,
     *  +per_month: int,
     *  +per_year: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function updatePlan($id, $data)
    {
        return Plan::where('id', '=', $id)->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));
    }

    /**
     * Add Addon.
     *
     * @param $data
     * [
     *  +per_month: int,
     *  +per_year: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function addAddon($data)
    {
        if (empty($data['code'])) {
            $data['code'] = preg_replace('/[^\w\d]/i', '', $data['name']);
        }
        if (empty($data['per_month'])) {
            $data['per_month'] = 0;
        }
        if (empty($data['per_year'])) {
            $data['per_year'] = 0;
        }
        return Plan::create(array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * Update Addon.
     *
     * @param $data
     * [
     *  +plan_id: int,
     *  +per_month: int,
     *  +per_year: int,
     *  +name: string,
     *  -notes: string,
     * ]
     * @return mixed
     */
    public function updateAddon($id, $data)
    {
        return Plan::where('id', '=', $id)->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));
    }

    /**
     * @param $user_id
     * @param $type
     * @param $amount
     * @param string $to
     * @param string $from
     * @param string $from_details
     * @return Payment
     */
    public function addPaymentHistory($user_id, $type, $amount, $to = '', $from = '', $from_details = ''): Payment
    {
        $now = Carbon::now();
        return Payment::create(array_merge(
            [
                'due_date' => $now->toDateTimeString(),
                'year' => $now->year,
                'month' => $now->month,
                'week' => $now->weekOfYear,
                'user_id' => $user_id,
                'type' => $type,
                'amount' => $amount,
                'to' => $to,
                'from' => $from,
                'from_details' => $from_details,
                'transaction_id' => $this->service->genTransationId(),
            ],
            $this->service->getModified(),
            $this->service->getCreated()
        ));
    }
}