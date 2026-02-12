<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Exceptions\ConfigurationException;
use Vanguard\Models\Table\TableTwilioAddonSetting;
use Vanguard\Repositories\Tablda\TableTwilioAddonRepository;
use Vanguard\Services\Tablda\TableTwilioAddonService;

class DelayTwilioAddon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var TableTwilioAddonService
     */
    private $service;
    /**
     * @var TableTwilioAddonRepository
     */
    private $repo;
    /**
     * @var TableTwilioAddonSetting
     */
    private $twilio;
    /**
     * @var array
     */
    private $row;

    /**
     * @param TableTwilioAddonSetting $twilio
     * @param array $row
     */
    public function __construct(TableTwilioAddonSetting $twilio, array $row)
    {
        $this->twilio = $twilio;
        $this->row = $row;
        $this->service = new TableTwilioAddonService();
        $this->repo = new TableTwilioAddonRepository();
    }

    /**
     * @throws ConfigurationException
     */
    public function handle()
    {
        $sms = $this->service->getTwilioSmsObject($this->twilio, $this->row);
        $sms->send();

        $this->repo->insertEmailHistory($this->twilio->id, $this->row['id'], $sms);
        $this->twilio->oneFinishedSms();
    }

    /**
     *
     */
    public function failed()
    {
        //
    }
}
