<?php
class HM_At_Session_Pair_Rating_RatingTable extends HM_Db_Table
{
    protected $_name    = "at_session_pair_ratings";
    //protected $_primary = "";

    protected $_referenceMap = array(
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion'
        ),
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
    	'Session' => array(
    		'columns'       => 'session_id',
    		'refTableClass' => 'HM_At_Session_SessionTable',
    		'refColumns'    => 'session_id',
    		'propertyName'  => 'session'
    	),    		
    );
}