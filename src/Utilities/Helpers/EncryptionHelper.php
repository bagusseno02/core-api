<?php
/**
 * Created by PhpStorm.
 * User: alysangadji
 * Date: 20/03/19
 * Time: 10.17
 */
namespace coreapi\Utilities\Helpers;

use phpseclib\Crypt\RSA;

class EncryptionHelper {

    const secretKey = 'YyYwUGu6ekb42ledYDc6h62B1L4N8iOK';
    const secretIv = 'fAgzAqEzUucGg0jT3PlLZAfKn2f0FwfR';
    const encryptMethod = "AES-256-CBC";
    const algo = 'sha256';
    const privateKeyPath = 'config/secret/privateKey';
    const publicKeyPath = 'config/secret/publicKey';

    public static function encrypt($string) {

        // hash
        $key = hash(self::algo, self::secretKey);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash(self::algo, self::secretIv), 0, 16);

        $output = openssl_encrypt($string, self::encryptMethod, $key, 0, $iv);

        return base64_encode($output);
    }

    public static function decrypt($string) {
        $isNeedEncrypt = env('IS_NEED_ENCRYPT_ENV', false);
        if (!$isNeedEncrypt || empty($isNeedEncrypt)) { return $string; }

        // hash
        $key = hash(self::algo, self::secretKey);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash(self::algo, self::secretIv), 0, 16);

        return openssl_decrypt(base64_decode($string), self::encryptMethod, $key, 0, $iv);

    }

    public static function decryptGeneral($string) {
        // hash
        $key = hash(self::algo, self::secretKey);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash(self::algo, self::secretIv), 0, 16);

        return openssl_decrypt(base64_decode($string), self::encryptMethod, $key, 0, $iv);

    }

    public static function encryptRSA($string)
    {
        $pathPublic = base_path(self::publicKeyPath);
        if (!file_exists($pathPublic)) {
            throw new \Exception("File $pathPublic not found");
        }
        $publicKey = file_get_contents($pathPublic);

        $rsa = new RSA();
        $rsa->loadKey($publicKey);

        $result = $rsa->encrypt($string);

        return $result;
    }

    public static function decryptRSA($data)
    {
        $pathSecret = base_path(self::privateKeyPath);

        if (!file_exists($pathSecret)) {
            throw new \Exception("File $pathSecret not found");
        }

        $privateKey = file_get_contents($pathSecret);

        $rsa = new RSA();
        $rsa->loadKey($privateKey);

        $result = $rsa->decrypt($data);

        return $result;
    }
}