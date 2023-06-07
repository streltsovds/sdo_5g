<?php
class Order_ListController extends HM_Controller_Action
{
	use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $_claimantsCache = null;

    public function init()
    {
        if (!$this->isAjaxRequest()) {

            $this->_subjectId = $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));

            $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if ($this->_subject) {
                $this->initContext($this->_subject);

                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                    
                   
                ]);

            }
        }

        parent::init();
    }

    /**
     * Этой проверкой отсекаем все заявки на курсы BASETYPE_BASE
     * (их согласовывать только в индивид.порядке, поскольку там есть какой-никакой процесс)
     * Все остальные курсы (BASETYPE_PRACTICE, BASETYPE_SESSION) - разрешаем групповое согласование
     *
     * @return unknown
     */
    public function checkBaseAction()
    {
        $this->_helper->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('check-base', 'json')
            ->initContext('json');

        $ids = array(0);
        $ids = array_merge($ids, explode(',', $this->_getParam('ids')) );
        $collection = $this->getService('Claimant')->fetchAllDependence('BaseSubject', array('SID IN (?)' => $ids));

        if(count($collection) == 0){
            $this->view->status = 'fail';
            $this->view->subjects =  _('Нет отмеченных заявок.');
            return;
        }

        foreach($collection as $element){
            if($element->base_subject){
                $this->view->status = 'fail';
                $this->view->subjects =  _('Бизнес-процесс согласования заявок по данным курсам требует дополнительных условий (выбор или формирование учебной сессии для зачисления претендентов). Вы можете воспользоваться диалогом "Бизнес-процесс" для выполнения этих условий.');
                return;
            }
        }

        $this->view->status = 'success';
        return true;
    }


    public function indexAction()
    {
        $subjectId = (int)$this->_getParam('subject_id', 0);
        $switcher = $this->getSwitcherSetOrder($subjectId, 'created_DESC');

        $claimantService = $this->getService('Claimant');
        $select = $claimantService->getSelect();
        $select_fields = [
            'c.SID',
            'workflow_id' => 'c.SID',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'created_fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pc.LastName, ' ') , pc.FirstName), ' '), pc.Patronymic)"),
            'positions' => new Zend_Db_Expr('GROUP_CONCAT(d.name)'),
            'departments' => new Zend_Db_Expr('GROUP_CONCAT(dd.name)'),
            'base_subject_id' => 'sbase.subid',
            'base_subject1' => 'sbase.name',
            's.name',
            's.period',
            's.longtime',
            'c.created',
            'c.created_by',
            'c.type',
            's.begin',
            's.end',
            'c.MID',
            'dublicate' => new Zend_Db_Expr('CASE WHEN (pd.mid is null OR pd.mid=0) THEN 0 ELSE pd.mid END'),
            'c.changing_date',
            'c.comments'
        ];

        if ($switcher) {
            $select_fields[] = 'c.status';
        }

        $select->from(['c' => 'claimants'], $select_fields)
            ->joinInner(['p' => 'People'], 'c.MID = p.MID', [])
            ->joinLeft(['pc' => 'People'], 'c.created_by = pc.MID', [])
            ->joinLeft(['s' => 'subjects'], 'c.CID = s.subid', [])
            ->joinLeft(['sbase' => 'subjects'], 'c.base_subject = sbase.subid', [])
            ->joinLeft(['pd' => 'People'], 'c.dublicate = pd.MID', [])
            ->joinLeft(['d' => 'structure_of_organ'], 'd.mid = c.MID', [])
            ->joinLeft(['dd' => 'structure_of_organ'], 'd.owner_soid = dd.soid', []);

        if (!$switcher) {
            $select->where('c.status = ?', HM_Role_ClaimantModel::STATUS_NEW);
        }

        $arGroup = [
            'c.MID',
            'c.dublicate',
            'p.LastName',
            'p.FirstName',
            'p.Patronymic',
            'pc.LastName',
            'pc.FirstName',
            'pc.Patronymic',
            'c.SID',
            's.name',
            's.period',
            's.longtime',
            'sbase.subid',
            'sbase.name',
            'c.created',
            'c.created_by',
            'c.type',
            's.begin',
            's.end',
            'pd.mid',
            'c.changing_date',
            'c.comments'
        ];

        if ($switcher) {
            $arGroup[] = 'c.status';
        }

        $select->group($arGroup);

        // Область ответственности
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
            $select = $this->getService('Responsibility')->checkUsers($select, '', 'p.MID');
            $select = $this->getService('Responsibility')->checkSubjects($select, 's.subid');
        }

        if ($subjectId) {
            $select->where('c.CID = ' . (int)$subjectId . ' or c.base_subject = ' . (int)$subjectId);
        }
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_USER))){
            //$select->where('c.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER)){
            $select->joinLeft(array('p1' => 'People'), 'c.MID = p1.MID AND p1.head_mid = ' . $this->getService('User')->getCurrentUserId(), array());
            $select->where('(c.MID = ? or p1.MID != NULL)', $this->getService('User')->getCurrentUserId());
        }

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("d.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $grid_fields = array(
            'MID' => array('hidden' => true),
            'dublicate'=>array('hidden'=>true),      
            'SID' => array('hidden' => true),
            'base_subject_id' => array('hidden' => true),
            'period' => array('hidden' => true),
            'longtime' => array('hidden' => true),
            'workflow_id' => array(
                'hidden' => true,
                'title' => _('Бизнес-процесс'), // бизнес проуцесс
                'callback' => array(
                    'function' => array($this, 'printWorkflow'),
                    'params' => array('{{workflow_id}}'),
                ),
            ),
            'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{MID}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'report' => true, 'user_id' => ''), null, true) . '{{MID}}'.'">'.'{{fio}}'.'</a>'),
            'positions' => array(
                'title' => _('Должность'),
            ),
            'departments' => array(
                'title' => _('Подразделение'),
            ),
            'name' => ($subjectId > 0 ? array('hidden' => true) : array(
                'title' => _('Учебный курс/сессия'),
                'callback' => array(
                    'function' => array($this, 'updateSubjectSession'),
                    'params' => array('{{base_subject1}}', '{{name}}')
                )
            )),
            'created_fio' => array(
                'title' => _('Инициатор заявки'),
                'callback' => array(
                    'function' => array($this, 'updateCreatedBy'), 
                    'params' => array('{{type}}', '{{created_by}}', '{{created_fio}}')
                )
            ),
            'created_by' => array('hidden' => true),
            'type' => array('hidden' => true),
            'created' => array('title' => _('Дата поступления заявки')),
            'base_subject1' => array('hidden' => true),
//            'type' => array('title' => _('Источник')),
//            'stateStatus' => array('title' => _('Статус')),
            'stateCurrent' => array('hidden' => true), //array('title' => _('Следующий шаг')),
            'begin' => array('hidden' => true), //array('title' => _('Дата начала обучения')),
            'end' => array('hidden' => true), //array('title' => _('Дата окончания обучения')),
            'changing_date' => array(
                'title' => _('Дата изменения заявки'),
                'format' => 'Date'
            ),
            'comments' => array(
                'title' => _('Комментарий')
            )
        );

        $basic = $this->getService('Subject')->fetchAll(array('base = ?' => HM_Subject_SubjectModel::BASETYPE_BASE));
        $baseList = $basic->getList('subid', 'name');

        $res = array_reverse($baseList, true);
        $res = $res + array(0 => _('--Нет--'));
        $resArray = array_reverse($res, true);
        $grid_filters = array(
               'fio' => null,
               'name' => null,
               'positions' => null,
               'departments' => null,
//               'base_subject1' => array(
//                   'values'=> $resArray,
//                   'callback'=> array('function'=> array($this,'customBaseFilter'),'params'=>array())
//               ),
               'created_fio' => null,
               'created' => array('render' => 'Date'),
//                'begin' => array('render' => 'Date'),
//                'end' => array('render' => 'Date'),
//                'stateCurrent' => array('values' => $this->getService('Process')->getProcessStates(HM_Process_ProcessModel::PROCESS_ORDER, false),'style'=>'width:95px;'),
               'status' => array('values' => $claimantService->getStatuses(),'style'=>'width:70px;'),
           );

        if ($switcher) {
            $grid_fields['status'] = array(
                 'title' => _('Статус'),
                 'callback' => array(
                     'function' => array($this, 'getStatus'),
                     'params'   => array('{{status}}')
                 )
            );
            $grid_fields['process'] = array('hidden' => true);
        }

        $grid = $this->getGrid($select, $grid_fields, $grid_filters, $this->gridId);

        $grid->setGridSwitcher([
            'modes' => [0, 1],
            'param' => 'all',
            'label' => _('Показать все'),
            'title' => _('Показать все заявки, включая обработанные'),
        ]);

        $grid->updateColumn('created', array(
           'format' => array(
               'date',
               array('date_format' => HM_Locale_Format::getDateFormat())
           )
        )
        );

        $grid->updateColumn('begin', array(
           'format' => array(
               'date',
               array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDateBegin'),
                'params' => array('{{begin}}', '{{period}}')
           )
        )
        );

        $grid->updateColumn('end', array(
            'callback' => array(
                'function' => array($this, 'updateDateEnd'),
                'params' => array('{{end}}', '{{period}}', '{{longtime}}')
           )
        )
        );

        $grid->updateColumn('stateStatus', array(
                'callback' => array(
                    'function' => array($this, 'updateStateStatus'),
                    'params' => array('{{stateStatus}}')
                )
            )
        );

        // Прячем массовые действия т.к. все заявки будут обрабатываться осознанно и в соответствии с бизнесс процессом  !!!
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            
            $grid->addMassAction(array('action' => 'accept-by'), _('Принять заявки'));
            $grid->addMassAction(array('action' => 'reject-by'), _('Отклонить заявки'));
            $grid->addMassAction(array('module' => 'message',
                                       'controller' => 'send',
                                       'action' => 'index'),
                                 _('Отправить сообщение'));

            if (!$this->_getParam('all', 0 ) ){
                //
                //  Редактирование курса
                //  ...

                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
                    // Функция ADDDATE в MSsql значится как DATEADD
                                        
                    $adaptr = Zend_Registry::get('config')->resources->db->adapter;
                    $is_mssql = (in_array($adaptr, array('mssql', 'sqlsrv', 'pdo_mssql'))) ? true : false;

                    if ( $is_mssql )
                    {
                        $collection = $this->getService('Subject')->fetchAll(
                            [' 
                                (
                                    period = ' . HM_Subject_SubjectModel::PERIOD_FREE . ' OR 
                                    end IS NULL OR
                                    (period = ' . HM_Subject_SubjectModel::PERIOD_DATES . ' AND end > NOW()) OR 
                                    (period = ' . HM_Subject_SubjectModel::PERIOD_FIXED . ' AND DATEADD(day, longtime, begin) > NOW())  
                                ) 
                                AND
                                state <> ' . HM_Subject_SubjectModel::STATE_CLOSED  
                            ], 
                            'name'
                        );
                    }
                    else {
                        $collection = $this->getService('Subject')->fetchAll(
                            [' 
                                (
                                    period = ' . HM_Subject_SubjectModel::PERIOD_FREE . ' OR 
                                    end IS NULL OR
                                    (period = ' . HM_Subject_SubjectModel::PERIOD_DATES . ' AND end > NOW()) OR 
                                    (period = ' . HM_Subject_SubjectModel::PERIOD_FIXED . ' AND ADDDATE(begin, INTERVAL longtime DAY) > NOW())  
                                ) 
                                AND
                                state <> ' . HM_Subject_SubjectModel::STATE_CLOSED  
                            ], 
                            'name'
                        );
                    }
                }
                if (count($collection)) {
                    $grid->addMassAction( 
                        array(
                            'action' => 'edit-by',
                        ),
                        _('Изменить курс/сессию'),
                        _('Вы уверены, что хотите изменить учебные курсы пользователям?')
                    );
        
                    $grid->addSubMassActionSelect(
                        $this->view->url( 
                            array(
                                'action' => 'edit-by',
                            )
                        ),
                        'subjectId',
                        $collection->getList('subid', 'name'),
                        false
                    );
                }

                //  ...
                //  Редактирование курса
                //
            }
            
            $grid->addAction(array('module' => 'message',
                    'controller' => 'send',
                    'action' => 'index'),
                array('MID'),
                _('Отправить сообщение'));
            
