<?php
/*
 */
class HM_Controller_Action_Vacancy_List extends HM_Controller_Action_List
{
    protected $service = 'RecruitVacancy';
    protected $idParamName  = 'vacancy_id';
    protected $idFieldName = 'vacancy_id';
    
    protected $_vacancyId = 0;
    protected $_vacancy = null;

    public function init()
    {
        $this->_vacancyId = $this->_getParam('vacancy_id', 0);
        $this->_vacancy  = $this->getService('RecruitVacancy')->getOne(
            $this->getService('RecruitVacancy')->findDependence(array('Evaluation', 'Profile', 'Session', 'CandidateAssign', 'Position'), $this->_vacancyId)
        );
        
        $this->view->setExtended(
            array(
                'subjectName' => 'RecruitVacancy',
                'subjectId' => $this->_vacancy->vacancy_id,
                'subjectIdParamName' => 'vacancy_id',
                'subjectIdFieldName' => 'vacancy_id',
                'extraSubjectIdParamName' => 'session_id',
                'extraSubjectId' => $this->_vacancy->session_id,
                'subject' => $this->_vacancy
            )
        );
        
        parent::init();
    }
}