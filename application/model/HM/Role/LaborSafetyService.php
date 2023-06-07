<?php
class HM_Role_LaborSafetyService extends HM_Service_Abstract
{
    public function getSubjects()
    {
        $subjects   = $this->getService('Subject')->fetchAll(array('is_labor_safety = ?' => 1), 'name');
        return $subjects;
    }
}