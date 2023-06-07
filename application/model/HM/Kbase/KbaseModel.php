<?php
class HM_Kbase_KbaseModel extends HM_Model_Abstract
{
    const TYPE_RESOURCE = 'resource';
    const TYPE_COURSE = 'course';
    const TYPE_TEST = 'test';
    const TYPE_POLL = 'poll';
    const TYPE_TASK = 'task';

    public static function getKbaseAndSphinxTypesMap($kbaseType = null)
    {
        $types = [
            self::TYPE_RESOURCE => HM_Search_Sphinx::TYPE_RESOURCE,
            self::TYPE_COURSE => HM_Search_Sphinx::TYPE_COURSE,
            self::TYPE_POLL => HM_Search_Sphinx::TYPE_POLL,
            self::TYPE_TASK => HM_Search_Sphinx::TYPE_TASK,
            self::TYPE_TEST => HM_Search_Sphinx::TYPE_TEST,
        ];

        if(!empty($kbaseType)) {
            return $types[$kbaseType];
        } else {
            return $types;
        }
    }

    public static function getKbaseAndEventTypesMap($kbaseType = null)
    {
        $types = [
            self::TYPE_RESOURCE => HM_Event_EventModel::TYPE_RESOURCE,
            self::TYPE_COURSE => HM_Event_EventModel::TYPE_COURSE,
            self::TYPE_POLL => HM_Event_EventModel::TYPE_POLL,
            self::TYPE_TASK => HM_Event_EventModel::TYPE_TASK,
            self::TYPE_TEST => HM_Event_EventModel::TYPE_TEST,
        ];

        if(!empty($kbaseType)) {
            return $types[$kbaseType];
        } else {
            return $types;
        }
    }
}