<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 22.09.2014
 * Time: 14:56
 */

class Session_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    /** @var HM_Tc_Session_SessionService $_defaultService */
    protected $_defaultService;

    protected $_session = null;
    protected $_sessionId = 0;

    const ACTION_FINISH_BY = 'finish-by';
    const ERROR_FINISH_BY = 'error-finish-by';

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Сессия успешно создана'),
            self::ACTION_UPDATE    => _('Сессия успешно обновлёна'),
            self::ACTION_DELETE    => _('Сессия успешно удалёна'),
            self::ACTION_DELETE_BY => _('Сессии успешно удалены'),
            self::ACTION_FINISH_BY => _('Процессы планирования успешно завершены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE => _('Сессия не была создана'),
            self::ERROR_NOT_FOUND        => _('Сессия не найдена'),
            self::ERROR_FINISH_BY        => _('Во время завершения процессов планирования произошли ошибки')
        );
    }

    public function init()
    {
        $this->_defaultService = $this->getService('TcSession');

        $this->_setForm(new HM_Form_TcSession());
        $this->_sessionId = (int) $this->_getParam('session_id', 0);
        $this->_session = $this->getOne(
            $this->_defaultService->fetchAllDependence(
                array('Cycle', 'Department'),
                $this->quoteInto('session_id = ?', $this->_sessionId))
        );


        if ($this->_session) {
            //$this->_getForm()->setDefaults($provider);
            if($this->getRequest()->getActionName() != 'description'){
                $this->view->setExtended(
                    array(
                        'subjectName' => 'TcSession',
                        'subjectId' => $this->_sessionId,
                        'subjectIdParamName' => 'session_id',
                        'subjectIdFieldName' => 'session_id',
                        'subject' => $this->_session
                    )
                );
            }
        }
        parent::init();
    }

    public function indexAction()
    {

        $view            = $this->view;
        $providerId       = $this->_getParam('provider_id', 0);

        $grid = HM_Session_Grid_SessionGrid::create(array(
            'controller' => $this,
        ));
        $options= array(
            'providerId'   => $providerId,
            'departmentId' => $this->getService('Orgstructure')->getResponsibleDepartments()
        );
        $listSource = $this->_defaultService->getListSource($options);

        $view->assign(array(
            'grid'          => $grid->init($listSource),
            'gridAjaxRequest' => $this->isGridAjaxRequest()
        ));
    }

    public function setDefaults(Zend_Form $form)
    {
        $form->populate(array(
                'session_id' => $this->_sessionId,
                'name'       => $this->_session->name,
                'date_begin' => strtotime($this->_session->date_begin) ? date('d.m.Y', strtotime($this->_session->date_begin)) : '',
                'date_end'   => strtotime($this->_session->date_end)   ? date('d.m.Y', strtotime($this->_session->date_end))   : '',
//                'norm' => $this->getService('Option')->getOption('standard')
            )
        );

    }

    public function create(Zend_Form $form)
    {
        $values = $form->getValues();

        if (!empty($values['date_begin'])) {
            $begin = new HM_Date($values['date_begin']);
            $values['date_begin'] = $begin->toString(HM_Date::SQL_DATE);
        } else
        {
            unset($values['date_begin']);
        }
        if (!empty($values['date_end'])) {
            $begin = new HM_Date($values['date_end']);
            $values['date_end'] = $begin->toString(HM_Date::SQL_DATE);
        } else
        {
            unset($values['date_end']);
        }

        unset($values['session_id']);

        if ($values['year']) {
            $year = (int) $values['year'];
            $cycle = $this->getService('Cycle')->fetchOne(
                array(
                    'year = ?' => $year,
                    'quarter = ?' => 0,
                    'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING
                )
            );

            $begin = $year.'-01-01';
            $end = $year.'-12-31';

            if (!$cycle) {
                $cycle = $this->getService('Cycle')->insert(
                    array(
                        'name' => $year. _(' год'),
                        'begin_date' => $begin,
                        'end_date' => $end,
                        'type' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING,
                        'year' => $year,
                        'quarter' => 0
                    )
                );
            }

//            $values['date_begin'] = $begin;
//            $values['date_end'] = $end;

            $values['cycle_id'] = $cycle->cycle_id;
            unset($values['year']);
        }
        $res = $this->getService('TcSession')->insert($values);
    }

    public function update(Zend_Form $form)
    {
        $values = $form->getValues();

        $data   = array(
            'session_id' => $this->_sessionId,
            'name'       => $values['name']
        );

        // как правильно?
        if (!empty($values['date_begin'])) {
            $begin = new HM_Date($values['date_begin']);
            $data['date_begin'] = $begin->toString(HM_Date::SQL_DATE);
        }
        if (!empty($values['date_end'])) {
            $end = new HM_Date($values['date_end']);
            $data['date_end'] = $end->toString(HM_Date::SQL_DATE);
        }

        $this->getService('TcSession')->update($data);
    }

    public function delete($id)
    {
        return $this->_defaultService->delete($id);
    }

    public function createFromOrgstructureAction()
    {
        $form = $this->_getForm();

        $request = $this->getRequest();
        if (!$this->_getParam('postMassIds_grid')) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirector->gotoSimple('index');
                } else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirector->gotoSimple('index');
                }
            } else {
                $departments = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
                    array('soid in (?) ', ' AND type = ?'),
                    array(explode(',', $form->getValue('checked_items')), HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT)
                ))->getList('soid','name');
                $form->setDefault('checked_items_names', implode("\r\n", $departments));

