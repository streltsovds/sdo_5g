<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 13:16
 */

class HM_Tc_Session_State_Analysis extends HM_State_Abstract
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
        return _('На этом этапе менеджер по обучению анализирует поступившие консолидированные заявки, проводит валидацию новых провайдеров и курсов. Определяются статьи расходов, формируется итоговый план обучения.');
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
        return _('Проверка заявок менеджером');
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Text(array(
                    'title' => _('Переход на следующий шаг происходит автоматически при завершении процесса согласования хотя бы одной консолидированной заявки.')
                ),
                array(),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'module' => 'session',
                        'controller' => 'list',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                        'session_id' => $params['session_id'],
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
}