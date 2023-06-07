<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 13:37
 */

class HM_Tc_SessionQuarter_Department_State_Open extends HM_State_Abstract {
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
        return _('На этом этапе руководитель подразделения уточняет даты обучения пользователей, определяет конкретных участников обучения для обезличенных заявок.');
    }

    public function initMessage() { return 'initMessage'; }

    public function onNextMessage() { return 'onNextMessage'; }

    public function onErrorMessage()
    {
        return _("Ошибка");
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
        return _('Формирование консолидированной заявки');
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'module' => 'session-quarter',
                    'controller' => 'consolidated',
                    'action' => 'change-state',
                    'state' => HM_State_Abstract::STATE_STATUS_CONTINUING,
                    'session_department_id' => $params['session_department_id'],
                    'baseUrl' => 'tc'),
                'title' => _('Завершить этап')
            ),array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_DEAN)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'module' => 'session-quarter',
                    'controller' => 'consolidated',
                    'action' => 'change-state',
                    'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                    'session_department_id' => $params['session_department_id'],
                    'baseUrl' => 'tc'),
                'title' => _('Не отправлять заявку от подразделения')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_DEAN)
            ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }

        return $actions;
    }
} 