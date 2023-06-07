<?php
class HM_Programm_Event_EventTable extends HM_Db_Table
{
	protected $_name = "programm_events";
    protected $_primary = "programm_event_id";

    protected $_referenceMap = array(
        'Programm' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'programm'
        ),
        'ProgrammUser' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_User_UserTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'programmUsers'
        ),
        'ProgrammEventUser' => array(
            'columns'       => 'programm_event_id',
            'refTableClass' => 'HM_Programm_Event_User_UserTable',
            'refColumns'    => 'programm_event_id',
            'propertyName'  => 'programmEventUser'
        ),
        'Evaluation' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns'    => 'evaluation_type_id',
            'propertyName'  => 'evaluation'
        ),
        'Agreement' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Agreement_AgreementTable',
            'refColumns'    => 'agreement_id',
            'propertyName'  => 'agreement'
        ),
    );
}