<?php
class HM_Process_Type_Programm_AgrerementClaimantsModel extends HM_Process_Type_ProgrammModel implements HM_Process_Type_Programm_Interface
{
    static public function getStatePrefix()
    {
        return 'HM_Role_Claimant_State_';
    } 
    
    static public function getProgrammType()
    {
        return HM_Programm_ProgrammModel::TYPE_AGREEMENT_CLAIMANTS;
    }
}