<?php
class HM_At_Session_Pair_Result_ResultTable extends HM_Db_Table
{
    protected $_name    = "at_session_pair_results";
    //protected $_primary = "";

    protected $_referenceMap = array(
        'SessionPair' => array(
            'columns'       => 'session_pair_id',
            'refTableClass' => 'HM_At_Session_Pair_PairTable',
            'refColumns'    => 'session_pair_id',
            'propertyName'  => 'pair'
        ),
   		'SessionEvent' => array(
			'columns'       => 'session_event_id',
			'refTableClass' => 'HM_At_Session_Event_EventTable',
			'refColumns'    => 'session_event_id',
			'propertyName'  => 'event'
   		),
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion'
        ),
        'UserSelected' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'selected'
        ),
    	'Pair' => array(
    		'columns'       => 'session_pair_id',
    		'refTableClass' => 'HM_At_Session_Pair_PairTable',
    		'refColumns'    => 'session_pair_id',
    		'propertyName'  => 'results'
    	),    		
    );
}