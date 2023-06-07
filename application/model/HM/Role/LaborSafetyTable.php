<?php

class HM_Role_LaborSafetyTable extends HM_Db_Table
{
    protected $_name = "labor_safety_specs";
    protected $_primary = "labor_safety_spec_id";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName' => 'user',
        ),
        'Position' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'mid',
            'propertyName'  => 'position',
        ),
        'Responsibility' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Responsibility_ResponsibilityTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'soid', // нужно еще отфильтровать по item_type
        )
    );
}