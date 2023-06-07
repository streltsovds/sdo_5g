<?php

/**
 * Чистим кэш виджета HM_View_Infoblock_ScheduleDailyBlock если у занятия закончился срок обучения,.
 *
 */
class HM_Crontask_Task_CheckLessonsDatesForCacheCleaning extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'checkLessonsDatesForCacheCleaning';
    }

    public function run()
    {

        $cache = Zend_Registry::get('cache');

        $userIds = $this->getExpiredLessonsUsers();

        foreach ($userIds as $userId) {
            $key = sprintf('widget_HM_View_Infoblock_ScheduleDailyBlock_%s', $userId['MID']);
            // memcache не поддерживает тэги
            $cache->remove($key);
        }

    }

    /*
     * SELECT distinct schid.MID FROM schedule AS sch
     * INNER JOIN scheduleID AS schid ON schid.SHEID = sch.SHEID
     * WHERE sch.`end` < NOW();
     *
     */
    private function getExpiredLessonsUsers()
    {
        $select = Zend_Registry::get('serviceContainer')->getService('Lesson')->getSelect();
        $select->distinct()->from(array('sch' => 'schedule'), array('schid.MID')
        )->joinInner(array('schid' => 'scheduleID'), 'schid.SHEID = sch.SHEID', array()
        )->where('sch.end < NOW()');
        $userIds = $select->query()->fetchAll();

        return $userIds;
    }
}