<?php

class HM_Meeting_MeetingTable extends HM_Db_Table
{
    protected $_name = "meetings";
    protected $_primary = "meeting_id";
    protected $_sequence = "S_57_1_MEETINGS";

    protected $_dependentTables = array("HM_Meeting_Assign_AssignTable", 'HM_User_UserTable', 'HM_Event_EventTable');

    protected $_referenceMap = array(        
        'Project' => array(
            'columns'       => 'project_id',
            'refTableClass' => 'HM_Project_ProjectTable',
            'refColumns'    => 'projid',
            'propertyName'  => 'project'
        ),
        'Assign' => array(
            'columns'       => 'meeting_id',
            'refTableClass' => 'HM_Meeting_Assign_AssignTable',
            'refColumns'    => 'meeting_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'assigns'
        ),
        'Moderator' => array(
            'columns'       => 'moderator',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'moderator'
        ),
        'Statistic' => array(
            'columns'       => 'meeting_id',
            'refTableClass' => 'HM_Module_Test_Statistic_StatisticTable',
            'refColumns'    => 'sheid',
            'propertyName'  => 'statistics'
        ),
        'Event' => array(
            'columns'       => 'typeID',
            'refTableClass' => 'HM_Event_EventTable',
            'refColumns'    => 'event_id',
            'propertyName'  => 'event'
        ),
        'Section' => array(
            'columns'       => 'section_id',
            'refTableClass' => 'HM_Section_SectionTable',
            'refColumns'    => 'section_id',
            'propertyName'  => 'section'
        ),
    );

    public function getDefaultOrder()
    {
        /**
         * По умолчанию сортировка по порядку.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        return array('meetings.order ASC','meetings.title ASC');
    }
}