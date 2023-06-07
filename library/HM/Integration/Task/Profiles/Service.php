<?php

class HM_Integration_Task_Profiles_Service extends HM_Integration_Abstract_Service implements HM_Integration_Interface_Service
{
    public function _fetch($onlyChanged = false)
    {
        $method = $onlyChanged ? 'GetChangesOfEmployees' : 'GetEmployees';
        $itemsEmployees = $this->_client
            ->call($method, 'EmployeeID', false, true);

        $method = $onlyChanged ? 'GetChangesOfStaffUnitsPeriods' : 'GetStaffUnitsPeriods';
        $itemsStaffUnits = $this->_client
            ->call($method, 'IDStaffUnit', false, true); // не копировать в processed! нужно еще для StaffUnits, которые идут далее

        // это не должности, а профили из 1С (крупнонарезаные)
        $itemsPositions = $this->_client
            ->call('GetTitleList');

        $itemsDepartments = $this->_client
            ->call('GetOrganizationalUnits');

        $source = $this->getSource();
        $itemsProfiles = array();

        foreach ($itemsEmployees as $item) {
            if (
                isset($itemsPositions[$item['PositionId']]) &&
                isset($itemsDepartments[$item['DepartmentId']])
            ) {

                $key = implode('_', array($item['PositionId'], $item['DepartmentId'], $source['inn']));
                if (!isset($itemsProfiles[$key])) {
                    $itemsProfiles[$key] = array(
                        'ID' => $key,
                        'PositionShortname' => $itemsPositions[$item['PositionId']]['PositionName'],
                        'PositionName' => implode(' / ', array(
                            $itemsPositions[$item['PositionId']]['PositionName'],
                            $itemsDepartments[$item['DepartmentId']]['Name'],
                            $source['title'],
                        )),
                        'PositionId' => $item['PositionId'],
                        'DepartmentId' => $item['DepartmentId'],
                        'DepartmentPath' => $item['DepartmentId'], // для последующей обработки
                        'CategoryId' => md5(mb_strtolower($itemsPositions[$item['PositionId']]['Category'])),
                        'Category' => $itemsPositions[$item['PositionId']]['Category'],
                        'isDeleted' => $item['isDeleted'], // нужно пробросить isDeleted, иначе никто не удалится
                    );
                }
            }
        }

        foreach ($itemsStaffUnits as $item) {
            if (
                isset($itemsPositions[$item['IDPosition']]) &&
                isset($itemsDepartments[$item['IDParentSubdivision']])
            ) {

                $key = implode('_', array($item['IDPosition'], $item['IDParentSubdivision'], $source['inn']));
                if (!isset($itemsProfiles[$key])) {
                    $itemsProfiles[$key] = array(
                        'ID' => $key,
                        'PositionShortname' => $itemsPositions[$item['IDPosition']]['PositionName'],
                        'PositionName' => implode(' / ', array(
                            $itemsPositions[$item['IDPosition']]['PositionName'],
                            $itemsDepartments[$item['IDParentSubdivision']]['Name'],
                            $source['title'],
                        )),
                        'PositionId' => $item['IDPosition'],
                        'DepartmentId' => $item['IDParentSubdivision'],
                        'DepartmentPath' => $item['IDParentSubdivision'], // для последующей обработки
                        'CategoryId' => md5(mb_strtolower($itemsPositions[$item['IDPosition']]['Category'])),
                        'Category' => $itemsPositions[$item['IDPosition']]['Category'],
                        'isDeleted' => $item['isDeleted'], // нужно пробросить isDeleted, иначе никто не удалится
                    );
                }
            }
        }

        return $itemsProfiles;
    }
}