<?php

class HM_Grid_Column_Els_UserList extends HM_Grid_ListColumn
{
    const TYPE = 'els.user-list';
    const COLUMN_CALLBACK_CLASS_NAME = 'HM_Grid_ColumnCallback_Els_UserList';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Пользователи'),
        );
    }

}