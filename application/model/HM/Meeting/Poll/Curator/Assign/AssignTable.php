<?php

class HM_Meeting_Poll_Curator_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "curator_poll_users";
    protected $_primary = array('meeting_id', 'head_mid', 'participant_id');
    //protected $_sequence = "S_58_1_SCHEDULEID";

    //protected $_dependentTables = array("HM_Role_ParticipantTable");

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('curator_poll_users.meeting_id ASC');
    }
}