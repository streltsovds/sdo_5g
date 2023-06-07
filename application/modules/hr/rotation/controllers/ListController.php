<?php
class Rotation_ListController extends HM_Controller_Action
{
	use HM_Controller_Action_Trait_Grid;

    protected $_rotationsCache = null;
    protected $_positionSoids  = array();
    protected $debts = array();
    
    public function init()
    {
        parent::init();

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'begin_date_DESC');
        }

        $positions = $this->getService('Orgstructure')->fetchAll(array(
            'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
            'blocked = ?' => 0
        ))->getList('soid');
        foreach ($positions as $position) $this->_positionSoids[] = $position;

    }

    public function indexAction()
    {
        $this->view->setHeader(_('Сессии ротации'));
        
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'created_DESC');
        }

        $select = $this->getService('HrRotation')->getSelect();
        $select->from(
            array(
                'r' => 'hr_rotations'
            ),
            array(
                'r.rotation_id',
                'MID' => 'p.MID',
                'sop.state_of_process_id',
                'workflow_id' => 'r.rotation_id',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'r.begin_date',
                'r.end_date',
                'r.name',
                'position' => 'sp.name',
                'pre_position' => 'psp.name',
                'position_id' => 'sp.soid',
                'pre_position_id' => 'psp.soid',
                'department' => 'so.name',
                'pre_department' => 'pso.name',
                'debt' => new Zend_Db_Expr("
                    CASE WHEN(DATEDIFF(day, NOW(), sopd.end_date_planned) < 0) AND r.status != " . HM_Hr_Rotation_RotationModel::STATE_CLOSED . " THEN 1 ELSE 0 END "),

            )
        );

        $select
            ->joinLeft(array('sop' => 'state_of_process'), 'r.rotation_id = sop.item_id AND sop.process_type = '.HM_Process_ProcessModel::PROCESS_PROGRAMM_ROTATION, array())
            ->joinLeft(array('sopd' => 'state_of_process_data'), 'sop.current_state = sopd.state AND sop.state_of_process_id = sopd.state_of_process_id', array())
            ->joinLeft(array('sp' => 'structure_of_organ'), 'sp.soid = r.position_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.soid = sp.owner_soid', array())
            ->joinLeft(array('psp' => 'structure_of_organ'), 'psp.mid = r.user_id', array())
            ->joinLeft(array('pso' => 'structure_of_organ'), 'pso.soid = psp.owner_soid', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = r.user_id', array())
            ->group(
                array(
                    'r.rotation_id',
                    'p.MID',
                    'p.LastName',
                    'p.FirstName',
                    'p.Patronymic',
                    'sop.state_of_process_id',
                    'r.name',
                    'r.begin_date',
                    'r.end_date',
                    'r.status',
                    'sp.name',
                    'psp.name',
                    'so.name',
                    'pso.name',
                    'r.state_change_date',
                    'r.state_id',
                    'sp.soid',
                    'psp.soid',
                    'sopd.end_date_planned',
                )
            );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
            // все по области ответственности, даже не назначенные
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("sp.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $columns = array(
            'rotation_id' => array('hidden' => true),
            'position_id' => array('hidden' => true),
            'pre_position_id' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'state_of_process_id' => array('hidden' => true),
            'debt' => array('hidden' => true),
            'workflow_id' => array(
                'title' => _('Бизнес-процесс'), // бизнес процесс
                'callback' => array(
                    'function' => array($this, 'printWorkflow'),
                    'params' => array('{{workflow_id}}'),
                ),
                'sortable'=>false,
                'position' => 1,
            ),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' =>  $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'view',
                            'gridmod' => null,
                            'baseUrl' => '',
                            'user_id' => ''
                        ),
                        null, true
                    ) . '{{MID}}') .
                    ' <a href="' .
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'edit',
                            'action' => 'card',
                            'gridmod' => null,
                            'baseUrl' => '',
                            'user_id' => ''
                        ),
                        null, true
                    ) .
                    '{{MID}}' . '">' . '{{fio}}</a>',
                'position' => 3,
            ),
            'name' => array(
                'title' => _('Сессия ротации'),
                'decorator' => '<a href="' .
                    $this->view->url(
                        array(
                            'module' => 'rotation',
                            'controller' => 'report',
                            'action' => 'index',
                            'gridmod' => null,
                            'rotation_id' => ''
                        ),
                        null, true
                    ) . '{{rotation_id}}' . '">' . '{{name}}' . '</a>',
                'position' => 2,
            ),
            'position' => array(
                'title' => _('Целевая должность'),
                'decorator' => $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'orgstructure',
                            'controller' => 'list',
                            'action' => 'card',
                            'baseUrl' => '',
                            'org_id' => ''
                        )
                    ) . '{{position_id}}',
                    HM_Orgstructure_OrgstructureService::getIconTitle(HM_Orgstructure_OrgstructureModel::TYPE_POSITION),
                    'icon-custom',
                    'pcard',
                    'pcard',
                    'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass(HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                    ) . ' {{position}}',
                'position' => 4,
            ),
            'department' => array(
                'title' => _('Целевое подразделение'),
                'position' => 5,
            ),
            'pre_position' => array(
                'title' => _('Исходная должность'),
                'decorator' => $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'orgstructure',
                            'controller' => 'list',
                            'action' => 'card',
                            'baseUrl' => '',
                            'org_id' => ''
                        )
                    ) . '{{pre_position_id}}',
                    HM_Orgstructure_OrgstructureService::getIconTitle(HM_Orgstructure_OrgstructureModel::TYPE_POSITION),
                    'icon-custom',
                    'pcard',
                    'pcard',
                    'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass(HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                ) . ' {{pre_position}}',
                'position' => 6,
            ),
            'pre_department' => array(
                'title' => _('Исходное подразделение'),
                'position' => 7,
            ),
            'begin_date' => array(
                'title' => _('Дата начала'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 8,
            ),
            'end_date' => array(
                'title' => _('Дата завершения'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 9,
            ),
        );

        $filters = array(
            'name' => null,
            'workflow_id' => array(
                'render' => 'process',
                'values' => Bvb_Grid_Filters_Render_Process::getStates('HM_Hr_Rotation_RotationModel', 'rotation_id'),
                'field4state' => 'sop.current_state',
//                'field4state' => 'state',
            ),
            'fio' => null,
            'position' => null,
            'department' => array('render' => 'department'),
            'pre_position' => null,
            'pre_department' => null,
            'begin_date' => array('render' => 'Date'),
            'end_date' => array('render' => 'Date'),
        );

        $grid = $this->getGrid($select, $columns, $filters);

        $grid->setClassRowCondition("{{debt}} > 0",'highlighted');

        $grid->addAction(array(
            'module' => 'rotation',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('rotation_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'rotation',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('rotation_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'rotation',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить сессии ротации'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->debts = $this->debts;
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updatePosition($rotationId, $name, $positionId)
    {
        return $this->view->cardLink(
                $this->view->url(array(
                        'module' => 'orgstructure',
                        'controller' => 'list',
                        'action' => 'card',
                        'baseUrl' => '',
                        'org_id' => '')
                ) . $positionId,
                HM_Orgstructure_OrgstructureService::getIconTitle(HM_Orgstructure_OrgstructureModel::TYPE_POSITION),
                'icon-custom',
                'pcard',
                'pcard',
                'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass(HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
            ) . ' <a href="' .
            $this->view->url(
                array(
                    'module' => 'rotation',
                    'controller' => 'report',
                    'action' => 'index',
                    'gridmod' => null,
                    'rotation_id' => ''
                ),
                null, true
            ) . $rotationId . '">' . $name . '</a>';
    }

    public function newAction()
    {
        $this->view->setHeader(_('Новая сессия ротации'));
        $form = new HM_Form_Rotation();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $values = $form->getValues();

                $rotation = $this->getService('HrRotation')->createByPosition($values);

                if (!in_array((int)$values['position_id'], $this->_positionSoids)) {
                    $this->_flashMessenger->addMessage(_('Укажите должность. Возможно, Вы указали подразделение.'));
                    $this->_redirector->gotoSimple('new', 'list', 'rotation');
                }

                $process = $this->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_PROGRAMM_ROTATION);
                $stateDuration = 0;
                foreach ($process->states as $state) {
                    foreach ($state as $item) {
                        if ($item['class'] == 'HM_Hr_Rotation_State_Open') $stateDuration = $item['day_end'] - $item['day_begin'];
                    }
                }
                $beginDate   = new HM_Date($rotation->begin_date);
                $maxPlanDate = HM_Date::getRelativeDate($beginDate, $stateDuration + 1);

                $manager = $this->getService('Orgstructure')->getManager($rotation->position_id);
                if ($manager) {
                    $manager = $manager->user->current();
                    $manager = $manager->getName();
                } else {
                    $manager = 'не указан';
                }

                $href = Zend_Registry::get('view')->serverUrl('/hr/rotation/report/index/rotation_id/'.$rotation->rotation_id);
                $url = '<a href="'.$href.'">'.$href.'</a>';

                $user = $this->getService('User')->findOne($rotation->user_id);

                $position   = $this->getService('Orgstructure')->find($rotation->position_id)->current();
                $department = $this->getService('Orgstructure')->find($position->owner_soid)->current();

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ROTATION_PLAN,
                    array(
                        'name' => $user->FirstName . ' ' . $user->Patronymic,
                        'begin_date' => date('d.m.Y', strtotime($rotation->begin_date)),
                        'end_date' => date('d.m.Y', strtotime($rotation->end_date)),
                        'rotation_position' => $position->name,
                        'rotation_department' => $department->name,
                        'rotation_manager' => $manager,
                        'fill_plan_date' => date("d.m.Y", strtotime($maxPlanDate->get("dd.MM.yyyy"))),
                        'url' => $url
                    ),
                    'rotation',
                    $rotation->rotation_id
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $rotation->user_id);

                $this->_flashMessenger->addMessage(_('Сессия ротации успешно создана'));
                $this->_redirectToIndex();
            }
        }

        $this->view->positionSoids = $this->_positionSoids;
        $this->view->orgId = $request->getParam('org_id');
        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = new HM_Form_Rotation();
        $request = $this->getRequest();
        $rotationId = $request->getParam('rotation_id');
        $rotation = $this->getService('HrRotation')->findOne($rotationId);
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $data = $form->getValues();

                $data['rotation_id'] = $rotationId;
                $data['user_id'] = $data['user_id'][0];
                $data['begin_date'] = date('Y-m-d', strtotime($data['begin_date']));
                $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
                $data['state_change_date'] = date('Y-m-d');
                $this->getService('HrRotation')->update($data);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            if ($rotation) {
                $data = array();

                $data['rotation_id'] = $rotation->rotation_id;
                if ($rotation->user_id) {
                    if ($user = $this->getService('User')->findOne($rotation->user_id)) {
                        $data['user_id'] = array($rotation->user_id => $user->getName());
                    }
                }
                $data['position_id'] = $rotation->position_id;
                $data['begin_date'] = date('d.m.Y', strtotime($rotation->begin_date));
                $data['end_date'] = date('d.m.Y', strtotime($rotation->end_date));

                $form->populate($data);
            }

            $this->setDefaults($form);
        }
        $this->view->positionId = $rotation->position_id;
        $this->view->form = $form;
    }

    public function mapStatus($status)
    {
        switch ($status) {
            case HM_Hr_Rotation_RotationModel::STATE_PENDING:
                return _('Не начата');
            case HM_Hr_Rotation_RotationModel::STATE_ACTUAL:
                return _('Идёт');
            case HM_Hr_Rotation_RotationModel::STATE_CLOSED:
                return _('Закончена');
        }
    }

