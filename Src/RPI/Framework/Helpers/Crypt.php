<?php

namespace RPI\Framework\Helpers;

/**
 * Encryption helpers
 * @author Matt Dunn
 */
class Crypt
{
    private function __construct()
    {
    }

    const ENC_CIPHER = MCRYPT_RIJNDAEL_256;

    /**
     * Encrypt a value
     * @param  string $key
     * @param  string $data
     * @return string Base64 encoded encrypted string
     */
    public static function encrypt($key, $data)
    {
        srand((double) microtime() * 1000000);
        $iv_size = mcrypt_get_iv_size(self::ENC_CIPHER, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        return base64_encode(mcrypt_encrypt(self::ENC_CIPHER, substr($key, 0, $iv_size), $data, MCRYPT_MODE_ECB, $iv));
    }

    /**
     * Decrypt a value
     * @param  string $key
     * @param  string $data Base64 encoded encrypted string
     * @return string
     */
    public static function decrypt($key, $data)
    {
        srand((double) microtime() * 1000000);
        $iv_size = mcrypt_get_iv_size(self::ENC_CIPHER, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        return rtrim(
            (mcrypt_decrypt(self::ENC_CIPHER, substr($key, 0, $iv_size), base64_decode($data), MCRYPT_MODE_ECB, $iv)),
            "\0"
        );
    }

    /**
     * Return a hash
     */
    public static function generateHash($key)
    {
        return hash("sha256", $key.":".\RPI\Framework\Helpers\Uuid::v4());
    }
}
