<?php
class HM_At_Profile_EducationRequirement_EducationRequirementTable extends HM_Db_Table
{
    protected $_name = "at_profile_education_requirement";
    protected $_primary = array("education_id", "profile_id", "education_type");
    //protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ), 
//         'Criterion' => array(
//             'columns'       => 'criterion_id',
//             'refTableClass' => 'HM_At_Evaluation_Criterion_CriterionTable',
//             'refColumns'    => 'criterion_id',
//             'propertyName'  => 'criterion'
//         ), 
//         'ScaleValue' => array(
//             'columns'       => 'value_id',
//             'refTableClass' => 'HM_Scale_ScaleTable',
//             'refColumns'    => 'value_id',
//             'propertyName'  => 'scaleValue'
//         ), 
    );
}