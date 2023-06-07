<?php
class HM_Crontask_Task_ReserveReport extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'sendReserveReport';
    }

    public function run()
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $process = $serviceContainer->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_PROGRAMM_RESERVE);
        $reportDuration     =
        $evaluationDuration = 0;

        foreach ($process->states as $state) {
            foreach ($state as $item) {
                if ($item['class'] == 'HM_Hr_Reserve_State_Publish') $reportDuration     = $item['day_end'] - $item['day_begin'];
                if ($item['class'] == 'HM_Hr_Reserve_State_Result' ) $evaluationDuration = $item['day_end'] - $item['day_begin'];
            }
        }

        $now = strtotime(date('Y-m-d'));
        $reserves = $serviceContainer->getService('HrReserve')->fetchAll(
            array(
                'status IN (?)' => array(HM_Hr_Reserve_ReserveModel::STATE_ACTUAL, HM_Hr_Reserve_ReserveModel::STATE_PENDING),
                'state_id = ?' => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_PUBLISH,
                'report_notification_sent = ?' => HM_Hr_Reserve_ReserveModel::REPORT_NOTIFICATION_NOT_SENT_YET,
            )
        );

        foreach ($reserves as $reserve) {
            $endDate = new Zend_Date($reserve->end_date);
            $reportPeriod = strtotime(HM_Date::getRelativeDate($endDate, -1*((int)$reportDuration + (int)$evaluationDuration)));
            $datetime2 = new DateTime($reserve->end_date);
            $datetime1 = new DateTime(date("Y-m-d", $reportPeriod));
            $interval = $datetime1->diff($datetime2);
            if ((int)$interval->format('a') == 0 && $reserve->state == 3) {
                $serviceContainer->getService('Process')->goToNextState($reserve);
                $user = $serviceContainer->getService('User')->findOne($reserve->user_id);

                $position   = $serviceContainer->getService('Orgstructure')->findOne($reserve->position_id);
                $department = $serviceContainer->getService('Orgstructure')->findOne($position->owner_soid);

                $href = Zend_Registry::get('view')->serverUrl('hr/rotation/report/index/reserve_id/' . $reserve->reserve_id);
                $url = '<a href="' . $href . '">' . $href . '</a>';

                $messenger = $serviceContainer->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_RESERVE_REPORT,
                    array(
                        'fio' => $user->getName($reserve->reserve_id),
                        'begin_date' => date('d.m.Y', strtotime($reserve->begin_date)),
                        'end_date' => date('d.m.Y', strtotime($reserve->end_date)),
                        'reserve_position' => $position->name,
                        'reserve_department' => $department->name,
                        'report_date' => date("d.m.Y", $reportPeriod),
                        'url' => $url
                    ),
                    'reserve',
                    $reserve->reserve_id
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);


                $serviceContainer->getService('HrReserve')->update(
                    array(
                        'reserve_id' => $reserve->reserve_id,
                        'report_notification_sent' => HM_Hr_Reserve_ReserveModel::REPORT_NOTIFICATION_SENT
                    )
                );
            }
        }

    }
}
