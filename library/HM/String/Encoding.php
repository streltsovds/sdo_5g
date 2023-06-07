<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 3/20/19
 * Time: 12:39 PM
 */

class HM_String_Encoding
{
    const UTF8_ENCODING = 'UTF-8';
    const WIN1251_ENCODING = 'Windows-1251';

    public static function detectEncoding($str)
    {
        $encodings = [self::UTF8_ENCODING, self::WIN1251_ENCODING];
        foreach($encodings as $encoding) {
            if ($str == iconv($encoding, $encoding, $str)) {
                return $encoding;
            }
        }
        return self::UTF8_ENCODING;
    }
}