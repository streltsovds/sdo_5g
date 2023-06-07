<?php

class HM_Mail_Hold_HoldTable extends HM_Db_Table
{
    protected $_name = "hold_mail";
    protected $_primary = "hold_mail_id";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'receiver_MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'receiver_MID',
            'propertyName'  => 'user'
        ),
    );
}