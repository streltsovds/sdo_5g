<?php 
class HM_Recruit_Vacancy_Resume_Hh_Ignore_IgnoreTable extends HM_Db_Table
{
	protected $_name     = "recruit_vacancy_hh_resume_ignore";
	protected $_primary  = array("vacancy_hh_resume_ignore_id");


    protected $_referenceMap = array(
    	'VacancyAssign' => array(
            'columns' => 'vacancy_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns' => 'vacancy_id',
            'propertyName' => 'vacancy',
        )
    );

    public function getDefaultOrder()
    {
        return array('recruit_vacancy_hh_resume_ignore.vacancy_hh_resume_ignore_id ASC');
    }
}