<?php
/**
 * Created by PhpStorm.
 * User: Dicky Eka Ramadhan
 * Date: 19/03/2019
 * Time: 17:18
 */

namespace coreapi\Utilities\Traits;

use coreapi\Utilities\Models\SessionSetting;
use coreapi\Utilities\Constants\Constant;

Trait SessionSettingTrait {

    public static function getSessionSettingByCompanyIdAndType($company_id,$type){
        return SessionSetting::where('company_id', $company_id)
            ->where('type', $type)
            ->where('state', Constant::STATE_ACTIVE)
            ->first();
    }

}