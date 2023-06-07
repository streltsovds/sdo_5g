<?php

class HM_Role_GraduatedModel extends HM_Role_Abstract_RoleModel
{
    
    const STATUS_FAIL    = 0; 
    const STATUS_SUCCESS = 1;
    const STATUS_EXPIRED = 2;
    
    const UNLOOKABLE = 1;
    const LOOKABLE   = 0;

    public function getServiceName()
    {
        return 'Graduated';
    }

    public function getSubject()
    {
        if ($this->subject) {
            return $this->subject[0];
        }
    }

    public function getUser()
    {
        if ($this->user) {
            return $this->user[0];
        }
    }
}