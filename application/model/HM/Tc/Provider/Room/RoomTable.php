<?php

class HM_Tc_Provider_Room_RoomTable extends HM_Db_Table
{
    protected $_name = "tc_provider_rooms";
    protected $_primary = "room_id";
    protected $_sequence = "S_106_5_TC_PROVIDER_ROOMS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Provider' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns'    => 'provider_id',
            'propertyName'  => 'providers' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Subject' => array(
            'columns'       => 'room_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'room_id',
            'propertyName'  => 'subjects'
        )
    );

    public function getDefaultOrder()
    {
        return array('tc_provider_rooms.room_id ASC');
    }
}