<?php
class HM_At_Evaluation_Method_Form_SelfModel extends HM_At_Evaluation_Method_FormModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        return array($position);
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }        
    
    public function isAutoPassing()
    {
        return true;
    }     
    
    public function isOtherRespondentsEventsVisible()
    {
        return true;
    }       
}