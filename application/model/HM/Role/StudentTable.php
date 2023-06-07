<?php

class HM_Role_StudentTable extends HM_Db_Table
{
    protected $_name = "Students";
    protected $_primary = "SID";
    protected $_sequence = "S_62_1_STUDENTS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'SubjectUser' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Subject_User_UserTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'subjectUser'
        ),
        'Course' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses'
        ),
        'Subject' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'courses'
        ),
        'Newcomer' => array(
            'columns'       => 'newcomer_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'newcomer_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'reserve_id',
            'propertyName'  => 'reserve'
        ),
        'ProgrammEventUser' => array(
            'columns'       => 'programm_event_user_id',
            'refTableClass' => 'HM_Programm_Event_User_UserTable',
            'refColumns'    => 'programm_event_user_id',
            'propertyName'  => 'programmEventUser'
        ),
    );

    public function getDefaultOrder()
    {
        return array('Students.SID');
    }
}