<?php

class HM_View_Helper_FullUrlEncode extends Zend_View_Helper_Url
{
    /**
     * Полный енкод адресов, включая символы, которые обычный urlencode не хочет брать
     * Обратно корректно декодится через обычный urldecode, включая эти "хитрые" символы
     *
     * @param $url
     * @return mixed|string
     */
    public static function fullUrlEncode($url)
    {
        $url = urlencode($url);
        $url = str_ireplace([".", "\""], ["%2E", "&quot;"], $url);
        return $url;
    }
}