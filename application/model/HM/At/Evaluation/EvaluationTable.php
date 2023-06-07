<?php
class HM_At_Evaluation_EvaluationTable extends HM_Db_Table
{
	protected $_name = "at_evaluation_type";
	protected $_primary = "evaluation_type_id";

    protected $_referenceMap = array(
        'EvaluationCriterion' => array(
            'columns'       => 'evaluation_type_id',
            'refTableClass' => 'HM_At_Evaluation_Criterion_CriterionTable',
            'refColumns'    => 'evaluation_type_id',
            'propertyName'  => 'evaluation_criterion'
        ),
        'EvaluationMemo' => array(
            'columns'       => 'evaluation_type_id',
            'refTableClass' => 'HM_At_Evaluation_Memo_MemoTable',
            'refColumns'    => 'evaluation_type_id',
            'propertyName'  => 'evaluation_memo'
        ),
        'SessionEvent' => array(
            'columns'       => 'evaluation_type_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'evaluation_id',
            'propertyName'  => 'sessionEvents'
        ),
        'ProgrammEvent' => array(
            'columns'       => 'evaluation_type_id',
            'refTableClass' => 'HM_Programm_Event_EventTable',
            'refColumns'    => 'item_id', // ВНИМАНИЕ! нужно еще отфильтровать по type
            'propertyName'  => 'programmEvent'
        ),
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ),
        'Vacancy' => array(
            'columns'       => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns'    => 'vacancy_id',
            'propertyName'  => 'vacancy'
        ),
        'Newcomer' => array(
            'columns'       => 'newcomer_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'newcomer_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'reserve_id',
            'propertyName'  => 'reserve'
        ),
    );

}