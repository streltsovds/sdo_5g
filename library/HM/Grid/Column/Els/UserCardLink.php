<?php

class HM_Grid_Column_Els_UserCardLink extends HM_Grid_SimpleColumn
{
    const TYPE = 'els.user-card-link';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Пользователь'),
            'column_id' => 'MID',
        );
    }

    public function getCallBack()
    {
        $callBack = new HM_Grid_ColumnCallback_Els_UserCardLink();

        $id   = $this->getOption('column_id');
        $name = $this->getFieldName();

        return $callBack->getCallback('{{'.$id.'}}', '{{'.$name.'}}');

    }

}