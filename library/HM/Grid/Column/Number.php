<?php

class HM_Grid_Column_Number extends HM_Grid_SimpleColumn
{
    const TYPE = 'number';

    protected static function _getDefaultOptions()
    {
        return array(
            'decimals'     => 2,   // округление до 2 знаков после запятой
            'decPoint'     => '.', // разделитель целой и дробной частей
            'thousandsSep' => ' ', // разделитель тысячных
            'emptyValue'   => '',  // значение при пустом значении (0, '', etc)
        );
    }

    public function getCallBack()
    {
        return array(
            'function' => array($this, 'updateColumn'),
            'params' => array(
                '{{'.$this->getFieldName().'}}',
            ),
        );
    }

    public function updateColumn($value)
    {
        if (!$value) {
            return $this->getOption('emptyValue');
        }

        $dec          = $this->getOption('decimals');
        $decPoint     = $this->getOption('decPoint');
        $thousandsSep = $this->getOption('thousandsSep');

        return number_format((int) $value, $dec, $decPoint, $thousandsSep);

    }

}