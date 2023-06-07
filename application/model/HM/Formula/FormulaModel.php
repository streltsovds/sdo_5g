<?php
class HM_Formula_FormulaModel extends HM_Model_Abstract
{
    const TYPE_MARK    = 1;
    const TYPE_FINISH  = 2;
    const TYPE_GROUP   = 3;
    const TYPE_SUBJECT = 4;
    const TYPE_PENALTY = 5;

    public static function getFormulaExample()
    {
        return _("0-50:0(Плохо); "). _("51-100:1(Хорошо);");
    }

    public static function getFormulaTypes()
    {
        return  array(
            self::TYPE_MARK    => _("автоматическое выставление оценок за тест"),
            //self::TYPE_FINISH  => _("Условие окончания обучения"),
            self::TYPE_GROUP   => _("автоматическое формирование групп по результатам теста"),
            self::TYPE_SUBJECT => _("итоговая оценка за курс"),
            //self::TYPE_PENALTY => _(" штраф за несвоевременное выполнение занятия"),
        );
    }

    public static function getFormulaType($type)
    {
        $types = self::getFormulaTypes();
        return isset($types[$type]) ? $types[$type] : "";
    }

    public function getResult($mark)
    {
        $marks   = array();
        $formula = rtrim($this->formula, ';');
        if (!strlen($formula)) return false;
        $items = explode(';', $formula);

        $pattern = '/(\d*)-(\d*):(.*)/';
        $subPattern = '/(.*)\((.*)\)/';

        foreach ($items as $item) {
            preg_match($pattern, trim($item), $matches);
            if ($matches && ($matches[1] <= $mark) && ($matches[2] >= $mark)) {
                if (preg_match($subPattern, $matches[3], $matches2)) {
                    return array($matches2[1] => $matches2[2]);
                } else {
                    return array($matches[3] => $matches[3]);
                }
            }
        }

        return false;
    }

    public function getResultValue($mark)
    {
        if ($result = $this->getResult($mark)) {
            list($value, $name) = each($result);
        }
        return $value;
    }

    public function getResultName($mark)
    {
        $result = $this->getResult($mark);
        list($value, $name) = each($this->getResult($mark));
        return empty($name) ? $value : $name;
    }
}