<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 13:37
 */

class HM_Tc_Session_Department_State_Open extends HM_State_Abstract
{
    public function isNextStateAvailable()
    {
        /**
         * @var HM_Tc_Session_Department_DepartmentService $tcsdService
         */
        $tcsdService = $this->getService('TcSessionDepartment');

        $sessionDepartment = $tcsdService->getOne(
            $tcsdService->find($this->_params['session_department_id']));
        if (!$sessionDepartment) {
            return false;
        }

        // эти условия неактуальны
/*
        $notWorkers = $tcsdService->getNotWorkersCounter($sessionDepartment->session_department_id);

        $appCount = $this->getService('TcApplication')->countAll($this->getService('TcApplication')->quoteInto(
            array('session_department_id = ?', 'AND status in (?)', 'AND category in (?)'),
            array(
                $sessionDepartment->session_department_id,
                array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE),
                array(HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION, HM_Tc_Application_ApplicationModel::CATEGORY_RECOMENDED))));

        $appMaxCount = max(1, round($notWorkers * HM_Tc_Session_Department_DepartmentModel::DEPARMENT_ADDITIONAL_APPLICATIONS_LIMIT));

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))){
            if ($appCount > $appMaxCount) {
                return false;
            }
        }
*/
        return true;
    }

    public function onNextState()
    {
        return true;
    }

    public function getDescription()
    {
        return _('На этом этапе руководитель подразделения формирует групповую заявку в части дополнительного обучения, а также корректирует параметры обязательного обучения.');
    }

    public function initMessage() { return 'initMessage'; }

    public function onNextMessage() { return 'onNextMessage'; }

    public function onErrorMessage()
    {
        return _("Для перевода консолидированной заявки на следующий этап,
            количество заявок на рекомендованное и дополнительное обучение не должно превышать 30% от общего числа пользователей подразделения");
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
                        'module' => 'session',
                        'controller' => 'consolidated',
                        'action' => 'change-state',
                        'state' => HM_State_Abstract::STATE_STATUS_CONTINUING,
                        'session_department_id' => $params['session_department_id'],
                        'baseUrl' => 'tc'),
                    'title' => _('Перевести на следующий этап')
                ),array(
                    'roles' => array(
                        HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                        HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL)
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
                        HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                        HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
                    )
                ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }

        return $actions;
    }
} 