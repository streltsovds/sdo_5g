<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 12:38
 */

class HM_Tc_Session_Department_DepartmentProcess extends HM_Process_Type_Static
{
    public function onProcessStart()
    {
        //!!! это убрали в создание сессии!!
        //Создаем заявки на обязательное обучение
        /*$sessionDepartment  = $this->getModel();
        $sessionService = $this->getService('TcSession');
        $session = $sessionService->getOne($sessionService->find($sessionDepartment->session_id));
        $sessionService->createReqApplications($session);
        $sessionDepartment  = $this->getModel();*/
        /** @var HM_Tc_Session_SessionService $sessionService */
        /*$sessionService = $sessionDepartment->getService()->getService('TcSession');
        $session = $sessionService->getOne(
            $sessionService->fetchAllDependence('Department',
                $sessionService->quoteInto('session_id = ?', $sessionDepartment->session_id)
            )
        );
        $sessionService->createReqApplications($session);*/

        $sessionDepartment  = $this->getModel();
        
        $sessionService = $this->getService('TcSession');
        $session = $sessionService->getOne(
            $sessionService->fetchAll(
                $sessionService->quoteInto(
                    'session_id = ?',
                    $sessionDepartment->session_id
                )
            )
        );
        
        $orgService = $this->getService('Orgstructure');
        $optionService = $this->getService('Option');
        $plan_amount = $optionService->getOption('standard');

        $cycle = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($session->cycle_id)
        );

        try {
            $managers = $orgService->getAllManagersInDep($sessionDepartment->department_id);

            $url = Zend_Registry::get('view')->serverUrl(
                Zend_Registry::get('view')->url(array(
                    'baseUrl' => 'tc',
                    'module' => 'session',
                    'controller' => 'list',
                    'action' => 'view',
                    'session_id' => $session->session_id
                ), null, true));

            foreach ($managers as $manager) {
                $user = $this->getService('User')->getOne(
                    $this->getService('User')->find($manager['MID'])
                );

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_MANAGER_SESSION_STARTED,
                    array(
                        'plan_amount'   => $plan_amount,
                        'NAME_PATRONYMIC' => $user->FirstName . ' ' . $user->Patronymic,
                        'PERIOD'          => $cycle->name,
                        'URL_SESSION'     => '<a href="' . $url . '">' . $url . '</a>',
                        'PLAN_DATE_END'   => date ("d-m-Y", strtotime( $session->date_end)),
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $manager['MID']);
            }
        } catch (Exception $exc) {
        }
       
        

    }

    public function getType()
    {
        $sessionDepartment  = $this->getModel();
        if ($sessionDepartment->session_id) {
            return HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT;
        } elseif ($sessionDepartment->session_quarter_id) {
            return HM_Process_ProcessModel::PROCESS_TC_SESSION_QUARTER_DEPARTMENT;
        }

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
        if ($this->getStatus() == self::PROCESS_STATUS_FAILED) {
            return;
        }

        $sessionDepartment  = $this->getModel();
        $processService = $sessionDepartment->getService()->getService('Process');

        // здесь один код для двух разных процессов :(
        // годовая конс.заявка
        if ($sessionDepartment->session_id) {
            $sessionService = $sessionDepartment->getService()->getService('TcSession');
            $session = $sessionService->getOne($sessionService->find($sessionDepartment->session_id));
            $sessionState = $processService->getCurrentState($session);
            if ($sessionState instanceof HM_Tc_Session_State_Analysis) {
                $processService->goToNextState($session);
            }
        } elseif ($sessionDepartment->session_quarter_id) {

            $sessionService = $sessionDepartment->getService()->getService('TcSessionQuarter');
            $session = $sessionService->getOne($sessionService->find($sessionDepartment->session_quarter_id));
            $sessionState = $processService->getCurrentState($session);
            if ($sessionState instanceof HM_Tc_SessionQuarter_State_Publish) {
                $processService->goToNextState($session);
            }

            $this->_assignStudents($sessionDepartment->session_department_id);
        }

        $applicationService = $sessionDepartment->getService()->getService('TcApplication');
        $applicationService->updateWhere(
            array('status' => HM_Tc_Application_ApplicationModel::STATUS_COMPLETE),
            $applicationService->quoteInto(
                array('session_department_id = ? ', ' AND status = ?'),
                array($sessionDepartment->session_department_id, HM_Tc_Application_ApplicationModel::STATUS_ACTIVE))
        );

    }

    protected function _assignStudents($sessionDepartmentId)
    {
        $tcApplicationService = $this->getService('TcApplication');
        $tcApplications = $tcApplicationService->fetchAll(
            $tcApplicationService->quoteInto(
                array(
                    'session_department_id = ?',
                ),
                array(
                    $sessionDepartmentId,
                )
            )
        );

        foreach ($tcApplications as $tcApplication) {
            $this->getService('Subject')->assignStudent($tcApplication->subject_id, $tcApplication->user_id, array('application_id' => $tcApplication->application_id));
        }

    }

    public function onProcessRollback()
    {
        $sessionDepartment  = $this->getModel();
        $applicationService = $sessionDepartment->getService()->getService('TcApplication');
        $applicationService->updateWhere(
            array('status' => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE),
            $applicationService->quoteInto(
                array('session_department_id = ? ', ' AND status = ?'),
                array($sessionDepartment->session_department_id, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE))
        );
    }

    public function getRedirectionUrl()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $resArray = array('action' => 'index', 'module' => 'session', 'controller' => 'claimant');
        return Zend_Registry::get('view')->url($resArray, null, true);

    }
}