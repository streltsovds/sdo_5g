<?php

/**
 * Description of Department
 *
 * @author slava
 */
class HM_Recruit_RecruitingServices_Entity_Validator_Hh_Department {
    
    
    public function hhVacancyDepartmentClassChecker() {
        return function($ev) {
            $params = $ev->getParameters();
            $deparmentInstance = $params['department'];
            if (!($deparmentInstance instanceof HM_Recruit_RecruitingServices_Entity_Hh_Department)) {
                throw new HM_Recruit_RecruitingServices_Exception_InvalidArgument("Department object must be instance of HM_Recruit_RecruitingServices_Entity_Hh_Department class");
            }
        };
    }
    
}

?>