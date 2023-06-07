<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 13:16
 */

class HM_Tc_SessionQuarter_State_Publish extends HM_State_Abstract
{
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
        return _('На этом этапе супервайзеры получают уведомления о старте сессии квартального планирования и уточняют потребности в обучении в рамках своих подразделений.');
    }

    public function initMessage() { return 'initMessage'; }

    public function onNextMessage() { return 'onNextMessage'; }

    public function onErrorMessage()
    {
        return _("При создании сессии планирования возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Сессия планирования отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function getCurrentStateMessage()
    {
        return _('Определение потребности в обучении');
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Text(array(
                    'title' => _('Переход на следующий шаг происходит автоматически при готовности хотя бы одной консолидированной заявки')
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'module' => 'session-quarter',
                        'controller' => 'list',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                        'session_quarter_id' => $params['session_quarter_id'],
                        'baseUrl' => 'tc'),
                    'title' => _('Отменить сессию планирования')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL)
                ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }

        return $actions;
    }


    public function onStateStart()
    {
        $this->getService('TcSessionDepartment')->startQuarterProcesses($this->_params['session_quarter_id']);
    }

}