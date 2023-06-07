<?php
class HM_Process_Type_Programm_RotationModel extends HM_Process_Type_ProgrammModel implements HM_Process_Type_Programm_Interface
{
    static public function getStatePrefix()
    {
        return 'HM_Hr_Rotation_State_';
    } 
    
    static public function getProgrammType()
    {
        return HM_Programm_ProgrammModel::TYPE_ROTATION;
    }
}