<?php

class HM_Role_ParticipantTable extends HM_Db_Table
{
    protected $_name = "Participants";
    protected $_primary = "participant_id";
    protected $_sequence = "S_62_1_PARTICIPANTS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'participant_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'Course' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses'
        ),
        'Project' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Project_ProjectTable',
            'refColumns'    => 'projid',
            'propertyName'  => 'courses'
        )
        
    );

    public function getDefaultOrder()
    {
        return array('Participants.participant_id');
    }
}