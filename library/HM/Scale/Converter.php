<?php
class HM_Scale_Converter
{
    private static $_converter = null;
    private static $_scales = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$_converter === null) {
            self::init();
        }

        return self::$_converter;
    }

    public static function setInstance(HM_Scale_Converter $converter)
    {
        if (self::$_converter !== null) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Scale value convertor is already initialized');
        }

        self::$_converter = $converter;
        self::setScales();
    }

    protected static function init()
    {
        self::setInstance(new HM_Scale_Converter());
    }

    protected static function setScales()
    {
        // надо отсортировать по 'ScaleValue.value'
        $scales = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue');
        foreach ($scales as $scale) {
            self::$_scales[$scale->scale_id] = array(
                'values' => $scale->scaleValues->getList('value', 'value_id'),
                'texts' => $scale->scaleValues->getList('text', 'value_id'),
                'scale' => $scale
            );
        }
    }

    public function value2id($value, $scaleId)
    {
        if (isset(self::$_scales[$scaleId])) {
            
            if (self::$_scales[$scaleId]->scale->type == HM_Scale_ScaleModel::TYPE_CONTINUOUS) return $value;
            
            if (isset(self::$_scales[$scaleId]['values'][$value])) {
                return self::$_scales[$scaleId]['values'][$value];
            }
        }
        return HM_Scale_Value_ValueModel::VALUE_NA;
        //throw new HM_Exception(_('Scale value not found'));
    }

    public function value2idExt($value, $scaleId)//Даем ближайшее
    {
        if (isset(self::$_scales[$scaleId])) {
            if (self::$_scales[$scaleId]->scale->type == HM_Scale_ScaleModel::TYPE_CONTINUOUS) return $value;
            
            $minDistance = 999999;
            $currentScaleValue = -999999;//Значение шкалы, выбранное на текущий момент
            $bestValueId = HM_Scale_Value_ValueModel::VALUE_NA;
            foreach(self::$_scales[$scaleId]['values'] as $score=>$id) {
                $currentDistance = abs($score-$value);
                if($currentDistance<$minDistance || ($currentDistance==$minDistance && $score>$currentScaleValue)) { //<= означает, что если мы, например в шкале 1,2,3,4,5 ищем 3.5, то получим 4 (типа как округление)
                    $minDistance = $currentDistance;
                    $currentScaleValue = $score;
                    $bestValueId = $id;
                }
            }
            return $bestValueId;
        }
        return HM_Scale_Value_ValueModel::VALUE_NA;
        //throw new HM_Exception(_('Scale value not found'));
    }

    public function id2value($id, $scaleId)
    {
        if (isset(self::$_scales[$scaleId])) {
            
            if (self::$_scales[$scaleId]->scale->type == HM_Scale_ScaleModel::TYPE_CONTINUOUS) return $id;
            
            if (false !== ($value = array_search($id, self::$_scales[$scaleId]['values']))) {
                return $value;
            }
        }
        return HM_Scale_Value_ValueModel::VALUE_NA;
        //throw new HM_Exception(_('Scale value not found'));
    }

    public function id2text($id, $scaleId)
    {
        if (isset(self::$_scales[$scaleId])) {
            
            if (self::$_scales[$scaleId]->scale->type == HM_Scale_ScaleModel::TYPE_CONTINUOUS) return $id;
            
            if (false !== ($text = array_search($id, self::$_scales[$scaleId]['texts']))) {
                return $text;
            }
        }
        return HM_Scale_Value_ValueModel::VALUE_NA;
        //throw new HM_Exception(_('Scale value not found'));
    }
    
    public function value2text($value, $scaleId)
    {
        if (!$valueId = self::value2id($value, $scaleId)) {
            
            if (self::$_scales[$scaleId]->scale->type == HM_Scale_ScaleModel::TYPE_CONTINUOUS) return $value;
            
            $valueId = self::value2id(HM_Scale_Value_ValueModel::VALUE_NA, $scaleId);
        }
        return self::id2text($valueId, $scaleId);
    }
}