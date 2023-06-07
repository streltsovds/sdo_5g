<?php
// это processAbstract
// обычно доступен как $model->getProcess()->processAbstract
class HM_Process_ProcessModel extends HM_Model_Abstract
{
    const PROCESS_ORDER = 1;
    const PROCESS_VACANCY = 2;
    const PROCESS_SESSION = 3;
    const PROCESS_PROGRAMM_RECRUIT = 4;
    const PROCESS_PROGRAMM_ASSESSMENT = 5;
    const PROCESS_PROGRAMM_ADAPTING = 6;
    const PROCESS_PROGRAMM_AGREEMENT_CLAIMANTS = 7;
    const PROCESS_TC_SESSION = 10;
    const PROCESS_TC_SESSION_DEPARTMENT = 11;
    const PROCESS_TC_SESSION_QUARTER = 12;
    const PROCESS_TC_SESSION_QUARTER_DEPARTMENT = 13;
    const PROCESS_PROGRAMM_ROTATION = 14;
    const PROCESS_PROGRAMM_RESERVE = 15;

    static public function factory($data, $default = 'HM_Process_Type_StaticModel')
    {
        if (isset($data['type'])) {
            
            switch($data['type']) {
                case self::PROCESS_PROGRAMM_ASSESSMENT:
                    return parent::factory($data, 'HM_Process_Type_Programm_AssessmentModel');
                    break;
                case self::PROCESS_PROGRAMM_RECRUIT:
                    return parent::factory($data, 'HM_Process_Type_Programm_RecruitModel');
                    break;
                //case self::PROCESS_PROGRAMM_ADAPTING:
                //    return parent::factory($data, 'HM_Process_Type_Programm_AdaptingModel');
                //    break;
                case self::PROCESS_PROGRAMM_AGREEMENT_CLAIMANTS:
                    return parent::factory($data, 'HM_Process_Type_Programm_AgrerementClaimantsModel');
                    break;
//                case self::PROCESS_PROGRAMM_ROTATION:
//                    return parent::factory($data, 'HM_Process_Type_Programm_RotationModel');
//                    break;
            }
        }
        return parent::factory($data, $default);        
    }    

}