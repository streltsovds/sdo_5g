<?php

class HM_Tc_Provider_Contact_ContactTable extends HM_Db_Table
{
    protected $_name = "tc_provider_contacts";
    protected $_primary = "contact_id";
    protected $_sequence = "S_106_1_TC_PROVIDER_CONTACTS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Contact' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns'    => 'provider_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'contacts' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('tc_provider_contacts.contact_id ASC');
    }
}