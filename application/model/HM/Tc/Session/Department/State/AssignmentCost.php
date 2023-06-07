<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 13:38
 */

class HM_Tc_Session_Department_State_AssignmentCost extends HM_State_Abstract {
    public function isNextStateAvailable()
    {
        /** @var Hm_Tc_Application_ApplicationService $applicationService*/
        $applicationService = $this->getService('TcApplication');
        $result = $applicationService->countAll($applicationService->quoteInto(
            array('status = ? ', ' AND session_department_id = ? AND cost_item IS NULL'),
            array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, $this->_params['session_department_id'])
        ));
        return ($result) ? false : true;
    }

    public function onNextState()
    {
        return true;
    }

    public function getDescription()
    {
        return _('На этом этапе проходит назначение статей расходов по каждой персональной заявке.
            Переводя групповую заявку на следующий этап, специалист по обучению подтверждает, что всем персональным
            заявкам назначены статьи расходов.Переход на следующий этап запрещен без назначения статей расходов.
            ');
    }

    public function initMessage() { return 'initMessage'; }

    public function onNextMessage() { return 'onNextMessage'; }

    public function onErrorMessage  ()
    {
        return _("Для перевода консолидированной заявки на следующий этап,
            у всех персональных заявок должны быть выставлены статьи расходов");
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
        return _('Назначение статей расходов');
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
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_DEAN)
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
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_DEAN)
                ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }

        return $actions;
    }
} 