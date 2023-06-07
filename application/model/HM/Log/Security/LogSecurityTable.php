<?php
class HM_Log_Security_LogSecurityTable extends HM_Db_Table
{
    protected $_name = "log_security";
    protected $_primary = "log_id";

    public function getDefaultOrder()
    {
        return array('log_security.log_id');
    }
}