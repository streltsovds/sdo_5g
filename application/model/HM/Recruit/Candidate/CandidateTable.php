<?php 
class HM_Recruit_Candidate_CandidateTable extends HM_Db_Table
{
	protected $_name     = "recruit_candidates";
	protected $_primary  = "candidate_id";
	
	
    protected $_dependentTables = array(
        "HM_Recruit_Vacancy_Assign_AssignTable",
    );
    
    protected $_referenceMap = array(
    	'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'user',
        ),
    	'VacancyAssign' => array(
            'columns' => 'candidate_id',
            'refTableClass' => 'HM_Recruit_Vacancy_Assign_AssignTable',
            'refColumns' => 'candidate_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'vacancies',
        ),
    );
}