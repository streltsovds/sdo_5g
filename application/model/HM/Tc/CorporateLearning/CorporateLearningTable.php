<?php

class HM_Tc_CorporateLearning_CorporateLearningTable extends HM_Db_Table
{
    protected $_name = "tc_corporate_learning";
    protected $_primary = "corporate_learning_id";
    protected $_sequence = "";

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
        return array('tc_corporate_learning.corporate_learning_id ASC');
    }
}