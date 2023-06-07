<?php
class HM_Cycle_CycleTable extends HM_Db_Table
{
    protected $_name = "cycles";
    protected $_primary = "cycle_id";
    protected $_sequence = "S_100_1_CYCLES";

    protected $_referenceMap = array(
        'Subject' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Chain_Item_ItemTable',
            'refColumns'    => 'cycle_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects'
        ),
        'Session' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_At_Session_SessionTable',
            'refColumns'    => 'cycle_id',
            'propertyName'  => 'session'
        ),
        'Newcomer' => array(
            'columns'       => 'newcomer_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'newcomer_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'cycle_id',
            'propertyName'  => 'reserve'
        ),
        'TcSession' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Tc_Session_SessionTable',
            'refColumns'    => 'cycle_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'tcsession'
        ),
        'TcSessionQuarter' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Tc_SessionQuarter_SessionQuarterTable',
            'refColumns'    => 'cycle_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'tcsessionQuarter'
        ),
    );

    public function getDefaultOrder()
    {
        return array('cycle.begin_date DESC');
    }
}