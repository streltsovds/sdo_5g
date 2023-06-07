<?php
class HM_At_Session_Event_Lesson_LessonTable extends HM_Db_Table
{
    protected $_name    = "at_session_event_lessons";
    protected $_primary = "lesson_id";

    protected $_referenceMap = array(
        'SessionEvent' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'event'
        ),
        'Lesson' => array(
            'columns'       => 'lesson_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID',
            'propertyName'  => 'lessons'
        ),
    );

}