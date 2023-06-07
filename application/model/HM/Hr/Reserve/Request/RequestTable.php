<?php 
class HM_Hr_Reserve_Request_RequestTable extends HM_Db_Table
{
    protected $_name     = "hr_reserve_requests";
    protected $_primary  = "reserve_request_id";
    
    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'user',
        ),
        'ReservePosition' => array(
            'columns'       => 'position_id',
            'refTableClass' => 'HM_Hr_Reserve_Position_PositionTable',
            'refColumns'    => 'reserve_position_id',
            'onDelete' => self::CASCADE,
            'propertyName'  => 'reservePosition'
        ),
        'Reserve' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'reserve_id',
            'onDelete' => self::CASCADE,
            'propertyName'  => 'reserve'
        ),
    );
}