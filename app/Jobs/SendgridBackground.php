<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Vanguard\Models\Table\TableEmailAddonSetting;

class SendgridBackground implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \SendGrid\Mail\Mail
     */
    protected $sendgrid;
    /**
     * @var string
     */
    protected $api_key;
    /**
     * @var TableEmailAddonSetting
     */
    protected $email_addon;

    /**
     * SendgridBackground constructor.
     * @param \SendGrid\Mail\Mail $sendgrid
     * @param string $api_key
     */
    public function __construct(\SendGrid\Mail\Mail $sendgrid, string $api_key, TableEmailAddonSetting $email = null)
    {
        $this->email_addon = $email;

        $this->sendgrid = $sendgrid;
        $this->api_key = $api_key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = (new \SendGrid($this->api_key))->send($this->sendgrid);
        if ($response->statusCode() != 202) {
            Log::alert('SendGrid Email Error: ' . $response->body());
        } else {
            if ($this->email_addon) {
                $this->email_addon->oneFinished();
            }
        }
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
