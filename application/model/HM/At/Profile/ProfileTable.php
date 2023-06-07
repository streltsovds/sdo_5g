<?php
class HM_At_Profile_ProfileTable extends HM_Db_Table
{
    protected $_name = "at_profiles";
    protected $_primary = "profile_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Evaluation' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'evaluations'
        ), 
        'Category' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_At_Category_CategoryTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'category'
        ), 
        'Position' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'positions'
        ), 
        'User' => array( // это только индивидуальное назначение профиля; обычные массовые назначения профиля надо получать через Position 
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ), 
        'CriterionValue' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_CriterionValue_CriterionValueTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'criteriaValues' 
        ), 
        'SessionUser' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'sessionUsers'
        ),
        'Vacancy' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'vacancy'
        ),
        'Newcomer' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'newcomer'
        ),
        'ProfileKpi' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Kpi_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile_kpi'
        ), 
        'Programm' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'item_id', // нужно еще отфильтровать по type
            'propertyName'  => 'programm' 
        ), 
        'Skill' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_Skill_SkillTable',
            'refColumns'    => 'profile_id', 
            'propertyName'  => 'skills' 
        ), 
        'ClassifierLink' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_Classifier_Link_LinkTable',
            'refColumns'    => 'item_id',
            'propertyName'  => 'classifierLinks' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по type!
        ),
        'Reserve' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'reserves'
        ),
    );
}