<?php

class HM_Role_ModeratorModel extends HM_Role_Abstract_RoleModel
{
    public function getUser()
    {
        if (isset($this->moderators) && count($this->moderators)) {
            return $this->moderators[0];
        }

        return false;
    }
}