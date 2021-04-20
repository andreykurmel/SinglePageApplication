<?php

namespace Vanguard\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Vanguard\Mail\UsersInvite;
use Vanguard\User;

class SendInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $import_id;
    private $invit_ids;

    /**
     * SendInvitations constructor.
     *
     * @param User $user
     * @param array $invit_ids
     * @param $import_id
     */
    public function __construct(User $user, array $invit_ids, $import_id)
    {
        $this->user = $user;
        $this->import_id = $import_id;
        $this->invit_ids = $invit_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $all_invitations = $this->user
            ->_invitations()
            ->where('status', '!=', 2);
        if (count($this->invit_ids)) {
            $all_invitations->whereIn('id', $this->invit_ids);
        }
        $all_invitations = $all_invitations->get();

        $lines = $all_invitations->count();
        foreach ($all_invitations as $i => $invitation) {

            Mail::to($invitation->email)
                ->send(new UsersInvite($this->user, $invitation));

            DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
                'complete' => (int)(($i / $lines) * 100)
            ]);

            $invitation->date_send = Carbon::now();
            $invitation->status = 1;
            $invitation->save();
        }

        DB::connection('mysql')->table('imports')->where('id', '=', $this->import_id)->update([
            'status' => 'done'
        ]);
    }

    public function failed()
    {
        //
    }
}
