<?php
class HM_Formula_FormulaService extends HM_Service_Abstract
{
    public function getListSource($subjectId)
    {
        $listSource = $this->getSelect();
        $listSource->from(array('f' => 'formula'), array(
            'id' => 'f.id',
            'formula_id' => 'f.id',
            'f.name',
            'formula' => new Zend_Db_Expr('CAST(f.formula as varchar(max))'),
            'ftype' => 'f.type',
            'f.CID',
        ));

        if ($subjectId) {
            $listSource->where('CID in (?)', array($subjectId, 0));
            $listSource->order('CID DESC');
        } else {
            $listSource->where('CID=0');
        }

        return $listSource;
    }


    public function getById($formulaId)
    {
        static $formulaCache = array();

        if (!isset($formulaCache[$formulaId])) {
            $formulaCache[$formulaId] = $this->getOne($this->find($formulaId));
        }

        return $formulaCache[$formulaId];
    }
    /**
     * Переводит значения формулы в аобсолютные величины
     * в рамках значений от $scaleMin до $scaleMax
     * @param $formula строковое представление вормулы
     * @param $scaleMin минимальное значение шкалы
     * @param $scaleMax максимальное значение шкалы
     * @return array|bool ключами массива являются числовые представления оценок,
     *                    в качестве значений - соответствующие абсолютные величины шкалы
     * @todo: Оценки приводятся к числовому представления, с нечисловыми оценками будет магия.
     */
    public static function getFormulaMarksByScale($formula, $scaleMin, $scaleMax)
    {
        $marks   = array();
        $formula = rtrim($formula, ';');
        if (!strlen($formula)) return false;

        $items = explode(';', $formula);

        foreach ($items as $item) {
            list($null, $mark) = explode(':', $item);
            $marks[] = (int) $mark;
        }

        if (!count($marks)) return false;

        $step      = ($scaleMax - $scaleMin)/(count($marks) - 1);
        $result[0] = $scaleMin;

        for ($i = 1; $i <= (count($marks) - 1); $i++ ) {
            $result[$i] = $step*$i;
        }

        $result[(count($marks) - 1)] = $scaleMax;

        return array_combine($marks, $result);
    }
}