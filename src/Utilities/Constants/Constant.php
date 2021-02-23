<?php
/**
 * Created by PhpStorm.
 * User: alysangadji
 * Date: 17/12/18
 * Time: 13.27
 */
namespace coreapi\Utilities\Constants;

class Constant {

    //FIELD ACTIVE
    const ACTIVE  =  1;
    const NO_ACTIVE = 0;

    //FIELD STATE IN TABLE
    const STATE_ACTIVE  =  1;
    const STATE_PENDING = 2;
    const STATE_BLOCKED = 3;
    const STATE_DELETED = 4;

    //FIELD STATUS LOG IN TABLE
    const STATUS_LOG_CREATED = 1;
    const STATUS_LOG_UPDATED = 2;
    const STATUS_LOG_DELETED = 3;

    // *_trail table status
    const STATUS_TRAIL_CREATE = 1;
    const STATUS_TRAIL_UPDATE = 2;

    const TYPE_FLOW_ORDER = 1;
    const TYPE_FLOW_NO_ORDER = 2;
    const TYPE_FLOW_MINIMUM = 3;

    //Session Setting
    const SESSION_SETTING_TYPE_KICK = 1;

    //PAGINATION TYPES
    const TYPE_PAGINATION_PAGE = 1;
    const TYPE_PAGINATION_LOAD_MORE = 2;

    //LOG TYPE
    const LOG_WRITE_DATABASE = 'database';
    const LOG_WRITE_FILE = 'file';

    //Type User Agent Device
    const USER_AGENT_DESKTOP = 1;
    const USER_AGENT_ANDROID = 2;
    const USER_AGENT_IOS = 3;
    const USER_AGENT_ROBOT = 4;
}