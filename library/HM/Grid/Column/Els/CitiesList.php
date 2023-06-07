<?php

class HM_Grid_Column_Els_CitiesList extends HM_Grid_ListColumn
{
    const TYPE = 'els.cities-list';
    const COLUMN_CALLBACK_CLASS_NAME = 'HM_Grid_ColumnCallback_Els_CitiesList';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Город'),
        );
    }

}