<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Services\Tablda\TableEmailAddonService;
use Vanguard\User;

class TableEmailAddonController extends Controller
{
    private $emailService;

    /**
     * TableEmailAddonController constructor.
     */
    public function __construct()
    {
        $this->emailService = new TableEmailAddonService();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return [
            'status' => $this->emailService->updateEmailSett($request->email_add_id, array_merge($request->fields, ['table_id' => $email->_table->id]))
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function sendEmail(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->sendEmails($email);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function cancelEmail(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->cancelEmail($email);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function previewEmail(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->previewEmail($email);
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableEmailAddonSetting
     */
    public function emailStatus(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $email;
    }
}
