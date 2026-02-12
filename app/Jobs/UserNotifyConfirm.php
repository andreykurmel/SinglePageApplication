<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vanguard\Repositories\Tablda\UserRepository;

class UserNotifyConfirm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userRepo;

    /**
     * UsersDailyPay constructor.
     */
    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(1200);

        $users = $this->userRepo->getUnconfirmed();
        foreach ($users as $user) {
            $this->userRepo->sendConfirmationEmail($user);
        }

        $users = $this->userRepo->getUnconfirmedWarning();
        foreach ($users as $user) {
            $removeDate = $user->created_at->addDays(7)->midDay()->toDateTimeString();
            $this->userRepo->sendConfirmationEmail($user, $removeDate);
        }

        $this->userRepo->deleteOldUnconfirmed();
    }

    public function failed()
    {
        //
    }
}
