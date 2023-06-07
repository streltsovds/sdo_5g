<?php

class HM_Grid_Column_Els_TagsList extends HM_Grid_ListColumn
{
    const TYPE = 'els.tags-list';
    const COLUMN_CALLBACK_CLASS_NAME = 'HM_Grid_ColumnCallback_Els_TagsList';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Теги'),
        );
    }

}