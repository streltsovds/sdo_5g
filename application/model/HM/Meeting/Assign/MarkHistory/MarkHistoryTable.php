<?php
class HM_Meeting_Assign_MarkHistory_MarkHistoryTable extends HM_Db_Table
{
     protected $_name = "meetings_marks_history";
     protected $_primary = array('MID','SSID','mark','updated');
     protected $_referenceMap = array(
        'MeetingAssign' => array(
            'columns'       => 'SSID',
            'refTableClass' => 'HM_Meeting_Assign_AssignTable',
            'refColumns'    => 'SSID',
            'propertyName'  => 'meetingAssigns'
        ),
        'Moderator' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'moderators'
        )
    );
}