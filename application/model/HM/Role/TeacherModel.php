<?php

class HM_Role_TeacherModel extends HM_Role_Abstract_RoleModel
{
    public function getCourse()
    {
        if (isset($this->courses) && count($this->courses)) {
            return $this->courses[0];
        }
        return false;
    }

    public function getUser()
    {
        if (isset($this->users) && count($this->users)) {
            return $this->users[0];
        }

        return false;
    }
}