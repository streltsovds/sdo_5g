<?php
/**
 * Итоговые мероприятия, специфичные для каждого вида программ
 */
class HM_At_Session_Event_Method_Form_Finalize_RecruitModel extends HM_At_Session_Event_Method_Form_FinalizeModel
{
    // Программа подбора пройдена 
    public function onQuestionVariant4()
    {
        list($vacancyCandidate, $programmEventUser) = $this->_getVacancyCandidate();
        if ($vacancyCandidate) {
            $stateClass = HM_Process_Type_Programm_RecruitModel::getStatePrefix() . $programmEventUser->programm_event_id;
            Zend_Registry::get('serviceContainer')->getService('Process')->goToSuccess($vacancyCandidate, $stateClass);
        }               
    }
    
    // Программа подбора не пройдена
    public function onQuestionVariant5()
    {
        $this->_onQuestionVariantFail(HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_RESERVE);
    }
    
    public function onQuestionVariant6()
    {
        $this->_onQuestionVariantFail(HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_BLACKLIST);
    }
    
    public function onQuestionVariant7()
    {
        $this->_onQuestionVariantFail(HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT);
    }
    
    protected function _onQuestionVariantFail($result)
    {
        list($vacancyCandidate, $programmEventUser) = $this->_getVacancyCandidate();

        if ($vacancyCandidate) {
            Zend_Registry::get('serviceContainer')->getService('RecruitVacancyAssign')->setStatus($vacancyCandidate, $result);

            $stateClass = HM_Process_Type_Programm_RecruitModel::getStatePrefix() . $programmEventUser->programm_event_id;
            Zend_Registry::get('serviceContainer')->getService('Process')->goToFail($vacancyCandidate, $stateClass);
        }          
    }
    
    protected function _getVacancyCandidate()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->fetchAllManyToMany('Vacancy', 'Programm', array('programm_event_user_id = ?' => $this->programm_event_user_id)))) {
            $programmEventUser = $collection->current();
            if (count($programmEventUser->vacancy)) {
                $vacancy = $programmEventUser->vacancy->current();
                if (count($collection = Zend_Registry::get('serviceContainer')->getService('RecruitVacancyAssign')->fetchAll(array(
                    'vacancy_id = ?' => $vacancy->vacancy_id,
                    'user_id = ?' => $this->user_id,
                )))) {
                    $vacancyCandidate = $collection->current();
                }
            }
        }
        return array($vacancyCandidate, $programmEventUser);
    }
    
    
    // специальный обработчик для вопросов про назначение уч.курсов
    public function assignSubjects($questionResults)
    {
        if (count($questionResults)) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('QuestQuestion')->fetchAll(array(
                    'question_id IN (?)' => array_keys($questionResults),
                    'type = ?' => HM_Quest_Question_QuestionModel::TYPE_SUBJECTS,
            )))) {
        
                $subjectQuestion = $collection->current(); // может быть только 1
                list($vacancyCandidate, $programmEventUser) = $this->_getVacancyCandidate();

                if ($vacancyCandidate && isset($questionResults[$subjectQuestion->question_id]) && count($subjectIds = unserialize($questionResults[$subjectQuestion->question_id]->variant))) {
                    foreach ($subjectIds as $subjectId) {
                        Zend_Registry::get('serviceContainer')->getService('Subject')->assignUser($subjectId, $vacancyCandidate->user_id);
                    }
                }
            }
        }    
    }

    // специальный обработчик для вопросов про назначение должностей кадрового резрва
    public function assignReservePositions($questionResults)
    {
        if (count($questionResults)) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('QuestQuestion')->fetchAll(array(
                'question_id IN (?)' => array_keys($questionResults),
                'type = ?' => HM_Quest_Question_QuestionModel::TYPE_RESERVE_POSITIONS,
            )))) {

                $reservePositionQuestion = $collection->current(); // может быть только 1
                list($vacancyCandidate, $programmEventUser) = $this->_getVacancyCandidate();

                if ($vacancyCandidate && isset($questionResults[$reservePositionQuestion->question_id]) && count($reservePositionIds = unserialize($questionResults[$reservePositionQuestion->question_id]->variant))) {
                    foreach ($reservePositionIds as $reservePositionId) {
                        Zend_Registry::get('serviceContainer')->getService('HrReservePosition')->assignCandidate($reservePositionId, $vacancyCandidate);
                    }
                }
            }
        }
    }
}