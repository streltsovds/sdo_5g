<?php
class HM_At_Criterion_Test_TestTable extends HM_Db_Table_NestedSet
{
    protected $_name = "at_criteria_test";
    protected $_left = 'lft';
    protected $_right = 'rgt';
    protected $_level = 'level';    
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
        'CriterionScaleValue' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_ScaleValue_ScaleValueTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'scaleValues'
        ), 
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ), 
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subject'
        ),
        'ReservePosition' => array(
            'columns'       => 'reserve_position_id',
            'refTableClass' => 'HM_HR_Reserve_Position_PositionTable',
            'refColumns'    => 'reserve_position_id',
            'propertyName'  => 'reservePosition'
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