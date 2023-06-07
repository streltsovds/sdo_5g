<?php
class HM_At_Evaluation_Results_IndicatorTable extends HM_Db_Table
{
    protected $_name = "at_evaluation_results_indicators";
    protected $_primary = "indicator_result_id";

    protected $_referenceMap = array(
        'SessionEvent' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'sessionEvents'
        ),
        'SessionUser' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'sessionUser'
        ),
        'Indicator' => array(
            'columns'       => 'indicator_id',
            'refTableClass' => 'HM_At_Criterion_Indicator_IndicatorTable',
            'refColumns'    => 'indicator_id',
            'propertyName'  => 'indicator'
        ),
    );
}