<?php
class HM_Json {
    /**
     * `JSON_HEX_APOS` - экранирование одинарной кавычки для передачи во vue через html-атрибуты
     * `:myValue='<?php echo $json ?>'`
     *
     * `JSON_HEX_AMP` - если попытаться закодировать html-entity "&quot;" без этого флага и передать в шаблон vue, возникнет ошибка "Vue: Error compiling template: invalid expression: Invalid or unexpected token in"
     */
    public const JSON_ENCODE_OPTS_DEFAULT = JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_AMP | JSON_PRETTY_PRINT;

    public const JSON_ENCODE_OPTS_BACKBONE_COMPATIBLE = self::JSON_ENCODE_OPTS_DEFAULT & ~JSON_PRETTY_PRINT;

    public static function getLastErrorString() {
        return 'json_encode error ' . json_last_error() . ': ' . json_last_error_msg();
    }

    /**
     * TODO переименовать в encodeErrorLog?
     *
     * Просто кодирует и не падает
     *
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     */
    public static function encodeErrorSkip($data, $params = HM_Json::JSON_ENCODE_OPTS_DEFAULT)
    {
        $json = json_encode($data, $params);

        if ($json === false) {
            $json = '';
            Zend_Registry::get('log_system')->debug('JSON encode error: ' . self::getLastErrorString());
        }

        return $json;
    }

    /**
     * Явно выбрасывает ошибку, если будут проблемы с кодированием
     *
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     * @throws Exception
     */
    public static function encodeErrorThrow($data, $params = HM_Json::JSON_ENCODE_OPTS_DEFAULT)
    {
        $json = json_encode($data, $params);

        if ($json === false) {
            throw new Error(self::getLastErrorString());
        }

        return $json;
    }

    /**
     * В случае ошибки возвращает её в json['error'] (для обработки ответа frontend)
     *
     * `JSON_HEX_APOS` - экранирование одинарной кавычки для передачи во vue через html-атрибуты
     * `:myValue='<?php echo $json ?>'`
     *
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     */
    public static function encodeErrorReturn($data, $params = JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_PRETTY_PRINT)
    {
        $json = json_encode($data, $params);

        if ($json === false) {
            $json = json_encode([
                'error' => self::getLastErrorString(),
            ]);
        }

        return $json;
    }
}
