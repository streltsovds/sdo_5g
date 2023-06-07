<?php
class HM_At_Evaluation_Method_Test_SelfModel extends HM_At_Evaluation_Method_TestModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        if ($this->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
            return array($position);
        } else {
            return array(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDummyPosition($user->MID));
        }
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }
    
    public function isAutoPassing()
    {
        // в ассессменте процесс проходит дальше автоматически
        // в подборе - стопорится до ручного проталкивания менеджером
        return ($this->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) ? true : false;
    }
    
    public function isOtherRespondentsEventsVisible()
    {
        return true;
    }   
}