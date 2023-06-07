<?php

class HM_Grid_Column_Els_SubjectCardLink extends HM_Grid_SimpleColumn
{
    const TYPE = 'els.subject-card-link';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Курс'),
            'column_id' => 'subid',
        );
    }

    public function getCallBack()
    {
        $callBack = new HM_Grid_ColumnCallback_Els_SubjectCardLink();

        $id   = $this->getOption('column_id');
        $name = $this->getFieldName();

        return $callBack->getCallback('{{'.$id.'}}', '{{'.$name.'}}');

    }

}