<?php
class HM_At_Profile_CriterionValue_CriterionValueTable extends HM_Db_Table
{
    protected $_name = "at_profile_criterion_values";
    protected $_primary = "profile_criterion_value_id";
    //protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ), 
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion' 
        ), 
        'CriterionTest' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionTest'
        ), 
        'CriterionPersonal' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Personal_PersonalTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionPersonal'
        ), 
//         'ScaleValue' => array(
//             'columns'       => 'value_id',
//             'refTableClass' => 'HM_Scale_ScaleTable',
//             'refColumns'    => 'value_id',
//             'propertyName'  => 'scaleValue'
//         ), 
    );
}