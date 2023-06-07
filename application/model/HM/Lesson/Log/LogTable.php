<?php

class HM_Lesson_Log_LogTable extends HM_Db_Table
{
    protected $_name = "schedule_log";
    protected $_primary = "id";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Lesson' => array(
            'columns'       => 'lesson_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID',
            'propertyName'  => 'lessons' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'Student' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'students'
        ),
    );

    public function getDefaultOrder()
    {
        return array('schedule_log.id ASC');
    }
}