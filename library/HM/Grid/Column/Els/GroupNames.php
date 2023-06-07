<?php

class HM_Grid_Column_Els_GroupNames extends HM_Grid_ListColumn
{
    const TYPE = 'els.group-names';
    const COLUMN_CALLBACK_CLASS_NAME = 'HM_Grid_ColumnCallback_Els_GroupNamesList';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Группы'),
        );
    }

}