<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 22.09.2014
 * Time: 14:56
 */

class SessionQuarter_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;



    /** @var HM_Tc_SessionQuarter_SessionQuarterService $_defaultService */
    protected $_defaultService;

    protected $_sessionQuarter = null;
    protected $_sessionQuarterId = 0;
    protected $sessionIdsCache = null;

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

        $this->_defaultService = $this->getService('TcSessionQuarter');

        $this->_setForm(new HM_Form_TcSessionQuarter());
        $this->_sessionQuarterId = (int) $this->_getParam('session_quarter_id', 0);
        $this->_sessionQuarter = $this->getOne(
            $this->_defaultService->find($this->_sessionQuarterId)
        );
        if ($this->_sessionQuarter) {
            //$this->_getForm()->setDefaults($provider);
            if($this->getRequest()->getActionName() != 'description'){
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
        }

        parent::init();
    }

    public function indexAction()
    {

        $view            = $this->view;
        $providerId       = $this->_getParam('provider_id', 0);

        $grid = HM_SessionQuarter_Grid_SessionGrid::create(array(
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

    public function setDefaults(Zend_Form $form) {
        $form->populate(array(
                'session_id' => $this->_sessionQuarterId,
                'name'       => $this->_sessionQuarter->name,
                'date_begin' => strtotime($this->_sessionQuarter->date_begin) ? date('d.m.Y', strtotime($this->_session->date_begin)) : '',
                'date_end'   => strtotime($this->_sessionQuarter->date_end)   ? date('d.m.Y', strtotime($this->_session->date_end))   : '',
//                'norm' => $this->getService('Option')->getOption('standard')
            )
        );

    }

    public function create(Zend_Form $form)
    {
        $values = $form->getValues();
        $values['date_begin'] = date('Y-m-d', strtotime($values['date_begin']));
        $values['date_end']   = date('Y-m-d', strtotime($values['date_end']));
        unset($values['session_quarter_id']);

        if (isset($values['quarter']) && $values['session_id']) {
            $quarter = (int) $values['quarter'];
            $sessionId = (int) $values['session_id'];

            $session = $this->getService('TcSession')->fetchOne(
                array(
                    'session_id = ?' => $sessionId,
                    'type = ?' => HM_Tc_Session_SessionModel::TYPE_TC
                )
            );

            $sessionCycle = $this->getService('Cycle')->fetchOne(
                array(
                    'cycle_id = ?' => $session->cycle_id
                )
            );

            $year = $sessionCycle->year;

            $cycle = $this->getService('Cycle')->fetchOne(
                array(
                    'year = ?' => $year,
                    'quarter = ?' => $quarter,
                    'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING,
                )
            );
            if (!$cycle) {
                $quarterList = $this->getService('TcSessionQuarter')->getQuarterList();
                switch ($quarter) {
                    case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_1:
                        $begin = $year.'-01-01';
                        $end = $year.'-03-31';
                        break;
                    case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_2:
                        $begin = $year.'-04-01';
                        $end = $year.'-06-30';
                        break;
                    case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_3:
                        $begin = $year.'-07-01';
                        $end = $year.'-09-30';
                        break;
                    case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_4:
                        $begin = $year.'-10-01';
                        $end = $year.'-12-31';
                        break;
                }
                $cycle = $this->getService('Cycle')->insert(
                    array(
                        'name' => $year. _(' год '). ' ' . $quarterList[$quarter],
                        'begin_date' => $begin,
                        'end_date' => $end,
                        'type' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING,
                        'year' => $year,
                        'quarter' => $quarter,
                    )
                );

//                $values['date_begin'] = $begin;
//                $values['date_end'] = $end;
            }

            $values['cycle_id'] = $cycle->cycle_id;
            unset($values['quarter']);
        }

        $this->getService('TcSessionQuarter')->insert($values);
    }

    public function update(Zend_Form $form)
    {
        $values = $form->getValues();

        $data   = array(
            'session_quarter_id' => $this->_sessionQuarterId,
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



        $this->getService('TcSessionQuarter')->update($data);
    }

    public function delete($id)
    {
        return $this->_defaultService->delete($id);
    }

    public function viewAction() {
        // = $this->_getParam('session_id', 0);

        $session = $this->getOne($this->_defaultService->fetchAll(
                $this->quoteInto('session_quarter_id = ?', $this->_sessionQuarterId))
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
                foreach($ids as $sessionQuarterId) {
                    $result = $this->_defaultService->changeState($sessionQuarterId, HM_State_Abstract::STATE_STATUS_FAILED);
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
        $sessionQuarterId  = $this->_getParam('session_quarter_id',0);

        $state = (int) $this->_getParam('state', 0);

        $currentState = $this->_defaultService->changeState($sessionQuarterId, $state);
        if ($currentState) {
            switch ($state) {
                case HM_State_Abstract::STATE_STATUS_FAILED:
                    $message = _('Сессия планирования успешно отменена.');
                    break;
                default:
                    $sessionQuarter = $this->_defaultService->getOne($this->_defaultService->find($sessionQuarterId));
                    $state = $this->getService('Process')->getCurrentState($sessionQuarter);
                    if($state instanceof HM_Tc_SessionQuarter_State_Publish) {
                        $this->notifyManagers($sessionQuarterId);
                    }

                    //
//                    if($state instanceof HM_Tc_SessionQuarter_State_Analysis) {
//                        $this->assignStudents($sessionQuarterId);
//                    }

                    $message = $state instanceof HM_Tc_SessionQuarter_State_Complete
                         ? _('Сессия планирования успешно завершена')
                         : _('Сессия планирования успешно переведена на следующий этап');
            }
            $this->_flashMessenger->addMessage($message);
        }else {
            $sessionQuarter = $this->getOne($this->_defaultService->find($sessionQuarterId));
            $sessionState = $this->getService('Process')->getCurrentState($sessionQuarter);
            $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => $sessionState->onErrorMessage())
            );
        }
        $this->_redirector->gotoUrl('session-quarter/list/view/session_quarter_id/' . $sessionQuarterId);
    }


    public function notifyManagers($sessionQuarterId)
    {
        $options = array('sessionQuarterId' => $sessionQuarterId);
        $listSource = $this->getService('TcSessionDepartment')->getListSourceQuarter($options);
        $data = $listSource->query()->fetchAll();
        $departments = array();
        foreach($data as $row) {
//            if(!$row['fact_count']) continue;
            $departments[] = $row['department_id'];
        }
        $cycle_id = $row ? $row['cycle_id'] : false;

        $managers = array();
        if (count($departments)) {
            $select = $this->getService('Orgstructure')->getSelect();
            $select
                ->from(array('d' => 'structure_of_organ'), array('j.mid'))
                ->joinInner(array('j' => 'structure_of_organ'), 'j.owner_soid = d.soid and j.type=1 and j.is_manager=1', array())
                ->where('d.soid in (?)', $departments);
            $managers = $select->query()->fetchAll();
        }
        if(!count($managers)) return;


        $messenger = $this->getService('Messenger');
        $messenger->setTemplate(HM_Messenger::TEMPLATE_MANAGER_SESSION_QUARTER_STARTED);

        $cycle = $this->getService('Cycle')->find($cycle_id)->current();
        $beginDate = new HM_Date($cycle->begin_date);
        $delay = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('managers_notify_session_quarter_days_before'); 
        $printDate = HM_Date::getRelativeDate($beginDate, -$delay);

        $url = Zend_Registry::get('view')->serverUrl(
            Zend_Registry::get('view')->url(array(
                'baseUrl' => 'tc',
                'module' => 'session-quarter',
                'controller' => 'claimant',
                'action' => 'index',
                'session_quarter_id' => $sessionQuarterId
            ), null, true));

        foreach($managers as $m) {
            $user = $this->getService('User')->getOne(
                $this->getService('User')->find($m['mid'])
            );
            $messenger->assign(
                array(
                    'NAME_PATRONYMIC' => $user->FirstName . ' ' . $user->Patronymic,
                    'PERIOD'          => $cycle->name,
                    'URL_SESSION'     => '<a href="' . $url . '">' . $url . '</a>',
                    'PLAN_DATE_END'   => date ("d-m-Y", strtotime($printDate->get("dd.MM.yyyy"))),
                )
            );
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $m['mid']);
        }
    }

    public function assignStudents($sessionQuarterId)
    {
        $tcApplications = $this->getService('TcApplication')->fetchAll(
            $this->getService('TcApplication')->quoteInto(
                array(
                    'session_quarter_id = ?',
                ),
                array(
                    $sessionQuarterId,
                )
            )
        );

        foreach ($tcApplications as $tcApplication) {
            $this->getService('Subject')->assignStudent($tcApplication->subject_id, $tcApplication->user_id, array('application_id' => $tcApplication->application_id));
        }
    }

    public function workflowAction()
    {
        $sessionQuarterId = $this->_getParam('index', 0);

        if(intval($sessionQuarterId) > 0){

            $model =  $this->getService('TcSessionQuarter')->find($sessionQuarterId)->current();
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
                $populate = array( );
                $form->populate($populate);
            }
        } else {
            $form->setDefaults(array(
                'date_begin' => date('d.m.Y'),
                'date_end'   => date('d.m.Y', mktime(0, 0, 0, date("m"), date("d") + 28, date("Y"))) // + 4 недели #27417
            ));
            $populate = array( );
            $form->populate($populate);
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
            $sessionQuarterId = $request->getParam('session_quarter_id');
            $sessionQuarter = $this->getService('TcSessionQuarter')->getOne(
                $this->getService('TcSessionQuarter')->find($sessionQuarterId)
            );
            $cycle = $this->getService('Cycle')->getOne(
                $this->getService('Cycle')->find($sessionQuarter->cycle_id)
            );
            $values = array(
                'name' => $sessionQuarter->name,
                'session_id' => $sessionQuarter->session_id,
                'quarter' => $cycle->quarter,
                'date_begin' => date("d.m.Y", strtotime($sessionQuarter->date_begin)),
                'date_end' => date("d.m.Y", strtotime($sessionQuarter->date_end)),
            );
            $form->setDefaults($values);
        }
        $this->view->form = $form;
    }

    public function rollbackAction()
    {
        $result = $this->_defaultService->changeState($this->_sessionQuarterId, HM_State_Abstract::STATE_STATUS_ROLLBACK);
        if ($result) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Cессия планирования успешно возвращена на предыдущий этап, все связанные с ней, завершенные консолидированные заявки возвращены на согласование')));
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось вернуть сессию планирования на предыдущий этап')));
        }
        $this->_redirector->gotoUrl('session-quarter/list/view/session_quarter_id/' . $this->_sessionQuarterId);
    }

    public function planReportAction()
    {
        $type = $this->_getParam('type', 0);
        $cycle = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($this->_sessionQuarter->cycle_id)
        );

        $yearCycles = $this->getService('Cycle')->fetchAll(
            array(
                'year = ?' => $cycle->year
            )
        );

        $data       =
        $total      =
        $categories =
        $directions = array();
        $factData   = $this->getService('TcApplication')->getYearFactArray();
        $planData   = $this->getService('TcApplication')->getYearPlanArray(
            $this->_sessionId, array(HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)
        );
        $allCats    = $this->getService('AtCategory')->fetchAll()->getList('category_id', 'name');

        // очищаем плановые данные от ненужных полей и раскладываем по кучкам -
        // $data['cats'] для таблицы ат категорий, $data['dirs'] для направлений обучения
        foreach ($planData as $key => $item) {
            if (is_null($item['at_category_id'])) $item['at_category_id'] = HM_At_Category_CategoryModel::AT_CATEGORY_SPECIALIST;
            if (is_null($item['quarter'])) $item['quarter'] = $this->_getQuarterFromDate($item['period']);
            $item['category'] = $allCats[$item['at_category_id']];

            unset($item['period']);
            unset($item['application_id']);
            unset($item['session_id2']);
            unset($item['subjectId']);
            unset($item['subject_status']);
            unset($item['provider_status']);
            unset($item['fio']);
            unset($item['position']);
            unset($item['department_name']);
            unset($item['manager_id']);
            unset($item['user_city']);
            unset($item['subject']);
            unset($item['subject_city']);
            unset($item['provider_id']);
            unset($item['provider_name']);
            unset($item['format']);
            unset($item['longtime']);
            unset($item['department_goal']);
            unset($item['education_goal']);
            unset($item['cost_item']);
            unset($item['event_name']);
            unset($item['at_category_id']);

            $planData[$key] = $item;
            $data['dirs'][$item['subject_direction']]['plan'][] = $item;
            $data['cats'][$item['category']]['plan'][] = $item;
        }

        // очищаем фактические данные от ненужных полей и раскладываем по кучкам -
        // $data['cats'] для таблицы ат категорий, $data['dirs'] для направлений обучения
        foreach ($factData as $key => $item) {
            if (is_null($item['at_category_id'])) $item['at_category_id'] = HM_At_Category_CategoryModel::AT_CATEGORY_SPECIALIST;
            $item['category'] = $allCats[$item['at_category_id']];
            $item['price'   ] = $item['pay_amount'];

            unset($item['actual_cost_id']);
            unset($item['provider_name']);
            unset($item['document_number']);
            unset($item['pay_date_document']);
            unset($item['subject_id']);
            unset($item['at_category_id']);
            unset($item['pay_amount']);

            $factData[$key] = $item;
            $data['dirs'][$item['subject_direction']]['fact'][] = $item;
            $data['cats'][$item['category']]['fact'][] = $item;
        }

        foreach ($allCats as $cat) {
            if (!isset($data['cats'][$cat])) $data['cats'][$cat] = array('plan' => array(), 'fact' => array());
        }
        foreach ($data['dirs'] as $key => $dir) $directions[] = array('dir' => $key);
        foreach ($data['cats'] as $key => $cat) $categories[] = array('cat' => $key);

        $placeHolders = array(
            'Y'    => $cycle->year,
            'Q'    => $cycle->quarter,
            'cats' => $categories,
            'dirs' => $directions
        );

        // заполняем категории должностей
        foreach ($categories as $key => $item) {
            $name = 'cat';
            $totalRow = array();
            foreach ($yearCycles as $yearCycle) {
                if ($yearCycle->quarter <= $cycle->quarter && $yearCycle->quarter != HM_Tc_SessionQuarter_SessionQuarterModel::WHOLE_YEAR) {
                    $money  = $this->getResultFor('money' , $data[$name.'s'][$item[$name]], $yearCycle);
                    $people = $this->getResultFor('people', $data[$name.'s'][$item[$name]], $yearCycle);

                    $placeHolders[$name.'s'][$key][$name.'_mp'.$yearCycle->quarter] = $money[$yearCycle->quarter]['money']['plan'];
                    $placeHolders[$name.'s'][$key][$name.'_mf'.$yearCycle->quarter] = $money[$yearCycle->quarter]['money']['fact'];
                    $placeHolders[$name.'s'][$key][$name.'_pp'.$yearCycle->quarter] = $people[$yearCycle->quarter]['people']['plan'];
                    $placeHolders[$name.'s'][$key][$name.'_pf'.$yearCycle->quarter] = $people[$yearCycle->quarter]['people']['fact'];

                    $totalRow['money']['plan']  += $money[$yearCycle->quarter]['money']['plan'];
                    $totalRow['money']['fact']  += $money[$yearCycle->quarter]['money']['fact'];
                    $totalRow['people']['plan'] += $people[$yearCycle->quarter]['people']['plan'];
                    $totalRow['people']['fact'] += $people[$yearCycle->quarter]['people']['fact'];
                } else {
                    $placeHolders[$name.'s'][$key][$name.'_mp'.$yearCycle->quarter] = '';
                    $placeHolders[$name.'s'][$key][$name.'_mf'.$yearCycle->quarter] = '';
                    $placeHolders[$name.'s'][$key][$name.'_pp'.$yearCycle->quarter] = '';
                    $placeHolders[$name.'s'][$key][$name.'_pf'.$yearCycle->quarter] = '';
                }
            }
            $placeHolders[$name.'s'][$key][$name.'_mp0'] = $totalRow['money']['plan'];
            $placeHolders[$name.'s'][$key][$name.'_mf0'] = $totalRow['money']['fact'];
            $placeHolders[$name.'s'][$key][$name.'_pp0'] = $totalRow['people']['plan'];
            $placeHolders[$name.'s'][$key][$name.'_pf0'] = $totalRow['people']['fact'];
        }

        // заполняем направлнения обучения
        foreach ($directions as $key => $item) {
            $name = 'dir';
            $totalRow = array();
            foreach ($yearCycles as $yearCycle) {
                if ($yearCycle->quarter <= $cycle->quarter && $yearCycle->quarter != HM_Tc_SessionQuarter_SessionQuarterModel::WHOLE_YEAR) {
                    $money  = $this->getResultFor('money' , $data[$name.'s'][$item[$name]], $yearCycle);
                    $people = $this->getResultFor('people', $data[$name.'s'][$item[$name]], $yearCycle);

                    $placeHolders[$name.'s'][$key][$name.'_mp'.$yearCycle->quarter] = $money[$yearCycle->quarter]['money']['plan'];
                    $placeHolders[$name.'s'][$key][$name.'_mf'.$yearCycle->quarter] = $money[$yearCycle->quarter]['money']['fact'];
                    $placeHolders[$name.'s'][$key][$name.'_pp'.$yearCycle->quarter] = $people[$yearCycle->quarter]['people']['plan'];
                    $placeHolders[$name.'s'][$key][$name.'_pf'.$yearCycle->quarter] = $people[$yearCycle->quarter]['people']['fact'];

                    $total[$yearCycle->quarter]['money']['plan']  += $money[$yearCycle->quarter]['money']['plan'];
                    $total[$yearCycle->quarter]['money']['fact']  += $money[$yearCycle->quarter]['money']['fact'];
                    $total[$yearCycle->quarter]['people']['plan'] += $people[$yearCycle->quarter]['people']['plan'];
                    $total[$yearCycle->quarter]['people']['fact'] += $people[$yearCycle->quarter]['people']['fact'];

                    $totalRow['money']['plan']  += $money[$yearCycle->quarter]['money']['plan'];
                    $totalRow['money']['fact']  += $money[$yearCycle->quarter]['money']['fact'];
                    $totalRow['people']['plan'] += $people[$yearCycle->quarter]['people']['plan'];
                    $totalRow['people']['fact'] += $people[$yearCycle->quarter]['people']['fact'];
                } else {
                    $placeHolders[$name.'s'][$key][$name.'_mp'.$yearCycle->quarter] = '';
                    $placeHolders[$name.'s'][$key][$name.'_mf'.$yearCycle->quarter] = '';
                    $placeHolders[$name.'s'][$key][$name.'_pp'.$yearCycle->quarter] = '';
                    $placeHolders[$name.'s'][$key][$name.'_pf'.$yearCycle->quarter] = '';
                }
            }
            $placeHolders[$name.'s'][$key][$name.'_mp0'] = $totalRow['money']['plan'];
            $placeHolders[$name.'s'][$key][$name.'_mf0'] = $totalRow['money']['fact'];
            $placeHolders[$name.'s'][$key][$name.'_pp0'] = $totalRow['people']['plan'];
            $placeHolders[$name.'s'][$key][$name.'_pf0'] = $totalRow['people']['fact'];
        }

        foreach ($directions as $key => $item) {
            $name = 'dir';
            $total[0]['money']['plan']  += $placeHolders[$name.'s'][$key][$name.'_mp0'];
            $total[0]['money']['fact']  += $placeHolders[$name.'s'][$key][$name.'_mf0'];
            $total[0]['people']['plan'] += $placeHolders[$name.'s'][$key][$name.'_pp0'];
            $total[0]['people']['fact'] += $placeHolders[$name.'s'][$key][$name.'_pf0'];
        }

        // заполняем ИТОГО
        foreach ($yearCycles as $yearCycle) {
            $placeHolders['total_mp'.$yearCycle->quarter] = (!is_null($total[$yearCycle->quarter]['money']['plan']))  ? $total[$yearCycle->quarter]['money']['plan']  : '';
            $placeHolders['total_mf'.$yearCycle->quarter] = (!is_null($total[$yearCycle->quarter]['money']['fact']))  ? $total[$yearCycle->quarter]['money']['fact']  : '';
            $placeHolders['total_pp'.$yearCycle->quarter] = (!is_null($total[$yearCycle->quarter]['people']['plan'])) ? $total[$yearCycle->quarter]['people']['plan'] : '';
            $placeHolders['total_pf'.$yearCycle->quarter] = (!is_null($total[$yearCycle->quarter]['people']['fact'])) ? $total[$yearCycle->quarter]['people']['fact'] : '';
        }

        $this->getService('PrintForm')->makePrintForm($type=='word' ? HM_PrintForm::TYPE_WORD : HM_PrintForm::TYPE_EXCEL, HM_PrintForm::FORM_QUARTER_PLAN_REPORT, $placeHolders, 'quarter_plan_report_'.$this->_sessionQuarterId);
        die('Ошибка генерации отчета');
    }

    protected function getResultFor($resType, $rowData, $cycle)
    {
        $result = array(
            $cycle->quarter => array(
                $resType  => array(
                    'plan' => 0,
                    'fact' => 0
                )
            )
        );

        foreach ($rowData as $type => $data) {
            $moneyCount  =
            $peopleCount = 0;
            foreach ($data as $datum) {
                if ($datum['quarter'] == $cycle->quarter) {
                    switch ($resType) {
                        case 'money':
                            $moneyCount += $datum['price'];
                            break;
                        case 'people':
                            if ($datum['MID'] != 0) $peopleCount += 1;
                            break;
                    }
                }
            }
            $result[$cycle->quarter][$resType][$type] = ($resType == 'money') ? $moneyCount : $peopleCount;
        }

        return $result;
    }

     private function in_array_r($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    private function _getQuarterFromDate($date)
    {
        $parts = explode('-', $date);
        $quarters = array(
            array('01', '02', '03'),
            array('04', '05', '06'),
            array('07', '08', '09'),
            array('10', '11', '12'),
        );

        foreach ($quarters as $key => $quarter) {
            if (in_array($parts[1], $quarter)) return $key + 1;
        }
    }
} 