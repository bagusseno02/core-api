<?php
/**
 * Created by PhpStorm.
 * User: alysangadji
 * Date: 13/12/18
 * Time: 12.31
 */
namespace coreapi\Utilities\Helpers;

class StringHelper {

    /**
     * Convert string to UTF-8
     *
     * @param string $string
     * @return string
     */
    public static function convertToUtf8(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8');
    }

    /**
     * Format number to IDR format
     * @param double $str
     * @return string
     */
    public static function formatNumber(float $str): string
    {
        return number_format($str, 2, '.', ',');
    }

    /**
     * Clean string from tab and newline.
     * @param string $string
     * @return string
     */
    public static function cleanString(string $string): string
    {
        $string = preg_replace('/[\t]+/', ' ', $string);
        $string = preg_replace('/\r\n|\r|\n/', '\n', $string);

        return $string;
    }

    public static function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public static function contains($strSource, $strFind) {
        if (empty($strSource) || empty($strFind)) {
            return false;
        }

        if (strpos($strSource, $strFind) !== false) {
            return true;
        } else { return false; }
    }
}