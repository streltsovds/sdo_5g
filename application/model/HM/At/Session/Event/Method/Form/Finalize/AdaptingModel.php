<?php
/**
 * Итоговые мероприятия, специфичные для каждого вида программ
 */
class HM_At_Session_Event_Method_Form_Finalize_AdaptingModel extends HM_At_Session_Event_Method_Form_FinalizeModel
{
    // Программа адаптации пройдена полностью и успешно
    public function onQuestionVariant1()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->findDependence('Programm', $this->programm_event_user_id))) {
            $programmEventUser = $collection->current();
            if (count($programmEventUser->programm)) {
                $programm = $programmEventUser->programm->current();
                $newcomer = Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->getOne(Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->find($programm->item_id));
            }
        }        
        
        if ($newcomer) {
            $stateClass = HM_Process_Type_Programm_AdaptingModel::getStatePrefix() . $programmEventUser->programm_event_id;
            Zend_Registry::get('serviceContainer')->getService('Process')->goToSuccess($newcomer, $stateClass);
        }               
    }
    
    // Программа адаптации не пройдена
    public function onQuestionVariant2()
    {
        $this->_onQuestionVariantFail();
    }
    
    // Продлить
    public function onQuestionVariant3($attempt = false)
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->findDependence('Programm', $this->programm_event_user_id))) {
            $programmEventUser = $collection->current();
            if (count($programmEventUser->programm)) {
                $programm = $programmEventUser->programm->current();
                $newcomer = Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->getOne(Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->find($programm->item_id));
            }
        }

        if ($newcomer && $attempt) {
            $results = $attempt->questionResults->asArrayOfObjects();
            foreach ($results as $result) {
                if (($result->question_id == 2) && !empty($result->free_variant)) { // см. db_dump2
                    try {
                        $date = new HM_Date($result->free_variant);
                        $newcomer->extended_to = $date->get('Y-MM-dd');
                        Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->update($newcomer->getValues());
                    } catch (Exception $e) {
                    }
                    break;
                }
            }
        }
    }

    protected function _onQuestionVariantFail()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->fetchAllManyToMany('Newcomer', 'Programm', array('programm_event_user_id = ?' => $this->programm_event_user_id)))) {
            $programmEventUser = $collection->current();
            if (count($programmEventUser->newcomer)) {
                $newcomer = $programmEventUser->newcomer->current();
            }
        }
        
        if ($newcomer) {
            $stateClass = HM_Process_Type_Programm_AdaptingModel::getStatePrefix() . $programmEventUser->programm_event_id;
            Zend_Registry::get('serviceContainer')->getService('Process')->goToFail($newcomer, $stateClass);
            Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->update(array(
                'newcomer_id' => $newcomer->newcomer_id,
                'result' => HM_Recruit_Newcomer_NewcomerModel::RESULT_FAIL_MANAGER
            ));
        }
    }
}