//                $form->setDefault('norm', $this->getService('Option')->getOption('standard'));
/*
                $pd = $form->getValue('planning_department');
                if ($pd) {
                    $item = $this->getOne($this->getService('Orgstructure')->find($pd[0]));
                    $form->populate(array('planning_department' => array($item->soid => $item->name)));
                }
*/
            }
        } else {
            $ids = explode(',', $this->_getParam('postMassIds_grid'));
            $departments = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
                array('soid in (?) ', ' AND type = ?'),
                array($ids, HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT)
            ))->getList('soid','name');

            if (count($ids) != count($departments)) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Сессию планирования можно создать только для подразделений')));

                $url = array(
                    'baseUrl' => '',
                    'controller' => 'list',
                    'module' => 'orgstructure' ,
                );
                $this->_redirector->gotoUrl($this->view->url($url, null, true), array('prependBase' => false));
            }
            //$form->setDefault('name', _('Сессия планирования: '.implode(', ', $departments)));
            //$form->setDefault('item_type', $this->_getParam('item-type', 'soid'));
            $form->setDefault('checked_items', $this->_getParam('postMassIds_grid'));
            $form->setDefault('checked_items_names', implode("\r\n", $departments));
//            $form->setDefault('norm', $this->getService('Option')->getOption('standard'));
        }
        $this->view->form = $form;
    }

    public function viewAction() {
        // = $this->_getParam('session_id', 0);

        $session = $this->getOne($this->_defaultService->fetchAllDependence(
                array('Cycle'),
                $this->quoteInto('session_id = ?', $this->_sessionId))
        );
        if (!$session) {
            $this->_redirectToIndex();
        }

        $data = array('session' => $session->getCardFields());

        $departmentIds = unserialize($session->checked_items);//array_merge(array($session->planning_department), unserialize($session->checked_items));
        if(is_array($departmentIds)&& count($departmentIds)){
            $departments = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
                'soid in (?)' , $departmentIds
            ))->getList('soid', 'name');
            $data['departments'] = array_values($departments);
        }
        $view = $this->view;

        $view->data = $data;
    }

    public function finishByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $error = false;
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $sessionId) {
                    $result = $this->_defaultService->changeState($sessionId, HM_State_Abstract::STATE_STATUS_FAILED);
                    if (!$result) {
                        $error = true;
                    }
                }
                if (!$error) {
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_FINISH_BY));
                }else {
                    $this->_flashMessenger->addMessage(
                        array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
                            'message' => $this->_getErrorMessage(self::ERROR_FINISH_BY))
                    );
                }
            }
        }
        $this->_redirectToIndex();
    }
    public function finishAction()
    {
        $this->_redirectToIndex();
    }

    public function changeStateAction()
    {
        $sessionId  = $this->_getParam('session_id',0);
        $state = (int) $this->_getParam('state', 0);
        $currentState = $this->_defaultService->changeState($sessionId, $state);
        if ($currentState) {
            switch ($state) {
                case HM_State_Abstract::STATE_STATUS_FAILED:
                    $message = _('Сессия планирования успешно отменена.');
                    break;
                default:
                    $session = $this->_defaultService->getOne($this->_defaultService->find($sessionId));
                    $state = $this->getService('Process')->getCurrentState($session);

                    $message = $state instanceof HM_Tc_Session_State_Complete
                         ? _('Сессия планирования успешно завершена')
                         : _('Сессия планирования успешно переведена на следующий этап');
            }
            $this->_flashMessenger->addMessage($message);
        }else {
            $session = $this->getOne($this->_defaultService->find($sessionId));
            $sessionState = $this->getService('Process')->getCurrentState($session);
            $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => $sessionState->onErrorMessage())
            );
        }
        $this->_redirector->gotoUrl('session/list/view/session_id/' . $sessionId);
    }

    public function workflowAction()
    {
        $sessionId = $this->_getParam('index', 0);

        if(intval($sessionId) > 0){

            $model =  $this->getService('TcSession')->find($sessionId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }

    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $isValid = $form->isValid($request->getParams());
/*
            if ($checkPD = $this->getService('TcSession')->checkPlanningDepartment($form->getValue('planning_department'), $form->getValue('checked_items'))) {
                $form->getElement('planning_department')
                    ->addError($checkPD);
                $isValid = false;
            }
*/
            if ($isValid) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            } else {
                $populate = array(
//                    'norm' => $this->getService('Option')->getOption('standard')
                );
/*
                $plDep = $form->getValue('planning_department');
                if ($plDep) {
                    $populate['planning_department'] = $this->getService('Orgstructure')->find($plDep[0])->getList('soid', 'name');
                }
*/
                $items = $form->getValue('checked_items');
                if (is_array($items) && !empty($items)) {
                    $department = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->find($items[0]));

                    $orgElement = $form->getElement('checked_items');
                    $positionIdJQueryParams = $orgElement->getAttrib('jQueryParams');
                    $positionIdJQueryParams['selected']   = 0;
                    $positionIdJQueryParams['itemId']     = $department->owner_soid;
                    $orgElement->setAttrib('jQueryParams', $positionIdJQueryParams);
                    $populate['checked_items'] = 0;
                }

                $form->populate($populate);
            }
        } else {
            $form->setDefaults(array(
                'date_begin' => date('d.m.Y'),
                'date_end'   => date('d.m.Y', mktime(0, 0, 0, date("m"), date("d") + 28, date("Y"))) // + 4 недели #27417
            ));
            $form->populate(array(/*'norm' => $this->getService('Option')->getOption('standard')*/));
        }
        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $form->removeElement('cycle_id');
