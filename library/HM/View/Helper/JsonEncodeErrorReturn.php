<?php
class HM_View_Helper_JsonEncodeErrorReturn extends HM_View_Helper_Abstract
{
    /**
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     */
    public function jsonEncodeErrorReturn($data, $params = HM_Json::JSON_ENCODE_OPTS_DEFAULT)
    {
        return HM_Json::encodeErrorReturn($data, $params);
    }
}
