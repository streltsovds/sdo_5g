<?php

class HM_Recruit_Newcomer_State_Complete extends HM_State_Complete_Abstract
{
    public function isNextStateAvailable() {}
    
    public function getDescription()
    {
        return _('На этом этапе доступ к оценочным мероприятиям закрыт для пользователей; в системе формируются отчеты по итогам оценочной сессии.');
    }

    // DEPRECATED! даты хранятся только в state_of_process_data
//    public function getProcessStepsData()
//    {
//        static $processStepsDataCache;
//
//        if (!isset($processStepsDataCache)) {
//            $processStepsDataCache = array();
//            $collection = $this->getService('ProcessStep')->fetchAll(array(
//                'process_type = ?' => $this->_process->getType(),
//                'item_id = ?' => $this->_params['newcomer_id'],
//            ));
//            foreach ($collection as $item) {
//                $processStepsDataCache[$item->step] = $item;
//            }
//        }
//
//        return $processStepsDataCache;
//    }

    public function getActions()
    {
        return array();
    }

    public function onNextState()
    {
        return true;
    }

    public function getForms()
    {
//        return $this->getDescriptionForm();
        return array($this->getFilesForm(), $this->getDescriptionForm());
    }

    public function initMessage() {}

    public function onNextMessage() {}

    public function onErrorMessage()
    {
        return _("При создании программы адаптации возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Программа адапатции отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function getCurrentStateMessage()
    {
        return _('В процессе');
    }
}
