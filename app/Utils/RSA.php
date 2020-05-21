<?php

namespace App\Utils;

/**
 * Created by PhpStorm.
 * User: wuhong
 * Date: 16/10/29
 * Time: 下午2:45
 */
class RSA
{
    //生成证书
    public static  function newOpenSSl()
    {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,           //字节数  512 1024 2048  4096 等
            "private_key_type" => OPENSSL_KEYTYPE_RSA,   //加密类型
        );
        $res = openssl_pkey_new($config);
        if ($res == false) return false;

        //提取私钥
        openssl_pkey_export($res, $private_key);
//生成公钥
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        return json_encode(['private_key' => $private_key, 'public_key' => $public_key]);

    }

    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public static function privEncrypt($data, $privateKey)
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, $privateKey) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public static function publicEncrypt($data, $publicKey)
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data, $encrypted, $publicKey) ? base64_encode($encrypted) : null;
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public static function privDecrypt($data, $privateKey)
    {
        if (!is_string($data)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($data), $decrypted, $privateKey)) ? $decrypted : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public static function publicDecrypt($data, $publicKey)
    {
        if (!is_string($data)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($data), $decrypted, $publicKey)) ? $decrypted : null;
    }
}