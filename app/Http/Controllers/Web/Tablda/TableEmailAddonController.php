<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Table\TableData;
use Vanguard\Services\Tablda\TableEmailAddonService;
use Vanguard\Services\Tablda\TableService;
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
     * @return \Vanguard\Models\Table\TableEmailAddonSetting
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insert(Request $request)
    {
        $table = (new TableService())->getTable($request->table_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $table]);
        return $this->emailService->insertEmailSett(array_merge($request->fields, ['table_id' => $table->id]));
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return [
            'status' => $this->emailService->deleteEmailSett($request->email_add_id)
        ];
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableEmailAddonSetting
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function copyAdn(Request $request)
    {
        $fr = $this->emailService->getEmailSett($request->from_adn_id);
        $to = $this->emailService->getEmailSett($request->to_adn_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $fr->_table]);
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $to->_table]);
        return $this->emailService->copyAdn($request->from_adn_id, $request->to_adn_id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sendEmail(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->sendEmails($email, $request->row_id ?: null);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function previewEmail(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->previewEmail($email, $request->row_id ?: null, $request->special ?: '');
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableEmailAddonSetting
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function emailStatus(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $email;
    }

    /**
     * @param Request $request
     * @return \Vanguard\Models\Table\TableEmailAddonSetting
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function clearHistory(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->clearHistory($email, $request->row_id ?: null);
    }
}
