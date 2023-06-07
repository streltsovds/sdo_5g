<?php 
class HM_Recruit_Vacancy_Assign_AssignTable extends HM_Db_Table
{
	protected $_name     = "recruit_vacancy_candidates";
	protected $_primary  = "vacancy_candidate_id";


    protected $_referenceMap = array(
    	'Vacancy' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns' => 'vacancy_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'vacancies',
        ),
        'Candidate' => array(
            'columns' => 'candidate_id',
            'refTableClass' => 'HM_Recruit_Candidate_CandidateTable',
            'refColumns' => 'candidate_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'candidates',
        ),
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'user',
        ),
        'SessionUser' => array(
            'columns' => 'vacancy_candidate_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns' => 'vacancy_candidate_id',
            'propertyName' => 'sessionUser',
        )
    );

    public function getDefaultOrder()
    {
        return array('recruit_vacancy_candidate.vacancy_id ASC');
    }
	
	
}