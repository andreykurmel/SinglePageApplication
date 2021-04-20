<?php

namespace Vanguard\Classes;


use Illuminate\Encryption\Encrypter;

class TabldaEncrypter
{
    /**
     * @var string
     */
    protected static $key = 'fGO6vQqezNylsXxrP1d6nUZXCKCuDD3o';
    /**
     * @var Encrypter
     */
    protected static $crypter;

    /**
     * @return Encrypter
     */
    public static function crypter()
    {
        if (!self::$crypter) {
            self::$crypter = new Encrypter(self::$key, 'AES-256-CBC');
        }
        return self::$crypter;
    }

    /**
     * @param string $input
     * @return string
     */
    public static function encrypt(string $input)
    {
        $c = self::crypter();
        return $c->encryptString($input);
    }

    /**
     * @param string $input
     * @return string
     */
    public static function decrypt(string $input)
    {
        $c = self::crypter();
        try {
            return $c->decryptString($input);
        } catch (\Exception $e) {
            return $input;
        }
    }
}