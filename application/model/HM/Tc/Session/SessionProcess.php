<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 12:38
 */

class HM_Tc_Session_SessionProcess extends HM_Process_Type_Static
{
    public function onProcessStart(){}

    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_TC_SESSION;
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
        $sessionService = $session->getService();
        /** @var HM_Tc_Session_Department_DepartmentService $sessionDepartmentService */
        $sessionDepartmentService = $sessionService->getService('TcSessionDepartment');
        /** @var HM_Process_ProcessService $processService */
        $processService = $sessionService->getService('Process');

        $result = $sessionService->updateWhere(
            array('status' => HM_Tc_Session_SessionModel::FINISHED),
            $sessionService->quoteInto('session_id = ?', $session->session_id)
        );

        // дергаем все БП групповых и фейлим все незавершенные
        $sessionDepartments = $sessionDepartmentService->fetchAll(
            $sessionDepartmentService->quoteInto('session_id = ?', $session->session_id)
        );
        foreach ($sessionDepartments as $sessionDepartment) {
            $state = $processService->getCurrentState($sessionDepartment);
            if (!$state) {
                continue;
            }
            if (!($state instanceof HM_Tc_Session_Department_State_Complete) &&
                $state->getStatus() != HM_State_Abstract::STATE_STATUS_FAILED
            ) {
                // автосогласуем все неудалённые консолидированные заявки
                $processService->goToSuccess($sessionDepartment, false, true);
            } elseif (($state instanceof HM_Tc_Session_Department_State_Agreement) &&
                $state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED
            ) {
                $toDelete = Zend_Registry::get('serviceContainer')->getService('TcApplication')->fetchAll(
                    array(
                        "session_id=?" => $session->session_id,
                        "session_department_id=?" => $sessionDepartment->session_department_id
                    )
                )->getList('application_id');

                foreach($toDelete as $id) {
                    Zend_Registry::get('serviceContainer')->getService('TcApplication')->delete($id);
                }
            }
        }
    }

    public function getRedirectionUrl()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $resArray = array('action' => 'index', 'module' => 'session', 'controller' => 'list');
        return Zend_Registry::get('view')->url($resArray, null, true);

    }
}
