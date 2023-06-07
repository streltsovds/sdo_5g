<?php
class HM_At_Criterion_CriterionTable extends HM_Db_Table
{
    protected $_name = "at_criteria";
    protected $_left = 'lft';
    protected $_right = 'rgt';
    protected $_level = 'level';
    protected $_primary = "criterion_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Category' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_At_Category_CategoryTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'category'
        ), 
        'EvaluationCriterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Evaluation_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'evaluation_criterion'
        ), 
        'EvaluationResult' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Evaluation_Results_ResultsTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'evaluation_results'
        ), 
        'CriterionCluster' => array(
            'columns'       => 'cluster_id',
            'refTableClass' => 'HM_At_Criterion_Cluster_ClusterTable',
            'refColumns'    => 'cluster_id',
            'propertyName'  => 'cluster'
        ), 
        'CriterionIndicator' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Indicator_IndicatorTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'indicators'
        ), 
        'CriterionScaleValue' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_ScaleValue_ScaleValueTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'scaleValues'
        ), 
        'ProfileCriterionValue' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Profile_CriterionValue_CriterionValueTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'profileCriterionValue'
        ),
        'MaterialCriteria' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_Material_Criteria_CriteriaTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'materialCriteria' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по criterion_type!
        ),
        'SubjectCriteria' => array(
            'columns' => 'criterion_id',
            'refTableClass' => 'HM_Subject_Criteria_CriteriaTable',
            'refColumns' => 'criterion_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'subjectCriteria'
        ),
    );
}