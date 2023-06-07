<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 14:02
 */

class SessionQuarter_ConsolidatedController extends HM_Controller_Action {

    /** @var HM_Tc_Session_Department_DepartmentService $_defaultService */
    protected $_defaultService;

    protected $_sessionQuarterId;
    protected $_sessionQuarter;

    public function init()
    {
        $this->_defaultService = $this->getService('TcSessionDepartment');
        $this->_sessionQuarterId = $this->_getParam('session_quarter_id', 0);
        $this->_sessionQuarter = $this->getOne(
            $this->getService('TcSessionQuarter')->find($this->_sessionQuarterId)
        );
        if ($this->_sessionQuarter->session_quarter_id) {
            $this->view->setExtended(
                array(
                    'subjectName' => 'TcSessionQuarter',
                    'subjectId' => $this->_sessionQuarterId,
                    'subjectIdParamName' => 'session_quarter_id',
                    'subjectIdFieldName' => 'session_quarter_id',
                    'subject' => $this->_sessionQuarter
                )
            );
        }
        parent::init();
    }

    public function indexAction()
    {
        $view = $this->view;

        $grid = HM_SessionQuarter_Grid_ConsolidatedGrid::create(array(
            'sessionQuarterId' => $this->_sessionQuarterId,
            'controller' => $this,
        ));

        $gridId = $grid->getGridId();
        if (!$this->_request->getParam("order{$gridId}")) {
            $this->_request->setParam("order{$gridId}", 'session_department_id_ASC');
        }

        $options = array(
            'sessionQuarterId' => $this->_sessionQuarterId,
            'departmentId' => $this->getService('Orgstructure')->getResponsibleDepartments()
        );

        $listSource = $this->_defaultService->getListSourceQuarter($options);

        $view->assign(array(
            'grid' => $grid->init($listSource)
        ));
    }


    public function getLevel1()
    {
        $select = $this->getService('Orgstructure')->getSelect();
        $select->from(array('dzo' => 'structure_of_organ'), array('name'=>'dzo.name'));
        $select->where('dzo.level = 0 and dzo.owner_soid = 0 and dzo.blocked = 0 and dzo.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $departmentIds = $this->getService('Orgstructure')->getResponsibleDepartments();

            $select->joinInner(
                array('resp'=>'structure_of_organ'),
                'dzo.lft<=resp.lft and resp.rgt<=dzo.rgt',
                array()
            );
            $select->where('resp.soid in (?)', $departmentIds);
            $select->group(array('dzo.soid', 'dzo.name'));
        }
        $dzoNames = $select->query()->fetchAll();
        $result = array();
        foreach($dzoNames as $name) {
            $result[$name['name']] = $name['name'];
        }

        return $result;

        /*
                $dzoNames = $this->getService('Orgstructure')->fetchAll(array('level = ?'=>0, 'owner_soid = ?'=>0, 'blocked = ?'=>0, 'type = ?'=>0));
                return $dzoNames->getList('name');
        */
    }

    public function getLevel2($gridId)
    {
        $dzoName = $this->_getParam("dzo_department{$gridId}");

        if(!$dzoName)
            return array();

        $dzo = $this->getService('Orgstructure')->fetchAll(array('level = ?'=>0, 'name LIKE ?'=>$dzoName, 'blocked = ?'=>0, 'type = ?'=>0));
        $dzo = $this->getOne($dzo);

        $select = $this->getService('Orgstructure')->getSelect();
        $select->from(array('level2' => 'structure_of_organ'), array('name'=>'level2.name'));
        $select->where('level2.level = 1 and level2.blocked = 0 and level2.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
        $select->where('level2.owner_soid = ?', $dzo->soid);

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $departmentIds = $this->getService('Orgstructure')->getResponsibleDepartments();

            $select->joinInner(
                array('resp'=>'structure_of_organ'),
                'level2.lft<=resp.lft and resp.rgt<=level2.rgt',
                array()
            );
            $select->where('resp.soid in (?)', $departmentIds);
            $select->group(array('level2.soid', 'level2.name'));
        }
        $dzoNames = $select->query()->fetchAll();
        $result = array();
        foreach($dzoNames as $name) {
            $result[$name['name']] = $name['name'];
        }
        return $result;
    }

    public function changeStateAction()
    {
        $sessionDepartmentId  = $this->_getParam('session_department_id', 0);

        $state = (int) $this->_getParam('state', 0);
        $currentState = $this->_defaultService->changeState($sessionDepartmentId, $state);
        if ($currentState) {
            switch ($state) {
                case HM_State_Abstract::STATE_STATUS_CONTINUING:
                    $state = $this->getService('Process')->getCurrentState($this->_defaultService->getOne($this->_defaultService->find($sessionDepartmentId)));
                    $state instanceof HM_Tc_Session_Department_State_Complete
                        ? $this->_flashMessenger->addMessage(_('Формирование консолидированной заявки успешно завершено'))
                        : $this->_flashMessenger->addMessage(_('Консолидированная заявка успешно переведена на следующий этап'));
                    break;
                case HM_State_Abstract::STATE_STATUS_FAILED:
                    $this->_flashMessenger->addMessage(_('Консолидированная заявка отменена'));
                    break;
                case HM_State_Abstract::STATE_STATUS_ROLLBACK:
                    $this->_flashMessenger->addMessage(_('Консолидированная заявка успешно переведена на предыдущий этап'));
                    break;
            }
        }else {
            $sessionDepartment = $this->getOne($this->_defaultService->find($sessionDepartmentId));
            $sessionDepartmentState = $this->getService('Process')->getCurrentState($sessionDepartment);
            $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => $sessionDepartmentState->onErrorMessage())
            );
        }
        $request = $this->getRequest();
        $this->_redirector->gotoUrl($request->getHeader('referer'));
    }


    public function changeStateByAction()
    {
        /**
         * Да простит меня Бог за этот **** (C) Кирилл
         */
        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();

        $params = $request->getParams();

        foreach ($params as $paramName => $param) {
            if (substr($paramName, 0, 11) === 'postMassIds') {
                $request->setParam('postMassIds_grid', $param);
                break;
            }
        }

        $sessionDepartmentsIds = $request->getParam('postMassIds_grid');
        $sessionDepartmentsIds = explode(',', $sessionDepartmentsIds);
        $successCount = 0;
        foreach ($sessionDepartmentsIds as $sessionDepartmentId) {
            $result = $this->_defaultService->changeState(
                $sessionDepartmentId, HM_State_Abstract::STATE_STATUS_CONTINUING);
            $successCount = ($result) ? $successCount+1 : $successCount;
        }
        $message = sprintf(
            _('Успешно переведено на следующий этап - %d, не могут быть переведены - %d'),
            $successCount, count($sessionDepartmentsIds)-$successCount);
        $this->_flashMessenger->addMessage($message);

        $request = $this->getRequest();
        $this->_redirector->gotoUrl($request->getHeader('referer'));
    }


    public function workflowAction()
    {
        $sessionDepartmentId = $this->_getParam('index', 0);

        if(intval($sessionDepartmentId) > 0){

            $model =  $this->_defaultService->find($sessionDepartmentId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }

}
