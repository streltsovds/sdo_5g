<?php

class HM_Role_ClaimantProcess extends HM_Process_Type_Programm
{
    public function onProcessStart(){}
    
    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_PROGRAMM_AGREEMENT_CLAIMANTS;
    }

    static public function getStatuses()
    {
    }
    
    public function onProcessComplete() 
    {
        $claimant = $this->getModel();
        
        if ($this->getStatus() == HM_Process_Abstract::PROCESS_STATUS_FAILED) {
            Zend_Registry::get('serviceContainer')->getService('Claimant')->reject($claimant->SID);
        } else {
            Zend_Registry::get('serviceContainer')->getService('Claimant')->accept($claimant->SID, '');
        }
    }

}