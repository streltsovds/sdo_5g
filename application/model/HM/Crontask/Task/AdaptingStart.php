<?php
class HM_Crontask_Task_AdaptingStart extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    const DAYS_BEFORE_STEP_END = 14;

    public function getTaskId()
    {
        return 'sendAdaptingStart';
    }

    public function run()
    {

        $serviceContainer = Zend_Registry::get('serviceContainer');
        $date = new DateTime(sprintf('+%d days', self::DAYS_BEFORE_STEP_END));

        $newcomers = $serviceContainer->getService('RecruitNewcomer')->fetchAll(
            array(
                'status IN (?)' => array(HM_Recruit_Newcomer_NewcomerModel::STATE_ACTUAL, HM_Recruit_Newcomer_NewcomerModel::STATE_PENDING),
                'evaluation_start_send = ?' => HM_Recruit_Newcomer_NewcomerModel::EVALUATION_START_NOT_SENT,
                'evaluation_date < ?' => $date->format('Y-m-d H:i:s'),
                'evaluation_date IS NOT NULL' => null,
            )
        );

        foreach ($newcomers as $newcomer) {
            $cycle = $serviceContainer->getService('Cycle')->findOne(
                array(
                    'newcomer_id = ?' => $newcomer->newcomer_id,
                )
            );
            if ($cycle) {
                $kpis = $serviceContainer->getService('AtKpiUser')->fetchAll(
                    array(
                        'user_id = ?' => $newcomer->user_id,
                        'cycle_id = ?' => $cycle->cycle_id,
                    )
                );


                if (!count($kpis)) {

                    $manager = $serviceContainer->getService('Orgstructure')->getManager($newcomer->position_id);
                    $user = $serviceContainer->getService('User')->findOne($newcomer->user_id);
                    if ($manager) {
                        $manager = $manager->user->current();

                        $href = Zend_Registry::get('view')->serverUrl() .
                            Zend_Registry::get('view')->url(array(
                                'baseUrl' => 'at',
                                'module' => 'session',
                                'controller' => 'event',
                                'action' => 'list',
                                'newcomer_id' => $newcomer->newcomer_id,
                            ), null, true);
                        $url = '<a href="' . $href . '">' . $href . '</a>';


                        $messenger = $serviceContainer->getService('Messenger');
                        $messenger->setOptions(
                            HM_Messenger::TEMPLATE_ADAPTING_START,
                            array(
                                'fio_adapt' => $user->getName($newcomer->newcomer_id),
                                'name_patronymic' => $manager->FirstName . ' ' . $manager->Patronymic,
                                'url' => $url,
                                'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                            ),
                            'newcomer',
                            $newcomer->newcomer_id
                        );
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $manager->MID);


                        $serviceContainer->getService('RecruitNewcomer')->update(
                            array(
                                'newcomer_id' => $newcomer->newcomer_id,
                                'evaluation_start_send' => HM_Recruit_Newcomer_NewcomerModel::EVALUATION_START_SENT
                            )
                        );
                    }
                }
            }
        }

    }
}
