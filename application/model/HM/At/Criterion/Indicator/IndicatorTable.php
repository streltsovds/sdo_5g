<?php
class HM_At_Criterion_Indicator_IndicatorTable extends HM_Db_Table
{
    protected $_name = "at_criteria_indicators";
    protected $_primary = "indicator_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion'
        ), 
        'IndicatorResult' => array(
            'columns'       => 'indicator_id',
            'refTableClass' => 'HM_At_Evaluation_Results_IndicatorTable',
            'refColumns'    => 'indicator_id',
            'propertyName'  => 'indicatorResults'
        ),
        'CriterionIndicatorScaleValue' => array(
            'columns'       => 'indicator_id',
            'refTableClass' => 'HM_At_Criterion_Indicator_ScaleValue_ScaleValueTable',
            'refColumns'    => 'indicator_id',
            'propertyName'  => 'scaleValues'
        ),
    );    
}