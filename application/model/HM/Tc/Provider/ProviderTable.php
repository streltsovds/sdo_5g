<?php

class HM_Tc_Provider_ProviderTable extends HM_Db_Table
{
    protected $_name = "tc_providers";
    protected $_primary = "provider_id";
    protected $_sequence = "S_106_1_TC_PROVIDERS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Contact' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_Contact_ContactTable',
            'refColumns'    => 'provider_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'contacts' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Teacher' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_Teacher_TeacherTable',
            'refColumns'    => 'provider_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'teachers' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Room' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_Room_RoomTable',
            'refColumns'    => 'provider_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'rooms' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Scmanager' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_Scmanager_ScmanagerTable',
            'refColumns'    => 'provider_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'scmanagers' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Subject' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'provider_id',
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'ScSubject' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_Subject_SubjectTable',
            'refColumns'    => 'provider_id',
            'propertyName'  => 'scSubjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'TcApplication' => array(
            'columns' => 'provider_id',
            'refTableClass' => 'HM_Tc_Application_ApplicationTable',
            'refColumns' => 'provider_id',
            'propertyName' => 'tcApplications'
        ),

    );

    public function getDefaultOrder()
    {
        return array('tc_providers.name ASC');
    }
}