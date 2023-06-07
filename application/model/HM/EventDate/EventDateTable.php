<?php

class HM_EventDate_EventDateTable extends HM_Db_Table
{
    protected $_name = "event_date";
    protected $_primary = "id";

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array();
    }
}