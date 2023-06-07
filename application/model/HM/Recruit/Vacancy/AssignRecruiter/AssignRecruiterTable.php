<?php 
class HM_Recruit_Vacancy_AssignRecruiter_AssignRecruiterTable extends HM_Db_Table
{
	protected $_name     = "recruit_vacancy_recruiters";
	protected $_primary  = array("vacancy_recruiter_id");

    protected $_referenceMap = array(
    	'Recruiter' => array(
            'columns' => 'recruiter_id',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns' => 'recruiter_id',
            'propertyName' => 'recruiters',
        ),
        'Vacancy' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns' => 'vacancy_id',
            'propertyName' => 'vacancies',
        )
    );

    public function getDefaultOrder()
    {
        return array('recruit_vacancy_recruiters.vacancy_recruiter_id ASC');
    }
	
	
}