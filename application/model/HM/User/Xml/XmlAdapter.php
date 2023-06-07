<?php
class HM_User_Xml_XmlAdapter extends HM_Adapter_Xml_Abstract
{

    // сопоставление названий полей модели и элементов в хмл, в перенте дефолтный набор
    public function getMappingArray()
    {
        return parent::getMappingArray();
        /* пример на основе хмл от алматв
         * return array(
            'mid_external' => 'mid_external',
            'sn' => 'LastName',
            'givenName' => 'FirstName',
            'middleName' => 'Patronymic',
        );*/
    }

    public static function getUserFields()
    {
        return array(
            'LastName',
            'FirstName',
            'Patronymic',
            'Login',
            'EMail',
            'Password',
        );
    }

    public static function getOrgName()
    {

    }

    public function getCallbacks()
    {

    }


}