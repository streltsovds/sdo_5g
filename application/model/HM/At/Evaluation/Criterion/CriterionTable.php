<?php
class HM_At_Evaluation_Criterion_CriterionTable extends HM_Db_Table
{
	protected $_name = "at_evaluation_criteria";
	protected $_primary = array('evaluation_type_id', "criterion_id");

    protected $_referenceMap = array(
        'Evaluation' => array(
            'columns'       => 'evaluation_type_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns'    => 'evaluation_type_id',
            'propertyName'  => 'evaluation'
        ),
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criteria'
        ),
        'CriterionKpi' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Kpi_KpiTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criteriaKpi'
        ),
        'CriterionTest' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criteriaTest'
        ),
        'CriterionPersonal' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Personal_PersonalTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criteriaPersonal'
        ),
        'CriterionPair' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Session_Pair_PairTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criteriaPair'
        ),
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ),
    );

}