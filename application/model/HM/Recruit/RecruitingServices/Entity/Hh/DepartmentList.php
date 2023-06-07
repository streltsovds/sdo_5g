<?php
/**
 * Description of DepartmentList
 *
 * @author slava
 */
class HM_Recruit_RecruitingServices_Entity_Hh_DepartmentList extends HM_Recruit_RecruitingServices_Entity_Collection
{
    
    const EVENT_DEPARMENT_ADD_PRE = "hhDepartmentAdd.pre";
    const EVENT_DEPARMENT_ADD_POST = "hhDepartmentAdd.post";
    
    public function add($object, $key = null) {
        $params = array(
            'department' => $object
        );
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_DEPARMENT_ADD_PRE, $params));
        parent::add($object, $key);
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_DEPARMENT_ADD_POST, $params));
    }
    
    public function out() {
        return $this;
    }
    
}

?>