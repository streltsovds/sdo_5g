<?php

class HM_Role_ParticipantModel extends HM_Role_Abstract_RoleModel
{
    const ROLE_PARTICIPANT   = 0;
    const ROLE_JURYMAN       = 1;
    const ROLE_SPECIAL_GUEST = 2;

    public function getCourse()
    {
        
        if (isset($this->courses) && count($this->courses))
        {
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

    static public function getProjectRoles()
    {
        return array(
            self::ROLE_PARTICIPANT   => _('Участник'),
            self::ROLE_JURYMAN       => _('Член жюри'),
            self::ROLE_SPECIAL_GUEST => _('Почётный гость')
        );
    }

    static public function getProjectRole($roleId)
    {
        $roles = self::getProjectRoles();
        return $roles[$roleId];
    }
    
}