<?php
class HM_At_Criterion_Indicator_ScaleValue_ScaleValueTable extends HM_Db_Table
{
    protected $_name = "at_criteria_indicator_scale_values";
    protected $_primary = "criterion_indicator_value_id";

    protected $_referenceMap = array(
        'Indicator' => array(
            'columns'       => 'indicator_id',
            'refTableClass' => 'HM_At_Criterion_Indicator_IndicatorTable',
            'refColumns'    => 'indicator_id',
            'propertyName'  => 'indicator'
        ),
        'ScaleValue' => array(
            'columns'       => 'value_id',
            'refTableClass' => 'HM_Scale_Value_ValueTable',
            'refColumns'    => 'value_id',
            'propertyName'  => 'scaleValue'
        ), 
    );
}