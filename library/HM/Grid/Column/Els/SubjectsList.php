<?php

class HM_Grid_Column_Els_SubjectsList extends HM_Grid_ListColumn
{
    const TYPE = 'els.subjects-list';
    const COLUMN_CALLBACK_CLASS_NAME = 'HM_Grid_ColumnCallback_Els_SubjectsList';

    protected static function _getDefaultOptions()
    {
        return array(
            'title' => _('Курсы'),
        );
    }

}