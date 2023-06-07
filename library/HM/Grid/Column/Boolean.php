<?php

class HM_Grid_Column_Boolean extends HM_Grid_SimpleColumn
{
    const TYPE = 'boolean';

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
        if ($value) {
            return _('Да');
        }

        return _('Нет');
    }

    public function getFilter()
    {
        return array(
            'values' => array(
                1 => _('Да'),
                0 => _('Нет'),
            ),
        );
    }

}