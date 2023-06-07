<?php
class HM_Process_Type_Programm_RecruitModel extends HM_Process_Type_ProgrammModel implements HM_Process_Type_Programm_Interface
{
    static public function getStatePrefix()
    {
        return 'HM_Recruit_Vacancy_Assign_State_';
    } 
    
    static public function getProgrammType()
    {
        return HM_Programm_ProgrammModel::TYPE_RECRUIT;
    }
}