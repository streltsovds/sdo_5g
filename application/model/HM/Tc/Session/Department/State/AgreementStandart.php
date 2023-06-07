<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 13:38
 */

class HM_Tc_Session_Department_State_AgreementStandart extends HM_State_Abstract {
    
    public function onStateStart()
    {
        $params = $this->getParams();
        $sessionDepartmentId = $params['department_id'];
        
        $orgService = $this->getService('Orgstructure');
        $departmnet = $orgService->fetchAll(array('soid = ?' => $sessionDepartmentId))->current();
        $departments = $orgService->getAllOwnersInTree($sessionDepartmentId);
        
        $responsibilityService = $this->getService('Responsibility');
        $responsibleUsers = $responsibilityService->getResponsibleUsersByItemIds($departments);

        foreach ($responsibleUsers as $responsibleUser) {
            try {
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_LEARNING_PLANNED,
                    array(
                        'dep_org' => '"'.$departmnet->name.'"',
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $responsibleUser['user_id']);            
            } catch (Exception $exc) {
            }
        }

        $sessionService = $this->getService('TcSession');
        $processService = $this->getService('Process');
        $session = $sessionService->getOne($sessionService->find($this->_params['session_id']));
        $sessionState = $processService->getCurrentState($session);
        if ($sessionState instanceof HM_Tc_Session_State_Publish) {
            $processService->goToNextState($session);
        }

        parent::onStateStart();
    }


    public function isNextStateAvailable()
    {
        return true;
    }

    public function onNextState()
    {
        return true;
    }

    public function getDescription()
    {
        return _('На этом этапе проходит проверка консолидированной заявки на предмет соответствия установленным требованиям к обучению.
            Переводя консолидированную заявку на следующий этап, специалист по обучению подтверждает, что заявка соответствует
            актуальному требованиям к планированию обучения.
            ');
    }

    public function initMessage() { return 'initMessage'; }

    public function onNextMessage() { return 'onNextMessage'; }

    public function onErrorMessage()
    {
        return _("При создании консолидированной заявки возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Консолидированная заявка отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function getCurrentStateMessage()
    {
        return _('Согласование с нормативными документами');
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        /*$actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'module' => 'session',
                    'controller' => 'list',
                    'action' => 'view',
                    'session_id' => $params['session_id'],
                    'baseUrl' => 'tc'),
                'title' => _('Просмотреть консолидированную заявку')
            ),
            array(), // всем
            $this
        );*/

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'module' => 'session',
                        'controller' => 'consolidated',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_CONTINUING,
                        'session_department_id' => $params['session_department_id'],
                        'baseUrl' => 'tc'),
                    'title' => _('Перевести на следующий этап')
                ),array(
                    'roles' => array(
                        HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        HM_Role_Abstract_RoleModel::ROLE_DEAN)
                ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'module' => 'session',
                        'controller' => 'consolidated',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                        'session_department_id' => $params['session_department_id'],
                        'baseUrl' => 'tc'),
                    'title' => _('Не отправлять заявку от подразделения')
                ), array(
                    'roles' => array(
                        HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        HM_Role_Abstract_RoleModel::ROLE_DEAN)
                ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }

        return $actions;
    }
} 