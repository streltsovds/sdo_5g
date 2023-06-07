<?php
class HM_User_Ad_AdService extends HM_Service_Import_Abstract
{
    public function fetchAllByLdap($ldap)
    {
        return $this->getMapper()->fetchAllByLdap($ldap);
    }
}