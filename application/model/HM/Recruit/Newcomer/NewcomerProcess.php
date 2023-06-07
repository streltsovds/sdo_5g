<?php

class HM_Recruit_Newcomer_NewcomerProcess extends HM_Process_Type_Static
{
    protected $dayEnd;

    public function onProcessStart()
    {


    }

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT => _('Сессия создана'),
            self::PROCESS_STATUS_CONTINUING => _('Доступ к оценочным мероприятиям открыт'),
            self::PROCESS_STATUS_COMPLETE => _('Сессия завершена'),
            self::PROCESS_STATUS_FAILED => _('Сессия отменена'), // ?
        );
    }

    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING;
    }

    public function getDatesFromDefinition($definition)
    {
        // удваиваем срок этапа "Прохождение плана",
        // если в профиле стоит соотв.галочка
        if (count($this->getModel()->profile)) {
            if ($profile = $this->getModel()->profile->current()) {
                if ($profile->double_time) {
                    switch ($definition['class']) {
                        case 'HM_Recruit_Newcomer_State_Welcome':
                        case 'HM_Recruit_Newcomer_State_Open':
                            break;
                        case 'HM_Recruit_Newcomer_State_Plan':
                            $definition['day_end'] = $definition['day_begin'] + 2 * ($definition['day_end'] - $definition['day_begin']);
                            $this->dayEnd = $definition['day_end'];
                            break;
                        case 'HM_Recruit_Newcomer_State_Publish':
                        case 'HM_Recruit_Newcomer_State_Result':
                        case 'HM_Recruit_Newcomer_State_Complete':
                            $delta = $definition['day_end'] - $definition['day_begin'];
                            $definition['day_begin'] = $this->dayEnd + 1;
                            $definition['day_end'] =
                            $this->dayEnd = $definition['day_begin'] + $delta;
                            break;
                    }
                }
            }
        }

        return $definition;
    }

    // дата начала процесса адаптации - это дата фактического трудорустройства
    public function getBeginDate()
    {
        if (count($this->getModel()->position)) {
            if ($position = $this->getModel()->position->current()) {
                return new HM_Date($position->position_date);
            }
        }
        return new HM_Date();
    }

    public function onProcessComplete()
    {
        $newcomerService = Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer');
        $feedbackService = Zend_Registry::get('serviceContainer')->getService('Feedback');
        $feedbackUserService = Zend_Registry::get('serviceContainer')->getService('FeedbackUsers');

        $newcomer = $this->getModel();
        $newcomerService->update(array(
            'newcomer_id' => $newcomer->newcomer_id,
            'result' => HM_Recruit_Newcomer_NewcomerModel::RESULT_SUCCESS,
            'state' => HM_Recruit_Newcomer_NewcomerModel::STATE_CLOSED,
        ));

        $feedbackUserService->assignUser(
            $newcomer->user_id,
            HM_Feedback_FeedbackModel::NEWCOMER_FEEDBACK_2
        );

        $this->_sendAdaptationCompleteNotification($newcomer);
    }

    protected function _sendAdaptationCompleteNotification($newcomer)
    {
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $user = $userService->findOne($newcomer->user_id);

        $messenger = Zend_Registry::get('serviceContainer')->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_ADAPTING_STOP,
            array(
                'fio' => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
                'url' => '<a href="http://' . $_SERVER['SERVER_NAME'] . '">' . $_SERVER['SERVER_NAME'] . '</a>',
                'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
            ),
            'newcomer',
            $newcomer->newcomer_id
        );
        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
    }
}