//    public function createFromRecruitAction()
//    {
//        $vacancyId = $this->_getParam('vacancy_id');
//        if ($vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->findDependence(array('CandidateAssign', 'RecruiterAssign'), $vacancyId))){
//
//            // создать сессию адаптации
//            $position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence('Parent', $vacancy->position_id));
//            if ($position && count($position->parent)) {
//                $rotation = $position->parent->current()->name;
//                if ($position->mid) {
//                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации, т.к. в настоящий момент должность занята другим пользователем')));
//                    $this->_redirector->gotoSimple('index', 'list', 'vacancy');
//                }
//            }
//
//            $collection = $this->getService('RecruitRotation')->fetchAll(array('position_id = ?' => $vacancy->position_id));
//            if (count($collection)) {
//                foreach($collection as $rotation) {
//                    $this->getService('Process')->initProcess($rotation);
//                    $status = $rotation->getProcess()->getStatus();
//                    if (in_array($status, array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING))) {
//                        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации повторно')));
//                        $this->_redirector->gotoSimple('index', 'list', 'vacancy');
//                    }
//                }
//            }
//
//            $this->getService('RecruitRotation')->createByVacancy($vacancy);
//
//            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации успешно создана')));
//            $this->_redirector->gotoSimple('index', 'list', 'rotation');
//        }
//        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Сессия адаптации не создана')));
//        $this->_redirector->gotoSimple('index', 'list', 'vacancy');
//    }
//
//    public function createFromStructureAction()
//    {
//        $positionId = $this->_getParam('org_id');
//        if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence(array('Parent', 'User'), $positionId))){
//
//            // создать сессию адаптации
//            if ($position && count($position->parent)) {
//                $rotation = $position->parent->current()->name;
//                if (!$position->mid) {
//                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации, т.к. в настоящий момент должность никому не назначена')));
//                    if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'rotation') {
//                        $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
//                    } else {
//                        $this->_redirector->gotoSimple('index', 'new-assignments', 'rotation');
//                    }
//                }
//            }
//
//            $collection = $this->getService('RecruitRotation')->fetchAll(array('position_id = ?' => $positionId));
//            if (count($collection)) {
//                foreach($collection as $rotation) {
//                    $this->getService('Process')->initProcess($rotation);
//                    $status = $rotation->getProcess()->getStatus();
//                    if (in_array($status, array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING))) {
//                        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации повторно')));
//                        if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'rotation') {
//                            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
//                        } else {
//                            $this->_redirector->gotoSimple('index', 'new-assignments', 'rotation');
//                        }
//                    }
//                }
//            }
//
//            $this->getService('RecruitRotation')->createByPosition($position);
//
//            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации успешно создана')));
//            $this->_redirector->gotoSimple('index', 'list', 'rotation');
//        }
//        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Сессия адаптации не создана')));
//        if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'rotation') {
//            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
//        } else {
//            $this->_redirector->gotoSimple('index', 'new-assignments', 'rotation');
//        }
//    }

    public function printFormsAction()
    {
        $rotationData = $this->rotationData($this->_getParam('rotation_id'));
        $type = $this->_getParam('type');
        $data = array(
            'fio' => $rotationData['fio'],
            'job' => $rotationData['job'],
            'new' => $rotationData['new'],
        );

        switch ($type) {
            case 'plan':
                $templateId = HM_PrintForm::FORM_ROTATION_PLAN;
                $outFileName = 'rotation_plan_'.$this->_getParam('rotation_id');
                break;
            case 'report':
                $templateId = HM_PrintForm::FORM_ROTATION_REPORT;
                $data['begin'] = $rotationData['begin'];
                $data['end']   = $rotationData['end'];
                $outFileName   = 'rotation_report_'.$this->_getParam('rotation_id');
                break;
        }

        $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, $templateId, $data, $outFileName);
    }

    private function rotationData($rotation_id)
    {
        $result = array();
        $rotation = $this->getService('HrRotation')->getOne(
            $this->getService('HrRotation')->fetchAll(
                $this->getService('HrRotation')->quoteInto('rotation_id=?', $rotation_id)
            )
        );
        $user = $this->getService('User')->getOne(
            $this->getService('User')->fetchAll(
                $this->getService('User')->quoteInto('MID=?', $rotation->getValue('user_id'))
            )
        );

        $position = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto('mid=?', $user->getValue('MID'))
            )
        );

        $newPosition = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto('soid=?', $rotation->getValue('position_id'))
            )
        );

        $result['fio']   = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
        $result['job']   = $position->getValue('name');
        $result['new']   = $newPosition->getValue('name');
        $result['begin'] = date('d.m.Y', strtotime($rotation->getValue('begin_date')));
        $result['end']   = date('d.m.Y', strtotime($rotation->getValue('end_date')));
        return $result;
    }
    
    public function delete($id) 
    {
        $this->getService('HrRotation')->delete($id);
    }

    public function printWorkflow($rotationId)
    {
        if ($this->_rotationsCache === null) {
            $this->_rotationsCache = array();
            $collection = $this->getService('HrRotation')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_rotationsCache[$item->rotation_id] = $item;
                }
            }
        }
        if ($rotationId && count($this->_rotationsCache) && array_key_exists($rotationId, $this->_rotationsCache)){
            $model = $this->_rotationsCache[$rotationId];
            $this->getService('Process')->initProcess($model);
       
            return $this->view->workflowBulbs($model);
        }
        return '';
    }

    public function updateName($rotationId, $name)
    {
        $url = $this->view->url(array(
            'module' => 'hr',
            'controller' => 'report', 
            'action' => 'index', 
            'rotation_id' => $rotationId,
        ));
        return '<a href="' . $url . '">' . $this->view->escape($name) . '</a>';
    }

    public function workflowAction()
    {
        $rotationId = $this->_getParam('index', 0);

        if(intval($rotationId) > 0){

            $model =  $this->getService('HrRotation')->find($rotationId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }

    /* ЭТАПЫ */

    public function planAction()
    {
        if ($rotationId = $this->_getParam('rotation_id')) {
            $this->getService('HrRotation')->planSession($rotationId);
            $this->_flashMessenger->addMessage(_('Сессия ротации успешно переведена на следующий этап'));
        }
        $this->_redirectToRotation();
    }

    public function publishAction()
    {
        if ($rotationId = $this->_getParam('rotation_id')) {
            $this->getService('HrRotation')->publishSession($rotationId);

            $this->_flashMessenger->addMessage(_('Сессия ротации успешно переведена на следующий этап'));
        }
        $this->_redirectToRotation();
    }

    public function resultAction()
    {
        if ($rotationId = $this->_getParam('rotation_id')) {
            $this->getService('HrRotation')->resultSession($rotationId);

            $this->_flashMessenger->addMessage(_('Сессия ротации успешно переведена на следующий этап'));
        }
        $this->_redirectToRotation();
    }

    public function completeAction()
    {
        if ($rotationId = $this->_getParam('rotation_id')) {
            $this->getService('HrRotation')->completeSession($rotationId);
            $this->_flashMessenger->addMessage(_('Сессия ротации успешно завершена'));
        }
        $this->_redirectToRotation();
    }

    public function abortAction()
    {
        if ($rotationId = $this->_getParam('rotation_id')) {
            $this->getService('HrRotation')->abortSession($rotationId);
            $this->_flashMessenger->addMessage(_('Сессия ротации отменена'));
        }
        $this->_redirectToRotation();
    }
    
    public function updateStatus($status)
    {
        return HM_Hr_Rotation_RotationModel::getStatus($status);
    }

    public function updateDebt($debt)
    {
        return HM_Hr_Rotation_RotationModel::getDebt($debt);
    }

    public function updateCurrentState($state, $debt)
    {
        $return = HM_Hr_Rotation_RotationModel::getState($state);
        if ($debt) {
            $return = '<span class="hm-rotation-debt" title="Задолжность">!</span> '.$return;
        }
        return $return;
    }

    public function updateResult($status, $finalComment)
    {
        $status = HM_Hr_Rotation_RotationModel::getResultStatus($status);
        $status = '<span title="'.$finalComment.'">'.$status.'</span>';
        return $status;
    }
    
    public function _redirectToRotation()
    {
        if ($rotationId = $this->_getParam('rotation_id')) {
            $url = $this->view->url(array('module' => 'rotation', 'controller' => 'report', 'action' => 'index', 'rotation_id' => $rotationId, 'programm_event_id' => null));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        } else {
            parent::_redirectToIndex();
        }
    }

    protected function _processPost($post)
    {
        $newPost = array();
        foreach ($post as $key => $value) {
            $parts = explode('_', $key);
            if (count($parts) == 3) {
                if (!is_array($newPost[$parts[0]])) $newPost[$parts[0]] = array();
                if (!is_array($newPost[$parts[0]][$parts[1]])) $newPost[$parts[0]][$parts[1]] = array();
                $newPost[$parts[0]][$parts[1]][$parts[2]] = $value;
            }
        }
        return $newPost;
    }
}
