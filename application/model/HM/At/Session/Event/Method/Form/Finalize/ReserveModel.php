<?php
/**
 * Итоговые мероприятия, специфичные для каждого вида программ
 */
class HM_At_Session_Event_Method_Form_Finalize_ReserveModel extends HM_At_Session_Event_Method_Form_FinalizeModel
{
    // Программа КР пройдена успешно
    public function onQuestionVariant8()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->findDependence('Programm', $this->programm_event_user_id))) {
            $programmEventUser = $collection->current();
            if (count($programmEventUser->programm)) {
                $programm = $programmEventUser->programm->current();
                $reserve = Zend_Registry::get('serviceContainer')->getService('HrReserve')->getOne(Zend_Registry::get('serviceContainer')->getService('HrReserve')->find($programm->item_id));
            }
        }        
        
        if ($reserve) {
            //$stateClass = HM_Process_Type_Programm_AdaptingModel::getStatePrefix() . $programmEventUser->programm_event_id;
            Zend_Registry::get('serviceContainer')->getService('Process')->goToSuccess($reserve, false, true); // до упора
        }               
    }
    
    // Программа КР не пройдена
    public function onQuestionVariant9()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->findDependence('Programm', $this->programm_event_user_id))) {
            $programmEventUser = $collection->current();
            if (count($programmEventUser->programm)) {
                $programm = $programmEventUser->programm->current();
                $reserve = Zend_Registry::get('serviceContainer')->getService('HrReserve')->getOne(Zend_Registry::get('serviceContainer')->getService('HrReserve')->find($programm->item_id));
            }
        }

        if ($reserve) {
            $stateClass = HM_Process_Type_Programm_AdaptingModel::getStatePrefix() . $programmEventUser->programm_event_id;
            Zend_Registry::get('serviceContainer')->getService('Process')->goToFail($reserve, $stateClass);
        }
    }
}