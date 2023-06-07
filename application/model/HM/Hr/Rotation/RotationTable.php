<?php 
class HM_Hr_Rotation_RotationTable extends HM_Db_Table
{
    protected $_name     = "hr_rotations";
    protected $_primary  = "rotation_id";
    
    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'user',
        ),
        'Position' => array(
            'columns' => 'position_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns' => 'soid',
            'onDelete' => self::CASCADE,
            'propertyName' => 'position',
        )
    );
}