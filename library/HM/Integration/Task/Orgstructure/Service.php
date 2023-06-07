<?php

class HM_Integration_Task_Orgstructure_Service extends HM_Integration_Abstract_Service implements HM_Integration_Interface_Service
{
    public function _fetch($onlyChanged = false)
    {
        $method = $onlyChanged ? 'GetChangesOfOrganizationalUnits' : 'GetOrganizationalUnits';
        $items = $this->_client->call($method);
        return $items;
    }
}