<?php

class HM_Integration_Task_Positions_Service extends HM_Integration_Abstract_Service implements HM_Integration_Interface_Service
{
    public function _fetch($onlyChanged = false)
    {
        $method = $onlyChanged ? 'GetChangesOfEmployees' : 'GetEmployees';
        $items = $this->_client
            ->setRequireInputParam(true)
            ->call($method, 'EmployeeID');

        $source = $this->getSource();

        foreach ($items as &$item) {
            $item['PositionId'] = implode('_', array(
                $item['PositionId'],
                $item['DepartmentId'],
                $source['inn'],
            ));
        }

        return $items;
    }
}