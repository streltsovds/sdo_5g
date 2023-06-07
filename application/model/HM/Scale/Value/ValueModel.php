<?php
class HM_Scale_Value_ValueModel extends HM_Model_Abstract
{
    const VALUE_NA = -1;

    const VALUE_BINARY_ON = 1;
    const VALUE_TERNARY_ON = 1;
    const VALUE_TERNARY_OFF = 0;
    
    protected $_primaryName = 'value_id';

    static public function getTextStatus($scaleId, $value)
    {
        if (empty($scaleId)) {
            return $scaleId == 0 ? "" : $value;
        }

        if ($value == self::VALUE_NA || is_null($value) || $value === '') {
            return _("");
        }
        switch ($scaleId) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                if ($value == self::VALUE_BINARY_ON){
                    return _("Пройдено");
                }
            case HM_Scale_ScaleModel::TYPE_TERNARY:
                if ($value == self::VALUE_TERNARY_ON) {
                     return _("Пройдено успешно");
                } elseif ($value == self::VALUE_TERNARY_OFF) {
                    return _("Пройдено неуспешно");
                }
            default:
                return $value;
        }
    }

    static public function getAllTextStatuses()
    {
        return array(
            _("Пройдено") => self::VALUE_BINARY_ON,
            _("Пройдено успешно") => self::VALUE_TERNARY_ON,
            _("Пройдено неуспешно") => self::VALUE_TERNARY_OFF
        );
    }

    static public function getVariants($scaleId)
    {
        switch ($scaleId) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                $variants_values = array(-1, 1);
            break;
            case HM_Scale_ScaleModel::TYPE_TERNARY:
                $variants_values = array(-1, 0, 1);
            break;
            default:
                return array();
}

        foreach($variants_values as $vv)
                $variants[$vv] = HM_Scale_Value_ValueModel::getTextStatus($scaleId, $vv);

        return $variants;
    }

}
