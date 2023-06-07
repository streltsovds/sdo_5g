<?php

class HM_Recruit_Vacancy_VacancyTable extends HM_Db_Table {

    protected $_name = "recruit_vacancies";
    protected $_primary = "vacancy_id";
    protected $_dependentTables = array(
        "HM_Recruit_Vacancy_Assign_AssignTable",
    );

    protected $_referenceMap = array(
        'CandidateAssign' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_Assign_AssignTable',
            'refColumns' => 'vacancy_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'candidates',
        ),
        'Profile' => array(
            'columns' => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns' => 'profile_id',
            'propertyName' => 'profile' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Session' => array(
            'columns' => 'session_id',
            'refTableClass' => 'HM_At_Session_SessionTable',
            'refColumns' => 'session_id',
            'propertyName' => 'session' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Evaluation' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns' => 'vacancy_id',
            'propertyName' => 'evaluations'
        ),
        'Position' => array(
            'columns' => 'position_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns' => 'soid',
            'propertyName' => 'position'
        ),
        'RecruiterAssign' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_AssignRecruiter_AssignRecruiterTable',
            'refColumns' => 'vacancy_id',
            'propertyName' => 'recruiterAssign'
        ),
        'VacancyResumeHhIgnore' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_Resume_Hh_Ignore_IgnoreTable',
            'refColumns' => 'vacancy_id',
            'propertyName' => 'resume_hh_ignore'
        ),
        'Programm' => array(
            'columns'       => 'vacancy_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'item_id', // нужно еще отфильтровать по type
            'propertyName'  => 'programm' 
        ),
        'DataFields' => array(
            'columns'       => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_DataFields_DataFieldsTable',
            'refColumns'    => 'item_id', // нужно еще отфильтровать по type
            'propertyName'  => 'dataFields'
        ),
    );

}
