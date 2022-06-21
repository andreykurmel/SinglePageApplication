<?php

namespace Vanguard\Repositories\Tablda;


use Vanguard\Classes\TabldaEncrypter;
use Vanguard\Models\User\UserApiKey;
use Vanguard\Models\User\UserConnection;
use Vanguard\Models\User\UserEmailAccount;
use Vanguard\Models\User\UserPaymentKey;
use Vanguard\Services\Tablda\HelperService;

class UserConnRepository
{
    protected $service;

    /**
     * UserConnRepository constructor.
     */
    public function __construct()
    {
        $this->service = new HelperService();
    }

    /**
     * Load UserConn
     *
     * @param int $user_id
     * @return mixed
     */
    public function loadUserConns(int $user_id = null)
    {
        $conns = UserConnection::where('user_id', '=', $user_id)->get()->toArray();
        foreach ($conns as &$uc) {
            $uc['login'] = $uc['login'] ? TabldaEncrypter::decrypt($uc['login']) : '';
            $uc['pass'] = $uc['pass'] ? TabldaEncrypter::decrypt($uc['pass']) : '';
            $uc['pass'] = preg_replace('/./i', '*', $uc['pass']);
        }
        return $conns;
    }

    /**
     * Get UserConn
     *
     * @param int $cloud_id
     * @return mixed
     */
    public function getUserConn(int $cloud_id = null)
    {
        $conn = UserConnection::where('id', '=', $cloud_id)->first();
        if ($conn) {
            $conn = $conn->toArray();
            $conn['login'] = $conn['login'] ? TabldaEncrypter::decrypt($conn['login']) : '';
            $conn['pass'] = $conn['pass'] ? TabldaEncrypter::decrypt($conn['pass']) : '';
            return $conn;
        } else {
            return null;
        }
    }

    /**
     * Add UserConnection.
     *
     * @param $data
     * [
     *  +user_id: int,
     *  +name: string,
     *  +host: string,
     *  +login: string,
     *  +pass: string,
     *  +db: string,
     *  +table: string,
     * ]
     * @return mixed
     */
    public function addUserConn($data)
    {
        $data = $this->setUC($data);
        return UserConnection::create(array_merge($this->service->delSystemFields($data), $this->service->getModified(), $this->service->getCreated()));
    }

    /**
     * @param array $data
     * @return array
     */
    protected function setUC(array $data)
    {
        $data['login'] = $data['login'] ?? '' ? TabldaEncrypter::encrypt($data['login']) : '';
        $data['pass'] = $data['pass'] ?? '' ? TabldaEncrypter::encrypt($data['pass']) : '';
        return $data;
    }

    /**
     * Update UserConnection
     *
     * @param int $user_conn_id
     * @param $data
     * [
     *  +user_id: int,
     *  +name: string,
     *  +host: string,
     *  +login: string,
     *  +pass: string,
     *  +db: string,
     *  +table: string,
     * ]
     * @return array
     */
    public function updateUserConn($user_conn_id, $data)
    {
        $data = $this->setUC($data);
        return UserConnection::where('id', $user_conn_id)
            ->update(array_merge($this->service->delSystemFields($data), $this->service->getModified()));
    }

    /**
     * Delete UserConnection
     *
     * @param int $user_conn_id
     * @return mixed
     */
    public function deleteUserConn($user_conn_id)
    {
        return UserConnection::where('id', $user_conn_id)->delete();
    }

