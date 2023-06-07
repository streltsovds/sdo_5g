<?php

class HM_User_Additional_AdditionalTable extends HM_Db_Table
{
    protected $_name = "user_additional_fields";
    protected $_primary = array('user_id', 'field_id');

    public function getDefaultOrder()
    {
        return array('user_additional_fields.field_id ASC');
    }
}