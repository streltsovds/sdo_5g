<?php
class HM_Extension_Remover_WebinarRemover extends HM_Extension_Remover_Abstract
{
    public function registerEventsCallbacks()
    {
        parent::registerEventsCallbacks();
        $this->getService('EventDispatcher')->connect(
                HM_Extension_ExtensionService::EVENT_FILTER_LESSON_TYPES,
                array($this, 'callFilterLessonTypes')
        );
    }

    public function callFilterLessonTypes($event, $lessonTypes)
    {
        unset($lessonTypes[HM_Event_EventModel::TYPE_WEBINAR]);
        return $lessonTypes;
    }
}