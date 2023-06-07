<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 13:18
 */

class HM_Tc_Session_State_Validator_Roles extends HM_State_Action_Validator
{
    public function validate($params)
    {
        return $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            $params
        );
    }

}