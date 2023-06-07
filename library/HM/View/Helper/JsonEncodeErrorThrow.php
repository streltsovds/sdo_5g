<?php
class HM_View_Helper_JsonEncodeErrorThrow extends HM_View_Helper_Abstract
{
    /**
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     * @throws Exception
     */
    public function jsonEncodeErrorThrow($data, $params = HM_Json::JSON_ENCODE_OPTS_DEFAULT)
    {
        return HM_Json::encodeErrorThrow($data, $params);
    }
}
