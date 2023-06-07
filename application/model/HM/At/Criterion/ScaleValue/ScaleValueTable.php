<?php
class HM_At_Criterion_ScaleValue_ScaleValueTable extends HM_Db_Table
{
    protected $_name = "at_criteria_scale_values";
    protected $_primary = "criterion_value_id";

    protected $_referenceMap = array(
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion'
        ), 
        'CriterionKpi' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Kpi_KpiTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionKpi'
        ), 
        'ScaleValue' => array(
            'columns'       => 'value_id',
            'refTableClass' => 'HM_Scale_Value_ValueTable',
            'refColumns'    => 'value_id',
            'propertyName'  => 'scaleValue'
        ), 
    );
}