//            $grid->addAction(array(
//                'module' => 'order',
//                'controller' => 'union',
//                'action' => 'index'
//            ),
//                array('MID','dublicate'),
//                _('Объединить дубликаты')
//            );
//            //подсвечиваем дубликаты
//            $grid->setClassRowCondition("{{dublicate}}>0","highlighted");

             $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{dublicate}}')
                )
            );
            //обновляем у таблички колонку fio если там есть дубликаты
            $grid->updateColumn('fio',
                array('callback' =>
                    array('function' => array($this, 'updateFiodublicate'),
                          'params'   => array('{{fio}}','{{dublicate}}')
                    )
                )
            );         
        }

        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
    }

    public function getStatus($statusId)
    {
        return Zend_Registry::get('serviceContainer')->getService('Claimant')->getStatusTitle($statusId);
    }
    
    public function updateCreatedBy($typeID, $createdBy, $createdFio)
    {
        if ($typeID == HM_Role_ClaimantModel::TYPE_SAP) {
            $type = HM_Role_ClaimantModel::getType($typeID); 
            return $type;
        } elseif ($createdBy) {
            $fio = $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $createdBy), null, true)) . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => $createdBy), null, true) . '">' . $createdFio . '</a>';
            return $fio;
        }
        return '';
    }
    
    /**
     * метод обрабатывает данные из поля dublicate
     * елси значения в этом поле отличны от нуля 
     * выталкиваем из массива _actions последнюю
     * партию, добавленных строк - ссылка объединить
     * @param array {{dublicate}}
     * @param array $actions
     * @return array $tmp
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function updateActions($type, $actions) 
    {
        if ($type > 0) {
            return $actions;
        } else {
            if (is_array($actions)) {
                foreach ($actions as $key => $action) {
                    if (false !== strpos($action['url'], 'dublicate/0')) {
                        unset($actions[$key]);
                        return $actions;
                    }
                }
            } else {
                $tmp = explode('<li>', $actions);
                array_pop($tmp);
                return implode('<li>', $tmp);
            }
        }
    }
     /**
     * метод обрабатывает данные из поля dublicate
     * елси значения в этом поле отличны от нуля 
     * добавляет пометку - дубликат
     * @param array {{fio}}
     * @param array {{dublicate}}
     * @return array $type
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function updateFiodublicate($type1, $type2)
    {
        if($type2>0)
            return trim($type1)."</br><a style='text-decoration:none;color:red' href=''>дубликат</a>";
        else 
            return $type1;
   
    }

    public function acceptLastAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $concreteSubject =  (int) $this->_getParam('concrete_subject', 0);

        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $this->getService('Claimant')->accept($id, $concreteSubject);
            }
        }
        $this->_flashMessenger->addMessage(_('Заявки успешно приняты'));
        $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
    }


    public function acceptAction()
    {
        $claimantId = $this->_getParam('claimant_id', 0);

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $model =  $this->getService('Claimant')->find($claimantId)->current();
        $this->getService('Process')->goToNextState($model);
        
//         $model =  $this->getService('Claimant')->find($claimantId)->current();
//         $this->getService('Process')->initProcess($model);
//         $result = $model->getProcess()->goToNextState();

        $this->_flashMessenger->addMessage(''/*$result*/);
        $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
    }

    public function acceptByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id');
        $postMassParamName = 'postMassIds_grid' . ($subjectId ?: '');
        $claimantIds = explode(',',$this->_getParam($postMassParamName, array()));
        $results = array();

        $this->getService('Lesson')->beginProctoringTransaction();
        if (count($claimantIds)) {
            foreach ($claimantIds as $claimantId) {
                $claimant = $this->getService('Claimant')->find($claimantId)->current();
                $this->getService('Claimant')->accept($claimantId, $claimant->getValue('CID'));
                
//                 $model =  $this->getService('Claimant')->find($claimantId)->current();
//                $this->getService('Process')->goToNextState($model);
            }
            //$this->_flashMessenger->addMessage(implode(' ', $result));
            $this->_flashMessenger->addMessage(_('Заявки успешно приняты'));
        }
        $this->getService('Lesson')->commitProctoringTransaction();
        $params = $subjectId ? ['subject_id' => $subjectId] : ['subject_id' => 0];
        $this->_redirector->gotoSimple('index', 'list', 'order', $params);
    }

    public function rejectByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $postMassParamName = 'postMassIds_grid' . ($subjectId ?: '');
        $ids = explode(',', $this->_getParam($postMassParamName, array()));

        $form = new HM_Form_Comment();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            // reject
            $orders = $this->getService('Claimant')->find($ids);

            foreach($orders as $order) {
                if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) continue;

                    $this->getService('Process')->goToFail($order);

