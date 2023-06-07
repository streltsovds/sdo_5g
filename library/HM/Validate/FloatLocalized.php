<?php

/**
 * ВНИМАНИЕ! Использовать только вместе с фильтром HM_Filter_FloatPoint.
 * В противном случае валидация корректно работать не будет!
 * В локалях, где требуется разделитель "запятая", неправильно работает валидация float:
 * 1. Могут ввести число через точку или запятую;
 * 2. Через точку валидация не пройдёт, т.к. для русской локали требуется разделитель "запятая"
 * 3. Через запятую - в базу запишется только целая часть;
 * Поэтому сначала нужно привести к виду с точкой, как в локали "en".
 */
class HM_Validate_FloatLocalized extends Zend_Validate_Float
{
    const INVALID   = 'floatInvalid';
    const NOT_FLOAT = 'notFloat';

    protected $_messageTemplates = [
        self::INVALID   => "Invalid type given, value should be float or integer",
        self::NOT_FLOAT => "'%value%' does not appear to be a float",
    ];

    public function isValid($value)
    {
        $this->_setValue($value);

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $type = gettype(filter_var($value, FILTER_VALIDATE_FLOAT));

        $firstLetter = $value[0];

        // '.15', '042' - подобные записи считаем невалидными
        // Но 0.15 считать валидными
        if (!is_numeric($firstLetter)) {
            $this->_error(self::NOT_FLOAT);
            return false;
        } elseif (
            preg_match("/^\\d+\\.\\d+$/", $value) !== 1
        ) {
            $this->_error(self::INVALID);
            return false;
        }


        if ($type === 'double') {
            return true;
        }

        $this->_error(self::NOT_FLOAT);
        return false;
    }
}
