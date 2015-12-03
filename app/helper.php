<?php

namespace App;

class Helper {

    private static $salt = 'It is out top secret encryption key';

    public static function copy($source, $target) {
        if (!is_dir($source)) {
            copy($source, $target);
            return;
        }

        @mkdir($target);
        $d = dir($source);
        $navFolders = array('.', '..');
        while (false !== ($fileEntry=$d->read() )) {
            
            if (in_array($fileEntry, $navFolders) ) {
                continue;
            }

            $s = "$source/$fileEntry";
            $t = "$target/$fileEntry";
            self::copy($s, $t);
        }
        $d->close();
    }

    public static function generateCode($length) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    public static function filterInput($value) {
        $return = '';
        for($i = 0; $i < strlen($value); ++$i) {
            $char = $value[$i];
            $ord = ord($char);
            if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
                $return .= $char;
            else
                $return .= '\\x' . dechex($ord);
        }
        return $return;
    }

    private static function salt() {
        return self::$salt;
    }

    public static function compareHash($hash, $password) {
        $passwordHash = self::hashIt($password);
        return ($hash == $passwordHash);
    }

    public static function hashIt($password) {
//        return password_hash($password, PASSWORD_BCRYPT);
        return crypt($password, self::$salt);
    }
}