//                 $this->getService('Process')->initProcess($order);
//                 $result = $order->getProcess()->goToFail(array('message' => (strlen($form->getValue('comments_'.$order->SID)) ? $form->getValue('comments_'.$order->SID) : $form->getValue('comments_all'))));

                $this->getService('Claimant')->reject($order->SID, (strlen($form->getValue('comments_'.$order->SID)) ? $form->getValue('comments_'.$order->SID) : $form->getValue('comments_all')));
            }

            $this->_flashMessenger->addMessage(_('Заявки успешно отклонены'));
            $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
        } else {
            $form->setDefault('subject_id', $subjectId);
            $form->setDefault($postMassParamName, $this->_getParam($postMassParamName, ''));
        }
        $this->view->form = $form;
    }

    public function rejectAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $claimantId = $this->_getParam('claimant_id', 0);

        $ids = array($claimantId);

        $orders = $this->getService('Claimant')->findDependence(array('User', 'Subject'), $ids);
        $rejected = array();
        foreach ($orders as $order) {
            if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) {
                $rejected[] = $order->SID;
            }
        }
        $ids = array_diff($ids, $rejected);
        if (count($ids)) {
            $form = new HM_Form_Comment();

            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('postMassIds_grid', implode(',', $ids));

            $this->view->form = $form;

        } else {
            $this->_flashMessenger->addMessage(_('Заявки не выбраны'));
            $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
        }
    }

    public function rejectLastAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $ids = explode(',', $this->_getParam('postMassIds_grid'));
        $orders = $this->getService('Claimant')->findDependence(array('User', 'Subject'), $ids);
        $rejected = array();
        foreach ($orders as $order) {
            if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) {
                $rejected[] = $order->SID;
            }
        }
        $ids = array_diff($ids, $rejected);
        if (count($ids)) {
            $form = new HM_Form_Comment();

            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('postMassIds_grid', $this->_getParam('postMassIds_grid', ''));

            $this->view->form = $form;

        } else {
            $this->_flashMessenger->addMessage(_('Заявки не выбраны'));
            $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => $subjectId));
        }
    }

    public function customBaseFilter($params)
    {
        $params['select']->where('c.base_subject = ?', $params['value']);
    }

    public function updateDateBegin($date, $period)
    {
        if (empty($date)) return '';
        switch ($period) {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return _('Без ограничений');
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return _('Дата регистрации на курс');
        }
        return $date;
    }

    public function updateDateEnd($date, $period, $longtime)
    {
        if (empty($date)) return '';
        switch ($period) {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return _('Без ограничений');
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return sprintf(_('Через %s дней'), $longtime);
        }
        return $this->getDateForGrid($date);
    }

    public function updateSubjectSession($subjectName, $sessionName)
    {
        $return = array();
        if ($subjectName) $return[] = $subjectName;
        if ($sessionName) $return[] = $sessionName;
        return implode(' / ', $return);
    }

    public function printWorkflow($claimantId)
    {
        if ($this->_claimantsCache === null) {
            $this->_claimantsCache = array();
            $collection = $this->getService('Claimant')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_claimantsCache[$item->SID] = $item;
                }
            }
        }

        if ($claimantId && count($this->_claimantsCache) && array_key_exists($claimantId, $this->_claimantsCache)){
            $model = $this->_claimantsCache[$claimantId];
            $this->getService('Process')->initProcess($model);
            return $this->view->workflowBulbs($model);
        }        
        return '';
    }
    
    public function workflowAction()
    {
        $claimantId = $this->_getParam('index', 0);

        if(intval($claimantId) > 0){
            $model =  $this->getService('Claimant')->find($claimantId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }
    public function editByAction()
    {
        if ($this->_request->isPost()) {

            $subjectId = $this->_getParam('subjectId', 0 );
            $postMassParamName = 'postMassIds_grid'; 
            $orderList = $this->_getParam($postMassParamName, array());
            $claimantIds = explode(',', $orderList );

            if (count($claimantIds)) {
                if (!empty($subjectId)) {

                    // $model = $this->getService('Claimant')->fetchAll(
                    //     $this->quoteInto(
                    //             array('SID IN (?)'), array($claimantIds)
                    //     )
                    // );

                    $order = $this->getService('Claimant')->updateWhere( 
                        array( 
                            'CID' => $subjectId,
                            'changing_date' => date('Y-m-d')
                        ),
                        $this->quoteInto( array('SID IN (?)'), array($claimantIds)
                        )
                    );

                    // Отправка сообщения
                    // $messenger = $this->getService('Messenger');

                    // $messenger->setOptions(
                    //     HM_Messenger::TEMPLATE_ORDER_ACCEPTED,
                    //     array(
                    //         'subject_id' => $order->CID
                    //     )
                    // );
                }
                $this->_flashMessenger->addMessage(_('Заявки успешно изменены'));
            }
        }
    $this->_redirector->gotoSimple('index', 'list', 'order', array('subject_id' => 0));
    }
}