<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 13:16
 */

class HM_Tc_SessionQuarter_State_Agreement extends HM_State_Abstract
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
        return _('На этом этапе формируется план обучения, план утверждается ответственными лицами.');
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
        return _('Сессия планирования успешно завершёна');
    }

    public function getCurrentStateMessage()
    {
        return _('Согласование плана обучения');
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array(
                        'module' => 'session-quarter',
                        'controller' => 'list',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_CONTINUING,
                        'session_quarter_id' => $params['session_quarter_id'],
                        'baseUrl' => 'tc'),
                    'title' => _('Завершить сессию планирования')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL)
                ),
                $this,
                HM_State_Action::DECORATE_SUCCESS
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
        } else {

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'module' => 'session-quarter',
                    'controller' => 'student',
                    'action' => 'index',
                    'session_quarter_id' => $params['session_quarter_id'],
                    'baseUrl' => 'tc'),
                'title' => _('Все участники обучения (квартал)')
            ),
                array(), // всем
                $this
            );
        }

        return $actions;
    }

    public function isPrevStateAvailable() {return true;}

    public function onPrevState() {
        /** @var HM_Tc_Session_Department_DepartmentService $sessionDepartmentService */
        $sessionDepartmentService = $this->getService('TcSessionDepartment');
        $sessionDepartments = $sessionDepartmentService->fetchAll(
            $sessionDepartmentService->quoteInto('session_id = ?', $this->_params['session_id'])
        );
        if ($sessionDepartments && count($sessionDepartments)) {
            foreach ($sessionDepartments as $sessionDepartment) {
                $sessionDepartmentService->changeState($sessionDepartment->session_department_id,
                    HM_State_Abstract::STATE_STATUS_ROLLBACK
                );
            }
        }
        return true;
    }
}