<?php
namespace coreapi\Utilities\Helpers;

class DateHelper {

    public static function getMillisecond(): float
    {
        return round(microtime(true) * 1000);
    }
}