    /**
     * Get UserApi
     *
     * @param int $cloud_id
     * @return UserApiKey
     */
    public function getUserApi(int $cloud_id, bool $force = false)
    {
        $q = UserApiKey::where('id', '=', $cloud_id);
        if (!$force) {
            $q->where('user_id', '=', auth()->id());
        }
        return $q->first();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function addUserApi(array $data)
    {
        $data = $this->setDef($data);
        return UserApiKey::create($data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function setDef(array $data)
    {
        $data['name'] = $data['name'] ?? '';
        $data['notes'] = $data['notes'] ?? '';
        $data['is_active'] = $data['is_active'] ? 1 : 0;
        $data['type'] = $data['type'] ?? 'google';
        $data['key'] = $data['key'] ?? '' ? TabldaEncrypter::encrypt($data['key']) : '';
        if ($data['type'] == 'airtable') {
            $data['air_type'] = $data['air_type'] ?? 'public_rest';
        }
        return $this->service->delSystemFields($data);
    }

    /**
     * @param int $user_conn_id
     * @param array $data
     * @return mixed
     */
    public function updateUserApi(int $user_conn_id, array $data)
    {
        //Radio fields
        if ($data['_changed_field'] ?? '' == 'is_active' && $data['type'] ?? '') {
            UserApiKey::where('user_id', '=', auth()->id())
                ->where('type', '=', $data['type'])
                ->update(['is_active' => 0]);
        }
        //Update
        $data = $this->setDef($data);
        return UserApiKey::where('id', '=', $user_conn_id)
            ->update($data);
    }

    /**
     * @param int $user_conn_id
     * @return mixed
     */
    public function deleteUserApi(int $user_conn_id)
    {
        return UserApiKey::where('id', '=', $user_conn_id)->delete();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function addUserPayment(array $data)
    {
        $data = $this->setPaymentDef($data);
        $saved = UserPaymentKey::create($data);
        return $this->getUserPayment($saved->id);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function setPaymentDef(array $data)
    {
        $data['type'] = $data['type'] ?? 'stripe';
        $data['mode'] = $data['mode'] ?? 'sandbox';
        $data['secret_key'] = $data['secret_key'] ?? '' ? TabldaEncrypter::encrypt($data['secret_key']) : '';
        $data['public_key'] = $data['public_key'] ?? '' ? TabldaEncrypter::encrypt($data['public_key']) : '';
        return $this->service->delSystemFields($data);
    }

    /**
     * Get UserPayment
     *
     * @param int $this_id
     * @return mixed
     */
    public function getUserPayment(int $this_id)
    {
        $data = UserPaymentKey::where('id', '=', $this_id)
            ->where('user_id', '=', auth()->id())
            ->first();
        $data = $this->userPaymentDecrypt($data);
        return $data;
    }

    /**
     * @param $payment
     * @return mixed
     */
    public function userPaymentDecrypt($payment)
    {
        if ($payment) {
            $payment['secret_key'] = $payment['secret_key'] ?? '' ? TabldaEncrypter::decrypt($payment['secret_key']) : '';
            $payment['public_key'] = $payment['public_key'] ?? '' ? TabldaEncrypter::decrypt($payment['public_key']) : '';
        }
        return $payment;
    }

    /**
     * @param int $this_id
     * @param array $data
     * @return mixed
     */
    public function updateUserPayment(int $this_id, array $data)
    {
        $data = $this->setPaymentDef($data);
        return UserPaymentKey::where('id', '=', $this_id)
            ->update($data);
    }

    /**
     * @param int $this_id
     * @return mixed
     */
    public function deleteUserPayment(int $this_id)
    {
        return UserPaymentKey::where('id', '=', $this_id)->delete();
    }

    /**
     * Get UserApi
     *
     * @param int $cloud_id
     * @return mixed
     */
    public function getUserEmailAcc(int $cloud_id)
    {
        return UserEmailAccount::where('id', $cloud_id)
            ->where('user_id', auth()->id())
            ->first();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function addUserEmailAcc(array $data)
    {
        $data = $this->setDefEmailAcc($data);
        return UserEmailAccount::create($data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function setDefEmailAcc(array $data)
    {
        $data['app_pass'] = $data['app_pass'] ?? '' ? TabldaEncrypter::encrypt($data['app_pass']) : '';
        return $data;
    }

    /**
     * @param int $user_conn_id
     * @param array $data
     * @return mixed
     */
    public function updateUserEmailAcc(int $user_conn_id, array $data)
    {
        $data = $this->setDefEmailAcc($data);
        return UserEmailAccount::where('id', $user_conn_id)
            ->update($this->service->delSystemFields($data));
    }

    /**
     * @param int $user_conn_id
     * @return mixed
     */
    public function deleteUserEmailAcc(int $user_conn_id)
    {
        return UserEmailAccount::where('id', $user_conn_id)->delete();
    }
}