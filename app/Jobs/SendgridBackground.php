<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
     * SendgridBackground constructor.
     * @param \SendGrid\Mail\Mail $sendgrid
     * @param string $api_key
     */
    public function __construct(\SendGrid\Mail\Mail $sendgrid, string $api_key)
    {
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
