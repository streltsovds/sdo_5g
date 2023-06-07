<?php

class HM_EstaffSpot_EstaffSpotTable extends HM_Db_Table
{
    protected $_name = "estaff_spot";
    protected $_primary = "spot_id";

    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'User'
        )
    );

    public function getDefaultOrder()
    {
        return array('estaff_spot.spot_id');
    }
}
