<?php
class HM_At_Criterion_Kpi_KpiTable extends HM_Db_Table
{
    protected $_name = "at_criteria_kpi";
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
    );    
}