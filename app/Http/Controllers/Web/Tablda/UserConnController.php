<?php

namespace Vanguard\Http\Controllers\Web\Tablda;


use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vanguard\Models\Table\TableData;
use Vanguard\Repositories\Tablda\UserConnRepository;
use Vanguard\Services\Tablda\DDLService;
use Vanguard\Services\Tablda\TableService;

class UserConnController extends Controller
{
    private $connRepository;

    /**
     * UserConnController constructor.
     * 
     * @param UserConnRepository $connRepository
     */
    public function __construct(UserConnRepository $connRepository)
    {
        $this->connRepository = $connRepository;
    }

    /**
     * Add DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function insert(Request $request){
        $usr_conn = $this->connRepository->addUserConn( array_merge($request->fields, ['user_id' => auth()->id()]) );
        return $usr_conn;
    }

    /**
     * Update DDL.
     *
     * @param Request $request
     * @return array
     */
    public function update(Request $request){
        $conn = $this->connRepository->getUserConn($request->user_conn_id);
        return [
            'status' => $conn && $conn->user_id == auth()->id()
                ? $this->connRepository->updateUserConn( $request->user_conn_id, array_merge($request->fields, ['user_id' => auth()->id()]) )
                : false
        ];
    }

    /**
     * Delete DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request){
        $conn = $this->connRepository->getUserConn($request->user_conn_id);
        return [
            'status' => $conn && $conn->user_id == auth()->id()
                ? $this->connRepository->deleteUserConn( $request->user_conn_id )
                : false
        ];
    }

    /**
     * Add DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function insertApi(Request $request){
        $usr_conn = $this->connRepository->addUserApi( array_merge($request->fields, ['user_id' => auth()->id()]) );
        $usr_conn['key'] = $usr_conn['key']??'' ? TabldaEncrypter::decrypt($usr_conn['key']) : '';
        return $usr_conn;
    }

    /**
     * Update DDL.
     *
     * @param Request $request
     * @return array
     */
    public function updateApi(Request $request){
        $api = $this->connRepository->getUserApi($request->user_api_id);
        return [
            'status' => $api && $api->user_id == auth()->id()
                ? $this->connRepository->updateUserApi( $request->user_api_id, array_merge($request->fields, ['user_id' => auth()->id()]) )
                : false
        ];
    }

    /**
     * Delete DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function deleteApi(Request $request){
        $api = $this->connRepository->getUserApi($request->user_api_id);
        return [
            'status' => $api && $api->user_id == auth()->id()
                ? $this->connRepository->deleteUserApi( $request->user_api_id )
                : false
        ];
    }

    /**
     * Add DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function insertPayment(Request $request){
        $usr_conn = $this->connRepository->addUserPayment( array_merge($request->fields, ['user_id' => auth()->id()]) );
        return $usr_conn;
    }

    /**
     * Update DDL.
     *
     * @param Request $request
     * @return array
     */
    public function updatePayment(Request $request){
        $api = $this->connRepository->getUserPayment($request->this_id);
        return [
            'status' => $api && $api->user_id == auth()->id()
                ? $this->connRepository->updateUserPayment( $request->this_id, array_merge($request->fields, ['user_id' => auth()->id()]) )
                : false
        ];
    }

    /**
     * Delete DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function deletePayment(Request $request){
        $api = $this->connRepository->getUserPayment($request->this_id);
        return [
            'status' => $api && $api->user_id == auth()->id()
                ? $this->connRepository->deleteUserPayment( $request->this_id )
                : false
        ];
    }

    /**
     * Add DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function insertEmailAcc(Request $request){
        $usr_conn = $this->connRepository->addUserEmailAcc( array_merge($request->fields, ['user_id' => auth()->id()]) );
        $usr_conn['app_pass'] = $usr_conn['app_pass']??'' ? TabldaEncrypter::decrypt($usr_conn['app_pass']) : '';
        return $usr_conn;
    }

    /**
     * Update DDL.
     *
     * @param Request $request
     * @return array
     */
    public function updateEmailAcc(Request $request){
        $api = $this->connRepository->getUserEmailAcc($request->user_email_acc_id);
        return [
            'status' => $api && $api->user_id == auth()->id()
                ? $this->connRepository->updateUserEmailAcc( $request->user_email_acc_id, array_merge($request->fields, ['user_id' => auth()->id()]) )
                : false
        ];
    }

    /**
     * Delete DDL
     *
     * @param Request $request
     * @return mixed
     */
    public function deleteEmailAcc(Request $request){
        $api = $this->connRepository->getUserEmailAcc($request->user_email_acc_id);
        return [
            'status' => $api && $api->user_id == auth()->id()
                ? $this->connRepository->deleteUserEmailAcc( $request->user_email_acc_id )
                : false
        ];
    }
}
