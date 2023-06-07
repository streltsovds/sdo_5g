<?php
class HM_Tc_Provider_Contact_ContactModel extends HM_Model_Abstract
{
    protected $_primaryName = 'contact_id';

    public function getServiceName()
    {
        return 'TcProviderContact';
    }
}