<?php
/*
 *  DEPRECATED!!!
 *
 *  Отметку об участии в тренинге вынесли в отдельный атрибут
 *  Этап БП теперь не нужен
 */
class HM_Recruit_Newcomer_State_Welcome extends HM_Recruit_Newcomer_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'baseUrl' => 'recruit',
                        'module' => 'newcomer',
                        'controller' => 'list',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_CONTINUING,
                        'newcomer_id' => $params['newcomer_id']
                    ),
                    'title' => _('Перейти к следующему этапу'),
                    'confirm' => _('Вы действительно подтверждаете факт прохождения welcome-тренинга пользователем?'),
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
                ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'baseUrl' => 'recruit',
                        'module' => 'newcomer',
                        'controller' => 'list',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                        'newcomer_id' => $params['newcomer_id']
                    ),
                    'title' => _('Отменить сессию адаптации')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
                ), 
                $this,
                HM_State_Action::DECORATE_FAIL
            );            
        }         
        return $actions;
    }

    public function getDescription()
    {
        return _('На этом этапе пользователь проходит очный welcome-тренинг.');
    }

    public function onNextState()
    {
        $params = $this->getParams();

        $newcomerService = Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer');
        $feedbackUserService = Zend_Registry::get('serviceContainer')->getService('FeedbackUsers');

        $newcomer = $newcomerService->findOne($params['newcomer_id']);

        $feedbackUserService->assignUser(
            $newcomer->user_id,
            HM_Feedback_FeedbackModel::NEWCOMER_FEEDBACK_1
        );

        // $this->_sendAdaptationCompleteWelcomeNotification($newcomer);

        return true;
    }

    protected function _sendAdaptationCompleteWelcomeNotification($newcomer)
    {
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $user = $userService->findOne($newcomer->user_id);

        $messenger = Zend_Registry::get('serviceContainer')->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_ADAPTING_WELCOME,
            array(
                'fio' => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
                'url' => '<a href="http://' . $_SERVER['SERVER_NAME'] . '">' . $_SERVER['SERVER_NAME'] . '</a>'
            ),
            'newcomer',
            $newcomer->newcomer_id
        );
        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
    }
}