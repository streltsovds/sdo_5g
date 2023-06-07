<?php
class HM_At_Category_CategoryTable extends HM_Db_Table
{
    protected $_name = "at_categories";
    protected $_primary = "category_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Evaluation' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'evaluations'
        ), 
        'Profile' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'profiles'
        ), 
        'Position' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'positions'
        ), 
        'Criterion' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'criteria'
        ), 
        'Programm' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'item_id', // нужно еще отфильтровать по type
            'propertyName'  => 'programm' 
        ),            
//         'Vacancy' => array(
//             'columns'       => 'category_id',
//             'refTableClass' => 'HM_Vacancy_VacancyTable',
//             'refColumns'    => 'category_id',
//             'propertyName'  => 'vacancy'
//         ),
    );
}