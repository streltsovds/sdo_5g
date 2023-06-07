<?php
class HM_Identity 
{
	static public function getUserId()
	{
        $user = Library::getAuth('default')->getIdentity();
        return $user['user_id'];	
	}

    static public function getRole()
    {
        if ($user = Library::getAuth('default')->getIdentity()) {
            return $user['role'];
        }

        return HM_Role_Abstract_RoleModel::ROLE_GUEST;
    }
}