<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 12:38
 */

class HM_Tc_SessionQuarter_SessionQuarterProcess extends HM_Process_Type_Static
{
    public function onProcessStart(){}

    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_TC_SESSION_QUARTER;
    }

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT       => _('Создана'),
            self::PROCESS_STATUS_CONTINUING => _('В процессе'),
            self::PROCESS_STATUS_COMPLETE   => _('Закончена успешно'),
            self::PROCESS_STATUS_FAILED     => _('Отменена'),
        );
    }


    public function onProcessComplete()
    {
        $session = $this->getModel();
        /** @var HM_Tc_Session_SessionService $sessionService */
        $sessionQuarterService = $session->getService();
        /** @var HM_Tc_Session_Department_DepartmentService $sessionDepartmentService */
        $sessionDepartmentService = $sessionQuarterService->getService('TcSessionDepartment');
        /** @var HM_Process_ProcessService $processService */
        $processService = $sessionQuarterService->getService('Process');

        $result = $sessionQuarterService->updateWhere(
            array('status' => HM_Tc_Session_SessionModel::FINISHED),
            $sessionQuarterService->quoteInto('session_quarter_id = ?', $session->session_quarter_id)
        );

        /* У проразделений квартальных сессий нет бизнес процессов
        // дергаем все БП групповых и фейлим все незавершенные
        $sessionDepartments = $sessionDepartmentService->fetchAll(
            $sessionDepartmentService->quoteInto('session_quarter_id = ?', $session->session_quarter_id)
        );
        foreach ($sessionDepartments as $sessionDepartment) {
            $state = $processService->getCurrentState($sessionDepartment);
            if (!$state) {
                continue;
            }
            if (!($state instanceof HM_Tc_Session_Department_State_Complete) &&
                $state->getStatus() != HM_State_Abstract::STATE_STATUS_FAILED
            ) {
                $result = $processService->goToFail($sessionDepartment);
            }
        }
        */
    }

    public function getRedirectionUrl()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $resArray = array('action' => 'index', 'module' => 'session-quarter', 'controller' => 'list');
        return Zend_Registry::get('view')->url($resArray, null, true);

    }
}
