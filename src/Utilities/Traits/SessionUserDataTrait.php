<?php
/**
 * Created by PhpStorm.
 * User: Dicky Eka Ramadhan
 * Date: 19/03/2019
 * Time: 17:18
 */

namespace coreapi\Utilities\Traits;

use coreapi\Utilities\Models\SessionUserData;
use coreapi\Utilities\Constants\Constant;

Trait SessionUserDataTrait {

    public static function getSessionUserDataByUserCompanyId($user_company_id){
        return SessionUserData::where('user_company_id', $user_company_id)
            ->where('state', Constant::STATE_ACTIVE)
            ->get();
    }

    public static function getSessionUserDataByUserCompanyIdAndToken($user_company_id, $token){
        return SessionUserData::where('user_company_id', $user_company_id)
            ->where('token', $token)
            ->where('state', Constant::STATE_ACTIVE)
            ->first();
    }

    public static function getSessionUserDataByUserCompanyIdAndId($user_company_id, $id){
        return SessionUserData::where('user_company_id', $user_company_id)
            ->where('id', $id)
            ->where('state', Constant::STATE_ACTIVE)
            ->first();
    }

    public static function createSessionUserData($params) {

        $sessionUserData = new SessionUserData();
        $sessionUserData->user_company_id = $params['user_company_id'];
        $sessionUserData->type = $params['type'];
        $sessionUserData->token = $params['token'];
        $sessionUserData->created_date = \Carbon\Carbon::now('Asia/Jakarta');
        $sessionUserData->updated_date = \Carbon\Carbon::now('Asia/Jakarta');
        $sessionUserData->state = Constant::STATE_ACTIVE;

        $sessionUserData->save();

        return $sessionUserData;
    }

    public static function updateSessionUserDataUpdatedDate($sessionUserData) {

        $sessionUserData->updated_date = \Carbon\Carbon::now('Asia/Jakarta');
        $sessionUserData->save();

        return $sessionUserData;
    }

    public static function deleteSessionUserData($sessionUserData) {

        $sessionUserData->state = Constant::STATE_DELETED;
        $sessionUserData->save();

        return $sessionUserData;
    }

}