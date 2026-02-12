<?php

namespace Vanguard\Listeners\Registration;

use Vanguard\Events\User\Registered;
use Vanguard\Models\PromoCode;
use Vanguard\Repositories\Tablda\PlanRepository;

class SetPromoCodeToNewUser
{
    /**
     * @var PlanRepository
     */
    protected $repository;

    public function __construct(PlanRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Handle the event.
     *
     * @param Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $code = PromoCode::where('code', '=', $event->promoValue)->first();
        if ($code) {
            $user = $event->getRegisteredUser();
            $user->update([
                'avail_credit' => $user->avail_credit + $code->credit,
            ]);
            $this->repository->addPaymentHistory($user->id, 'Receiving Credit', $code->credit, $user->full_name(), 'System Credit', 'Registration Promo Code: '.$code->code);
        }
    }
}
