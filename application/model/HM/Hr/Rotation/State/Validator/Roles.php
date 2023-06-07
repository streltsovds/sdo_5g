<?php

class HM_Hr_Rotation_State_Validator_Roles extends HM_State_Action_Validator
{
    public function validate($params)
    {
        return $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), $params);
    }

}
