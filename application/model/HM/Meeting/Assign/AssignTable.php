<?php

class HM_Meeting_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "meetingsID";
    protected $_primary = "SSID";
    protected $_sequence = "S_57_1_MEETINGSID";

    //protected $_dependentTables = array("HM_Role_ParticipantTable");

    protected $_referenceMap = array(
        'Meeting' => array(
            'columns'       => 'meeting_id',
            'refTableClass' => 'HM_Meeting_MeetingTable',
            'refColumns'    => 'meeting_id',
            'propertyName'  => 'meetings' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'MarkHistory' => array(
            'columns' => 'SSID',
            'refTableClass' => 'HM_Meeting_Assign_MarkHistory_MarkHistoryTable',
            'refColumns' => 'SSID',
            'propertyName' => 'markHistory')

    );

    public function getDefaultOrder()
    {
        return array('meetingsID.SSID ASC');
    }
}