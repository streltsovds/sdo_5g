<?php


class HM_String_Transform
{
    public static function crop($string, $length, $ending = '...')
    {
        return mb_strlen($string) > $length ? mb_substr($string, 0, $length) . $ending : $string;
    }
}