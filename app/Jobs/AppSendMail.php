<?php

namespace Vanguard\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AppSendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $files;

    /**
     * AppSendMail constructor.
     * @param array $request
     * @param array $files : [
     *  {
     *      path: string,
     *      name: string
     *  },
     * ...
     * ]
     */
    public function __construct(array $request, array $files = [])
    {
        $this->request = $request;
        $this->files = $files;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        $files = $this->files;
        if (!empty($request['email'])) {
            Mail::send(
                'emails.contact',
                [
                    'mes' => $request['message'] ?? '',
                    'email' => $request['email'],
                    'company' => $request['company'] ?? '',
                    'pref_time' => $request['pref_time'] ?? '',
                ],
                function ($mail) use ($request, $files) {
                    $mail->from( config('mail.accounts.main.email') );
                    $mail->to( config('mail.accounts.main.email') );
                    $mail->subject($request['subject'] ?? '');
                    foreach ($files as $file) {
                        $mail->attach($file['path'], ['as' => $file['name']]);
                        Storage::delete($file['path']);
                    }
                }
            );
        }
    }

    public function failed()
    {
        //
    }
}
