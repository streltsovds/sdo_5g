<?php
class HM_At_Criterion_Personal_PersonalTable extends HM_Db_Table
{
    protected $_name = "at_criteria_personal";
    protected $_primary = "criterion_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'EvaluationCriterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Evaluation_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'evaluation_criterion'
        ), 
        'EvaluationResult' => array( // нужно?
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Evaluation_Results_ResultsTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'evaluation_results'
        ), 
        'Quest' => array( // нужно?
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ), 
        'ProfileCriterionValue' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Profile_CriterionValue_CriterionValueTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'profileCriterionValue'
        ),
        'SessionEvent' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'sessionEvents'
        ),
        'MaterialCriteria' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_Material_Criteria_CriteriaTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'materialCriteria' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по criterion_type!
        ),
    );
}