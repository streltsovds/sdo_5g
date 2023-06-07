<?php

class HM_Integration_Task_Absence_Service extends HM_Integration_Abstract_Service implements HM_Integration_Interface_Service
{
    public function _fetch($onlyChanged = false)
    {
        $vacations = $watches = array();

        $method = $onlyChanged ? 'GetChangesOfAbsence' : 'GetAbsence';
        $vacations = $this->_client
            ->setRequireInputParam($onlyChanged)
            ->call($method, 'ID', array('StartDate', 'EndDate'));

//        $method = $onlyChanged ? 'GetChangesOfAbsenceWatchEmployees' : 'GetAbsenceWatchEmployees';
        $method = 'GetAbsenceWatchEmployees'; // надо всех
        $items = $this->_client
            ->call($method, 'PersonID');

        $method = $onlyChanged ? 'GetChangesOfAbsenceWatch' : 'GetAbsenceWatch';
        $itemsWatch = $this->_client
            ->setRequireInputParam($onlyChanged)
            ->call($method, 'ID', 'Date');

        foreach ($items as $item) {

            $item['Watches'] = array();

            foreach ($itemsWatch as $itemWatch) {
                if ($item['WatchSchedulesID'] == $itemWatch['ID']) {
                    $item['Watches'][] = $itemWatch;
                }
            }

            $prevType = 'Вахта';
            $startDate = '';
            foreach ($item['Watches'] as $itemWatch) {

                if (HM_Integration_Task_Absence_Adapter::isAbsence($itemWatch['TypeTime'])) {

                    if (!HM_Integration_Task_Absence_Adapter::isAbsence($prevType)) {
                        $key = implode('-', array($item['PersonID'], $itemWatch['Date']));
                        if (!isset($result[$key])) {
                            $watches[$key] = array(
                                'ID' => $item['PersonID'],
                                'StartDate' => $itemWatch['Date'],
                                'EndDate' => $itemWatch['Date'],
                                'Type' => HM_Absence_AbsenceModel::TYPE_WATCH,
                                'isDeleted' => $item['isDeleted'], // нужно пробросить isDeleted, иначе никто не удалится
                            );
                            $startDate = $itemWatch['Date'];
                        }
                    } else {
                        $key = implode('-', array($item['PersonID'], $startDate));
                        $watches[$key]['EndDate'] = $itemWatch['Date'];
                    }
                }
                $prevType = $itemWatch['TypeTime'];
            }
        }

        return array_merge($vacations, $watches);
    }
}