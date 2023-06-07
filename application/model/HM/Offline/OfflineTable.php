<?php

class HM_Offline_OfflineTable extends HM_Db_Table
{
    protected $_name = "offlines";
    protected $_primary = "id";
    protected $_sequence = "S_42_1_OFFLINES";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    /*protected $_referenceMap = array(
        'Student' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'CID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'students' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );*/

    public function getDefaultOrder()
    {
        return array('offlines.created DESC');
    }
}