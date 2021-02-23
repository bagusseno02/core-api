<?php
/**
 * Created by PhpStorm.
 * User: Sabriyan
 * Date: 4/1/2019
 * Time: 2:43 PM
 */

namespace coreapi\Utilities\Controllers;

use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Traits\SessionSettingTrait;
use coreapi\Utilities\Traits\SessionUserDataTrait;
use coreapi\Utilities\Controllers\JwtAuthController;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Models\UserModel;
use Validator;

class SessionSettingController extends JwtAuthController
{
    use SessionSettingTrait, SessionUserDataTrait;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function checkSession($credentials) {

        $userData = $credentials->user;
        $jwtToken = trim(str_replace('Bearer', '',$this->request->header('authorization')));

        $value = JWT::decode($jwtToken, config('coreapi.jwt_secret'), ['HS256']);

        $dateNow = \Carbon\Carbon::now('Asia/Jakarta')->toDateTimeString();
        $dateNow = new \DateTime($dateNow);

        if($value->sess_id) {
            //GET EXISTING SESSION USER DATA
            $sessionUserData = $this->getSessionUserDataByUserCompanyIdAndId($value->user->user_company_id, $value->sess_id);
        }
        else {
            //GET EXISTING SESSION USER DATA
            $sessionUserData = $this->getSessionUserDataByUserCompanyIdAndToken($value->user->user_company_id, $jwtToken);
        }

        if($sessionUserData) {

            $lastUpdate = new \DateTime($sessionUserData->updated_date);

            $diff=date_diff($lastUpdate, $dateNow);
            $hour = $diff->format('%h');
            $minutes = $diff->format('%i');
            $difference = ($hour * 60) + $minutes;

            if($difference < 1) {

            }
            else{

                //CHECK IS USING ENVIRONMENT SESSION SETTING
                if(getenv('SESSION_SETTING') == 'true') {

                    $sessionSetting = $this->getSessionSettingByCompanyIdAndType($userData->company_id, Constant::SESSION_SETTING_TYPE_KICK);

                    if($sessionSetting && $difference > $sessionSetting->duration) {
                        //DELETE SESSION USER DATA WHEN BIGGER THAN MAXIMUM DURATION
                        $this->deleteSessionUserData($sessionUserData);
                        return [
                            'error' => true,
                            'message' => 'Provided token is expired.'
                        ];
                    }
                    else {
                        //UPDATE SESSION USER DATA UPDATED DATE
                        $this->updateSessionUserDataUpdatedDate($sessionUserData);
                    }

                }

            }

        }
        else {
            return [
                'error' => true,
                'message' => 'Provided token is not valid.'
            ];
        }

        return [
            'error' => false,
            'message' => 'Token valid',
            'token' => $jwtToken
        ];


    }

}