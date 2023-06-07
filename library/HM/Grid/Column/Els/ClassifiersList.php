<?php

class HM_Grid_Column_Els_ClassifiersList extends HM_Grid_ListColumn
{
    const TYPE = 'els.сlassifiers-list';
    const COLUMN_CALLBACK_CLASS_NAME = 'HM_Grid_ColumnCallback_Els_ClassifiersList';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Классификаторы'),
        );
    }

}