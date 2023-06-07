<?php

class HM_StaffUnit_StaffUnitTable extends HM_Db_Table
{
    protected $_name = "staff_units";
    protected $_primary = "staff_unit_id";

    protected $_referenceMap = array(
        'Position' => array(
            'columns'       => 'staff_unit_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'staff_unit_id',
            'propertyName'  => 'positions'
        ),
    );
}