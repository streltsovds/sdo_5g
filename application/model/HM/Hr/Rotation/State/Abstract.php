<?php
class HM_Hr_Rotation_State_Abstract extends HM_State_Abstract
{
    // DEPRECATED! даты хранятся только в state_of_process_data
//    public function getProcessStepsData()
//    {
//        static $processStepsDataCache;
//
//        if (!isset($processStepsDataCache)) {
//            $processStepsDataCache = array();
//            $collection = $this->getService('ProcessStep')->fetchAll(array(
//                'process_type = ?' => $this->_process->getType(),
//                'item_id = ?' => $this->_params['rotation_id'],
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

    public function getDescription()
    {
        return '';
    }

    public function isNextStateAvailable()
    {
        return true;
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
        return _("При создании программы КР возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Программа КР отменена');
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