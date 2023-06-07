<?php 
class HM_Hr_Reserve_Position_PositionTable extends HM_Db_Table
{
    protected $_name     = "hr_reserve_positions";
    protected $_primary  = "reserve_position_id";
    
    protected $_referenceMap = array(
        'ReserveRequest' => array(
            'columns'       => 'reserve_position_id',
            'refTableClass' => 'HM_Hr_Reserve_Request_RequestTable',
            'refColumns'    => 'position_id',
            'propertyName'  => 'reserveRequest'
        ),
        'Reserve' => array(
            'columns'       => 'reserve_position_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'reserve_position_id',
            'onDelete' => self::CASCADE,
            'propertyName'  => 'reserve'
        ),
        'CriterionTest' => array(
            'columns'       => 'reserve_position_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'reserve_position_id',
            'propertyName'  => 'criterionTest'
        ),
    );
}