//        $form->removeElement('planning_department');
        $form->removeElement('checked_items_names');
        $form->removeElement('checked_items');

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->update($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                    $this->_redirectToIndex();
                }
            } else {
                $form->populate(array(
//                    'norm' => $this->getService('Option')->getOption('standard')
                ));
            }
        } else {
            $sessionId = $request->getParam('session_id');
            $session = $this->getService('TcSession')->getOne(
                $this->getService('TcSession')->find($sessionId)
            );
            $cycle = $this->getService('Cycle')->getOne(
                $this->getService('Cycle')->find($session->cycle_id)
            );
            $values = array(
                'session_id' => $session->session_id,
                'name' => $session->name,
                'year' => $cycle->year,
//                'checked_items' => $session->checked_items,
//                'checked_items_names' => $session->checked_items_names,
                'date_begin' => date("d.m.Y", strtotime($session->date_begin)),
                'date_end' => date("d.m.Y", strtotime($session->date_end)),
//                'norm' => $session->norm,
            );
            $form->setDefaults($values);
            $values = array();
            $form->setDefaults($values);
        }
        $this->view->form = $form;
    }

    public function rollbackAction()
    {
        $result = $this->_defaultService->changeState($this->_sessionId, HM_State_Abstract::STATE_STATUS_ROLLBACK);
        if ($result) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Cессия планирования успешно возвращена на предыдущий этап, все связанные с ней, завершенные консолидированные заявки возвращены на согласование')));
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось вернуть сессию планирования на предыдущий этап')));
        }
        $this->_redirector->gotoUrl('session/list/view/session_id/' . $this->_sessionId);
    }

    public function planAction()
    {
        $type = $this->_getParam('type', 0);
        $sourceData = $this->getService('TcApplication')->getYearPlanArray($this->_sessionId, array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE));
        
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $sourceData = array_merge($sourceData, $this->getService('TcApplicationImpersonal')->getYearPlanArray($this->_sessionId, array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)));
        }

        $destData = array();
        foreach($sourceData as $row) {
            $provider = $this->getService('TcProvider')->getOne($this->getService('TcProvider')->find($row['provider_id']));
            $period = new HM_Date($row['period']);
            $destData[] = array(
                'dep'=>$row['department_name'],
                'job'=>$row['position'],
                'cat'=>$row['category'],
                'fio'=>$row['fio'],
                'tema'=>$row['subject'],
                'dir'=>$row['subject_direction'],
                'srok'=>(intval(intval($period->toString('MM'))/3)+1)." "._("квартал"),
                'contr'=> $provider->name,
                'item'=>HM_Tc_Application_ApplicationModel::getCostItem($row['cost_item']),
                'price'=>$row['price'] * (isset($row['quantity']) ? $row['quantity'] : 1),
            );
        }

        $data = array('Y'=>date('y'), 'table_1'=>$destData);

        //Формируем сводку по подразделению
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            if ($department = $this->getService('Orgstructure')->getResponsibleDepartment()) {
                $sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant($this->_session, $department->soid);
            }
            $options = array(
                'sessionId' => $this->_session->session_id,
                'departmentId' => $department ? $department->soid : 0,
                'sessionDepartmentId' => $sessionDepartment ? $sessionDepartment->session_department_id : 0,
                'showButton' => false
            );
            $listSource = $this->getService('TcApplication')->getClaimantListSource($options);
            $res = $listSource->query()->fetchAll();
            $fact = $req = 0;
            $additionalCounter  = 0;
            foreach($res as $val){
                if ($val['category'] != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {
                    $fact += $val['price'];
                    $additionalCounter++;
                } else {
                    $req += $val['price'];
                }
            }
            $data['cost_required_learning'] = number_format($req, 0, '.', ' ');
            $data['cost_recommended_learning'] = number_format($fact, 0, '.', ' ');
            $data['count_courses'] = $additionalCounter;
        }


        $this->getService('PrintForm')->makePrintForm(
            $type=='word' ? HM_PrintForm::TYPE_WORD : HM_PrintForm::TYPE_EXCEL, 
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) ? HM_PrintForm::FORM_STUDY_PLAN_MANAGER : HM_PrintForm::FORM_STUDY_PLAN, 
            $data,
            'study_plan_'.$this->_getParam('session_id')
        );
        die('Ошибка генерации отчета');
    }

    public function pastAction()
    {
        $employee = $this->getRequest()->getParam('employee');

        $user = $this->getService('User')->getOne(
            $this->getService('User')->find($employee)
        );

        $fio = $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic;
        $this->view->setSubHeader($fio);

        $grid = HM_Session_Grid_PastGrid::create(array(
            'controller' => $this,
        ));

        $options= array(
            'employee' => $employee
        );

        $pastSource = $this->_defaultService->getPastSource($options);

        $this->view->assign(array(
            'grid'            => $grid->init($pastSource),
            'gridAjaxRequest' => $this->isGridAjaxRequest()
        ));
    }
} 