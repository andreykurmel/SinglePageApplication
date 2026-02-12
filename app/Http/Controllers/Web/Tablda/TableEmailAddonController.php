<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Tablda\Email\SendSingleEmailRequest;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\TableEmailRight;
use Vanguard\Repositories\Tablda\TableFieldRepository;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\TableEmailAddonService;
use Vanguard\Services\Tablda\TableService;
use Vanguard\User;

class TableEmailAddonController extends Controller
{
    use CanEditAddon;

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
        $this->canEditAddon($table, 'email');
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
        $this->canEditAddonItem($email->_table, $email->_email_rights());
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
        $this->canEditAddonItem($email->_table, $email->_email_rights());
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
        $this->canEditAddonItem($fr->_table, $fr->_email_rights());
        $this->canEditAddonItem($to->_table, $to->_email_rights());
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
        $this->canEditAddonItem($email->_table, $email->_email_rights());
        return $this->emailService->sendEmails($email, $request->row_id ?: null);
    }

    /**
     * @param SendSingleEmailRequest $request
     * @return string[]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sendSingleEmail(SendSingleEmailRequest $request)
    {
        $api = null;
        if ($request->twilio_google_acc_id) {
            $api = (new UserConnRepository())->getUserEmailAcc($request->twilio_google_acc_id);
        }
        if ($request->twilio_sendgrid_acc_id) {
            $api = (new UserConnRepository())->getUserApi($request->twilio_sendgrid_acc_id);
        }

        $field = (new TableFieldRepository())->getField($request->field_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->authorizeForUser($user, 'load', [TableData::class, $field->_table]);
        return $this->emailService->sendSingleEmail($api, $request->email_data, $field, $request->row_id);
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
        $this->canEditAddonItem($email->_table, $email->_email_rights());
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
        $this->canViewAddonItem($email->_table, $email->_email_rights());
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
        $this->canEditAddonItem($email->_table, $email->_email_rights());
        return $email;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function clearHistory(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_add_id);
        $user = auth()->check() ? auth()->user() : new User();
        $this->canEditAddonItem($email->_table, $email->_email_rights());
        return $this->emailService->clearHistory($email, $request->row_id ?: null, $request->history_id ?: null);
    }

    /**
     * @param Request $request
     * @return TableEmailRight
     * @throws AuthorizationException
     */
    public function toggleEmailRight(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->toggleEmailRight($email, $request->permission_id, $request->can_edit);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws AuthorizationException
     */
    public function delEmailRight(Request $request)
    {
        $email = $this->emailService->getEmailSett($request->email_id);
        $this->authorizeForUser(auth()->user(), 'isOwner', [TableData::class, $email->_table]);
        return $this->emailService->deleteEmailRight($email, $request->permission_id);
    }
}
