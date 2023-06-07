<?php

class HM_Integration_Task_StaffUnits_Service extends HM_Integration_Abstract_Service implements HM_Integration_Interface_Service
{
    public function _fetch($onlyChanged = false)
    {
        $method = $onlyChanged ? 'GetChangesOfStaffUnitsPeriods' : 'GetStaffUnitsPeriods';
        $items = $this->_client
            ->setRequireInputParam($onlyChanged)
            ->call($method, 'IDStaffUnit');

        $source = $this->getSource();

        foreach ($items as &$item) {
            $item['NumberOfStaffUnitsText'] = $item['NumberOfStaffUnits'];
            $item['IDPosition'] = implode('_', array(
                $item['IDPosition'],
                $item['IDParentSubdivision'],
                $source['inn'],
            ));
        }

        return $items;
    }
}