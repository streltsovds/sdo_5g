<?php

class HM_Role_State_Validator_Roles extends HM_State_Action_Validator
{
    public function validate($params)
    {
        return $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), $params['roles']); 
    }

}
