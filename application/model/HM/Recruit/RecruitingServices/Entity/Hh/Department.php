<?php
/**
 * Description of Department
 *
 * @author slava
 */
class HM_Recruit_RecruitingServices_Entity_Hh_Department {
    
    protected $value = null;
    protected $name = null;
    
    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
}

?>