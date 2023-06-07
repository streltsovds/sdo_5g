<?php
class HM_Crontask_Task_RotationReport extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'sendRotationReport';
    }

    public function run()
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $process = $serviceContainer->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_PROGRAMM_ROTATION);
        $reportDuration     =
        $evaluationDuration = 0;

        foreach ($process->states as $state) {
            foreach ($state as $item) {
                if ($item['class'] == 'HM_Hr_Rotation_State_Publish') $reportDuration     = $item['day_end'] - $item['day_begin'];
                if ($item['class'] == 'HM_Hr_Rotation_State_Result' ) $evaluationDuration = $item['day_end'] - $item['day_begin'];
            }
        }
        $reportDuration++;

        $now = strtotime(date('Y-m-d')) + 4*60*60;
        $rotations = $serviceContainer->getService('HrRotation')->fetchAll(
            array(
                'status IN (?)' => array(HM_Hr_Rotation_RotationModel::STATE_ACTUAL, HM_Hr_Rotation_RotationModel::STATE_PENDING),
                'state_id = ?' => HM_Hr_Rotation_RotationModel::PROCESS_STATE_PLAN,
                'report_notification_sent = ?' => HM_Hr_Rotation_RotationModel::REPORT_NOTIFICATION_NOT_SENT_YET,
            )
        );

        foreach ($rotations as $rotation) {
            $timestamp = strtotime($rotation->end_date) + 4*60*60;
            $endDate = new Zend_Date($timestamp, Zend_Date::TIMESTAMP);
            $reportPeriod   = strtotime(HM_Date::getRelativeDate($endDate, -1*((int)$reportDuration + (int)$evaluationDuration)));
            $reportDeadline = strtotime(HM_Date::getRelativeDate(new Zend_Date(date('Y-m-d', $reportPeriod)), (int)$reportDuration));
            $datetime2 = new DateTime(date("Y-m-d H:i:s", $now));
            $datetime1 = new DateTime(date("Y-m-d H:i:s", $reportPeriod));
            $interval = $datetime1->diff($datetime2);
            if ((int)$interval->format('%a') == 0 && $rotation->state_id == HM_Hr_Rotation_RotationModel::PROCESS_STATE_PLAN) {
                $serviceContainer->getService('Process')->goToNextState($rotation);
                $serviceContainer->getService('HrRotation')->update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'status' => HM_Hr_Rotation_RotationModel::STATE_ACTUAL,
                        'state_id' => HM_Hr_Rotation_RotationModel::PROCESS_STATE_PUBLISH,
                        'state_change_date' => date('Y-m-d')
                    )
                );
                $user = $serviceContainer->getService('User')->findOne($rotation->user_id);

                $position   = $serviceContainer->getService('Orgstructure')->findOne($rotation->position_id);
                $department = $serviceContainer->getService('Orgstructure')->findOne($position->owner_soid);

                $href = Zend_Registry::get('view')->serverUrl('hr/rotation/report/index/rotation_id/' . $rotation->rotation_id);
                $url = '<a href="' . $href . '">' . $href . '</a>';

                $messenger = $serviceContainer->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ROTATION_REPORT,
                    array(
                        'name' => $user->FirstName . ' ' . $user->Patronymic,
                        'begin_date' => date('d.m.Y', strtotime($rotation->begin_date)),
                        'end_date' => date('d.m.Y', strtotime($rotation->end_date)),
                        'rotation_position' => $position->name,
                        'rotation_department' => $department->name,
                        'report_date' => date("d.m.Y", $reportDeadline),
                        'url' => $url
                    ),
                    'rotation',
                    $rotation->rotation_id
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);


                $serviceContainer->getService('HrRotation')->update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'report_notification_sent' => HM_Hr_Rotation_RotationModel::REPORT_NOTIFICATION_SENT
                    )
                );
            }
        }

    }
}
