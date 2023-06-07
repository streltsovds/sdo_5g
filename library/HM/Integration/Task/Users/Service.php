<?php

class HM_Integration_Task_Users_Service extends HM_Integration_Abstract_Service implements HM_Integration_Interface_Service
{
    public function _fetch($onlyChanged = false)
    {
        $method = $onlyChanged ? 'GetChangesOfPhysicalPersons' : 'GetPhysicalPersons';
        $items = $this->_client
            ->setRequireInputParam($onlyChanged)
            ->call($method);

        $method = $onlyChanged ? 'GetChangesOfPhysicalPersonsNames' : 'GetPhysicalPersonsNames';
        $itemsExtended = $this->_client
            ->setRequireInputParam(true)
            ->call($method);

        foreach ($itemsExtended as $itemExtended) {
            if (isset($items[$itemExtended['ID']])) {
                $items[$itemExtended['ID']] = array_merge(
                    $items[$itemExtended['ID']],
                    $itemExtended
                );
            } else {
                $id = $itemExtended['ID'];
                $itemExtended['ID'] = $id;
                $items[$id] = $itemExtended;
            }
        }

        return $items;
    }

}