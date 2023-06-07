<?php
class HM_At_Evaluation_Method_Psycho_SelfModel extends HM_At_Evaluation_Method_PsychoModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        return array(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDummyPosition($user->MID));
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                return true;
            break;
        }
        return false;
    }        
    
    public function isAutoPassing()
    {
        return false;
    }     
    
    public function isOtherRespondentsEventsVisible()
    {
        return true;
    }       
}