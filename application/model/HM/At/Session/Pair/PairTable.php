<?php
class HM_At_Session_Pair_PairTable extends HM_Db_Table
{
    protected $_name    = "at_session_pairs";
    protected $_primary = "session_pair_id";

    protected $_referenceMap = array(
        'SessionEvent' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'event'
        ),
        'UserFirst' => array(
            'columns'       => 'first_user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'userFirst'
        ),
        'UserSecond' => array(
            'columns'       => 'second_user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'userSelected'
        ),
        'PairResult' => array(
            'columns'       => 'session_pair_id',
            'refTableClass' => 'HM_At_Session_Pair_Result_ResultTable',
            'refColumns'    => 'session_pair_id',
            'propertyName'  => 'pairResults'
        ),
    );
}