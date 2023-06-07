<?php
class HM_Section_SectionModel extends HM_Model_Abstract
{
    const ITEM_TYPE_SUBJECT = 'subject';
    const ITEM_TYPE_PROJECT = 'project';

    public static function getDefaultName()
    {
        return _('Раздел');
    }
}