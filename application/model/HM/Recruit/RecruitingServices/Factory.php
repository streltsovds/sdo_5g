<?php

/**
 * Description of Factory
 *
 * @author tutrinov
 */
class HM_Recruit_RecruitingServices_Factory extends HM_Service_Primitive {
    
    public function getRecruitingService($name, $api = HM_Recruit_RecruitingServices_PlacementBehavior::API_REST) {
        $serviceClassName = "HM_Recruit_RecruitingServices_".ucfirst($api)."_".ucfirst($name);
        if (!class_exists($serviceClassName)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidService('Requested recruiting  service by name '.$name.' and '.$api.' API is not defined!');
        }
        return new $serviceClassName;
    }
    
    public function newExternalCandidate() {
        return $this->getService('RedcruitExternalCandidateEntity');
    }
    
    public function getEntityDecorator($name, HM_Recruit_RecruitingServices_Entity_Collection $collection) {
        $fullName = "HM_Recruit_RecruitingServices_Entity_Decorator_".$name."Decorator";
        if (!class_exists($fullName)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidArgument("Recuired decorator class does not exist");
        }
        return new $fullName($collection);
    }
    
}
