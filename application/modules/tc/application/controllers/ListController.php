<?php
/**
 * Created by PhpStorm.
 * User: cuthuk
 * Date: 13.10.2014
 * Time: 7:30
 */


class Application_ListController extends HM_Controller_Action {
    use HM_Controller_Action_Trait_Grid;

    /** @var HM_Tc_Application_ApplicationService $_defaultService */
    protected $_defaultService;

    protected $_session;
    protected $_department;
    protected $_sessionDepartment;

    public function init()
    {
        /** @var  $this->_defaultService HM_Tc_Application_ApplicationService */
        $this->_defaultService = $this->getService('TcApplication');

        // аккордеон
        $sessionId    = $this->_getParam('session_id', 0);
        $this->_session      = $this->getOne(
            $this->getService('TcSession')->fetchAllDependence(
                array('Cycle', 'Department'),
                $this->quoteInto('session_id = ?', $sessionId))
        );

        if ($this->_session) {
            $this->view->setExtended(
                array(
                    'subjectName' => 'TcSession',
                    'subjectId' => $sessionId,
                    'subjectIdParamName' => 'session_id',
                    'subjectIdFieldName' => 'session_id',
                    'subject' => $this->_session
                )
            );
        }

        $currentRole = $this->getService('User')->getCurrentUserRole();
        if (
            $this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))
        ) {
            // менеджер видит только в режиме консолидированной заявки
            if ($this->_department = $this->getService('Orgstructure')->getResponsibleDepartment()) {
                $this->_sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant($this->_session, $this->_department->soid);
            }

        } elseif ($sessionDepartmentId = $this->_getParam('session_department_id', 0)) {

            // если передан session_department_id, значит грид в режиме консолидированной заявки
            $this->_sessionDepartment = $this->getOne($this->getService('TcSessionDepartment')->find($sessionDepartmentId));
        }

        parent::init();
    }

    public function indexAction()
    {
        // фильтры для запроса
        $options = array(
            'sessionId' => $this->_session->session_id,
            // непосредственное подразделение (не уровень конс.заявки)
            'departmentId' => $this->_department ? $this->_department->soid : 0,
            // подразделение уровеня конс.заявки
            'sessionDepartmentId' => $this->_sessionDepartment ? $this->_sessionDepartment->session_department_id : 0,
            'showButton' => false
        );

        if ($options['departmentId'] != 0) {
            $options['department'] = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->find($options['departmentId'])
            );
        }

        $listSource = $this->_defaultService->getClaimantListSource($options);
//        exit($listSource->__toString());

        // если смотрим в режиме консолидированной заявки
        if ($this->_sessionDepartment) {

            // если смотрим в режиме консолидированной заявки - над гридом сводка
            $stmt = $listSource->query();
            $res = $stmt->fetchAll();
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
            $cost = array(
                _('Планируемые затраты на обязательное обучение') => number_format($req, 0, '.', ' '),
                _('Планируемые затраты на рекомендованное и инициативное обучение') => number_format($fact, 0, '.', ' '),
                _('Выбрано курсов инициативного обучения') => $additionalCounter
            );

            $departmentChiefs = $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto(
                    array(' owner_soid = ? AND type = 1 AND is_manager = 1'),
                    array($this->_sessionDepartment->department_id)
                )
            )->getList('mid');

            $currentUser = $this->getService('User')->getCurrentUserId();

            if (in_array($currentUser, $departmentChiefs)) $options['showButton'] = true;
        }

        // собственно, грид
        $grid = HM_Application_Grid_ApplicationGrid::create(
            array_merge(array('controller' => $this), $options)
        );


        $this->view->assign(array(
            'isGridAjaxRequest' => $this->isGridAjaxRequest(),
            'session'           => $this->_session,
            'grid'              => $grid->init($listSource),
            'cost'              => $cost
        ));
    }

    protected function _goToIndex()
    {
        $request = $this->getRequest();
        $this->_redirector->gotoUrl($request->getHeader('referer'));
    }

    /*
     * На эту страницу приходят из следующих мест:
     * * со страницы "все персональные заявки"
     * * со страницы "инициативное обучение" над гридом
     */
    public function createAction()
    {
        $this->view->setHeader(_('Создание заявки'));

        $subjectId = $this->_getParam('subid');
        $categoryParam = $this->getRequest()->getParam('category');
        if ($subjectId) {
            $subject = $this->getService('Subject')->getOne(
                $this->getService('Subject')->find($subjectId)
            );
            if ($subject) {
                $this->view->setSubHeader($subject->name);
            }
        }

        $sessionId = $this->getRequest()->getParam('session_id');
        $session   = $this->getService('TcSession')->getOne(
            $this->getService('TcSession')->find($sessionId)
        );
        $cycle     = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($session->cycle_id)
        );

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_Application();

        // только месяцы, относящиеся к периоду квартального планирования
        $form->getElement('period')->setMultiOptions($tcApplicationService->getSessionPeriodsForForm($this->_session));

        // статьями занимается только менеджер
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $form->removeElement('cost_item');
        } else {
            // только 2 статьи затрат, по которым реально учатся (не конкурс)
            $form->getElement('cost_item')->setMultiOptions($tcApplicationService->getCostItemsForForm());
        }

        // вообще все пользователи, либо только подчиненные
        $form->getElement('users')->setAttrib('multiOptions', $tcApplicationService->getUsersForForm($this->_session));

        $form->getElement('category')->setAttribs(array(
            'onchange' => "getInitialCoursesList();if(this.value == 1) { $('fieldset#fieldset-typegroup').hide(); addPeriodOptions(true,".$cycle->year.",".$sessionId.",".$applicationId.");} else { $('fieldset#fieldset-typegroup').show(); addPeriodOptions(false,".$cycle->year.",".$sessionId.",".$applicationId."); }",
        ));
        // create - это всегда инициативное
        if ($categoryParam && ($categoryParam == HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION)) {
            $form->removeElement('category');
        }

        if ($subject) {
            // конкретный курс, если пришли по прямой ссылке
            $form->removeElement('subject_id');
            $form->addElement('hidden', 'subject_id', array('value' => $subject->subid));

        } else {
            // все курсы, сгруппированные по провайдерам
            $form->getElement('subject_id')->setMultiOptions(
                $tcApplicationService->getSubjectsForForm($categoryParam)
            );
        }

        $post = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($post)) {

                $positionUsers = $this->_getPositionUsers($post['users']);
                $applicationResult = array(
                    'success' => array(),
                    'success_empty' => 0,
                    'dublicate' => array(),
                    'fulldp' => array(),
                    'fulldp_empty' => array(),
                );

                if (!$subject && $post['subject_id']) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                }

                foreach ($positionUsers as $userId => $positionData) {

                    $departmentId = $positionData['department'];
                    $sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant($this->_session, $departmentId);

                    $insert = array(
                        'session_id' => $this->_session->session_id,
                        'department_id' => $departmentId,
                        'session_department_id' => $sessionDepartment->session_department_id,
                        'user_id' => $userId,
                        'position_id' => $positionData['soid'],
                        'criterion_id' => $subject ? $subject->criterion_id : null,
                        'criterion_type' => $subject ? $subject->criterion_type : null,
                        'subject_id' => $subject ? $subject->subid : $post['subject_id'],
                        'provider_id' => $subject ? $subject->provider_id : null,
                        'period' => $post['period'],
                        'category' => ($categoryParam && ($categoryParam == HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION)) ? HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION : $post['category'],
                        'created' => date('Y-m-d'),
                        'status' => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                        'cost_item' => $post['cost_item'],
                        'event_name' => $post['event_name'],
                        'initiator' => $this->getService('User')->getCurrentUserId()
                    );

                    if ($post['category'] != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {
                        $insert['payment_type'] = $post['payment_type'];
                        switch ($post['payment_type']) {
                            case HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY:
                                $insert['price_employee'] = 0;
                                $insert['price'] = $subject ? $subject->price : 0;
                                break;
                            case HM_Tc_Application_ApplicationModel::PAYMENT_EMPLOYEE:
                                $insert['price_employee'] = $subject ? $subject->price : 0;
                                $insert['price'] = 0;
                                break;
                            case HM_Tc_Application_ApplicationModel::PAYMENT_PARTIAL:
                                $price = $subject ? $subject->price : 0;
                                $partialPrice = (int) round($price * $post['payment_percent'] * 0.01);
                                $insert['price_employee'] = $partialPrice;
                                $insert['price'] = $price - $partialPrice;
                                $insert['payment_percent'] = $post['payment_percent'];
                                break;
                        }
                    } else {
                        $insert['payment_type'] = HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY;
                        $insert['price_employee'] = 0;
                        $insert['price'] = $subject ? $subject->price : 0;
                        $insert['payment_percent'] = 0;
                    }

                    $tcApplicationService->insert($insert);
                }

                if ($applicationResult['success'] || $applicationResult['success_empty']) {
                    $total = count($applicationResult['success']) + $applicationResult['success_empty'];
                    $plural = sprintf(_n('заявка plural', '%s заявка', $total), $total);
                    $this->_flashMessenger->addMessage(
                        ($total == 1 ? _('Успешно создана ') : _('Успешно созданы ')) . $plural .
                        ($applicationResult['success']
                            ? ((count($applicationResult['success']) == 1
                                    ? _(' для пользователя ')
                                    : _(' для пользователей '))
                                . implode(', ', $applicationResult['success']))
                            : ''
                        )
                    );
                }

                $this->_redirect($this->view->url(
                    array(
                        'baseUrl' => false,
                        'module' => 'application',
                        'controller' => 'list',
                        'action' => 'index',
                        'session_id' => $this->_session->session_id,
                        'category' => null,
                        'subid' => null,
                        'gridmod' => null,
                    )));

            }
        }

        $this->view->form = $form;
    }

    public function createRecommendedAction()
    {
        $this->view->setHeader(_('Создание заявки'));

        $applicationId = $this->_getParam('application_id');
        $application   = $this->getService('TcApplication')->getOne(
            $this->getService('TcApplication')->findDependence('Users', $applicationId)
        );
        if ($application && $application->user) {
            $user = $application->user->current();
            $this->view->setSubHeader($user->getName());
        }

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_Application();

        // только месяцы, относящиеся к периоду квартального планирования
        $form->getElement('period')->setMultiOptions($tcApplicationService->getSessionPeriodsForForm($this->_session));

        // статьями занимается только менеджер
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $form->removeElement('cost_item');
        } else {
            // только 2 статьи затрат, по которым реально учатся (не конкурс)
            $form->getElement('cost_item')->setMultiOptions($tcApplicationService->getCostItemsForForm());
        }

        // только курсы, сгруппированные по провайдерам
        $form->getElement('subject_id')->setMultiOptions($tcApplicationService->getSimilarSubjectsForForm($application));
        // рекомендованное
        $form->getElement('category')->setAttrib('value', HM_Tc_Application_ApplicationModel::CATEGORY_RECOMENDED);

        // конкретный пользователь
        $form->removeElement('users');
        $form->addElement('hidden', 'application_id', array('value' => $application->application_id));


        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getParams();

            if ($form->isValid($post)) {

                // курс мог измениться; этом случае надо изменить и провайдера
                if ($post['subject_id'] && ($post['subject_id'] != $application->subject_id)) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                }

                $data = array(
                    'application_id'  => $applicationId,
                    'period'          => $post['period'],
                    'subject_id'      => $subject ? $subject->subid : $application->subject_id,
                    'provider_id'     => $subject ? $subject->provider_id : $application->provider_id,
                    'status'          => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                    'category'        => $post['category'],
                );
                $sendMessage = false; // #29142 (п.2в)
                $tcApplicationService->update($data, $sendMessage);

                $this->_flashMessenger->addMessage(_('Заявка успешно создана'));

                $this->_redirect($this->view->url(
                    array(
                        'baseUrl' => false,
                        'module' => 'application',
                        'controller' => 'list',
                        'action' => 'index',
                        'session_id' => $this->_session->session_id,
                        'application_id' => null,
                    )));
            }
        }

        $this->view->form = $form;
    }

    /*
     * На эту страницу приходят из следующих мест:
     * * со страницы "все персональные заявки" (все типы)
     * * со страницы "обязательное обучение"
     */
    public function editAction()
    {
        $this->view->setHeader(_('Редактирование заявки'));

        $applicationId = $this->_getParam('application_id');
        $application   = $this->getService('TcApplication')->getOne(
            $this->getService('TcApplication')->findDependence('Users', $applicationId)
        );

        $sessionId = $this->getRequest()->getParam('session_id');
        $session   = $this->getService('TcSession')->getOne(
            $this->getService('TcSession')->find($sessionId)
        );
        $cycle     = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($session->cycle_id)
        );

        if ($application && $application->user) {
            $user = $application->user->current();
            $this->view->setSubHeader($user->getName());
        }

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_Application();

   
        // только месяцы, относящиеся к периоду квартального планирования
        $sessionPeriods = $tcApplicationService->getSessionPeriodsForForm($this->_session, false);
        $form->getElement('period')->setMultiOptions($sessionPeriods);
        $form->getElement('period')->setValue($application->period);

        // статьями занимается только менеджер
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $form->removeElement('cost_item');
        } else {
            // только 2 статьи затрат, по которым реально учатся (не конкурс)
            $form->getElement('cost_item')->setMultiOptions($tcApplicationService->getCostItemsForForm());
        }

        $subject = $this->getService('Subject')->find($application->subject_id)->current();
        if ($subject && $subject->is_labor_safety == 1) {
            $form->getElement('subject_id')->setMultiOptions(array($subject->subid => $subject->name));
            $form->getElement('subject_id')->setAttribs(array(
                'disable' => true,
                'style' => 'background-color: #DDDDDD;',
            ));
            $form->addElement('hidden', 'subject_id', array('value' => $subject->subid));
        } else {
            // только курсы, сгруппированные по провайдерам
            $form->getElement('subject_id')->setMultiOptions(
                $tcApplicationService->getSubjectsForForm()
            );
        }


        // конкретный пользователь
        $form->removeElement('users');
        $form->addElement('hidden', 'application_id', array('value' => $application->application_id));

        $form->getElement('category')->setAttribs(array(
            'onchange' => "getInitialCoursesList();if(this.value == 1) { $('fieldset#fieldset-typegroup').hide(); addPeriodOptions(true,".$cycle->year.",".$sessionId.",".$applicationId.", 'application');} else { $('fieldset#fieldset-typegroup').show(); addPeriodOptions(false,".$cycle->year.",".$sessionId.",".$applicationId.", 'application'); }",
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getParams();

            if ($form->isValid($post)) {

                // курс мог измениться; этом случае надо изменить и провайдера
                if ($post['subject_id'] && ($post['subject_id'] != $application->subject_id)) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                }

                $data = array(
                    'application_id'  => $applicationId,
                    'period'          => $post['period'],
                    'subject_id'      => $subject ? $subject->subid : $application->subject_id,
                    'provider_id'     => $subject ? $subject->provider_id : $application->provider_id,
                    'status'          => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                    'category'        => $post['category'],
                    'cost_item'       => $post['cost_item'],
                );

                if ($post['category'] != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {
                    $data['payment_type'] = $post['payment_type'];
                    switch ($post['payment_type']) {
                        case HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY:
                            $data['price_employee'] = 0;
                            $data['price'] = $subject ? $subject->price : $application->price;
                            break;
                        case HM_Tc_Application_ApplicationModel::PAYMENT_EMPLOYEE:
                            $data['price_employee'] = $subject ? $subject->price : $application->price;
                            $data['price'] = 0;
                            break;
                        case HM_Tc_Application_ApplicationModel::PAYMENT_PARTIAL:
                            $price = $subject ? $subject->price : $application->price;
                            $partialPrice = (int) round($price * $post['payment_percent'] * 0.01);
                            $data['price_employee'] = $partialPrice;
                            $data['price'] = $price - $partialPrice;
                            $data['payment_percent'] = $post['payment_percent'];
                            break;
                    }
                } else {
                    $data['payment_type'] = HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY;
                    $data['price_employee'] = 0;
                    $data['price'] = $subject ? $subject->price : $application->price;
                    $data['payment_percent'] = 0;
                }

                $tcApplicationService->update($data, $this->needSendMessage());

                $this->_flashMessenger->addMessage(_('Заявка успешно отредактирована'));

                $this->_redirect($this->view->url(
                    array(
                        'baseUrl' => false,
                        'module' => 'application',
                        'controller' => 'list',
                        'action' => 'index',
                        'session_id' => $this->_session->session_id,
                        'application_id' => null,
                    )));
            }
        } else {
            $data = array(
                'subject_id'      => $application->subject_id,
                'cost_item'       => $application->cost_item,
                'period'          => $application->period,
                'category'        => $application->category,
                'payment_type'    => $application->payment_type,
                'payment_percent' => $application->payment_percent
            );
            $form->populate($data);
        }
        $this->view->form = $form;
        
    }
   
    public function getPeriodsAction()
    {
        
        $applicationId = $this->_getParam('application_id');
        $application   = $this->getService('TcApplication')->getOne(
            $this->getService('TcApplication')->findDependence('Users', $applicationId)
        );

        $category = $this->_getParam('category');
        $app = ($category == 2) ? false : $application;
        
        $sessionPeriods = $this->getService('TcApplication')->getSessionPeriodsForForm($this->_session, $app);
        
        $options = '';
            foreach ($sessionPeriods as $key => $period) {
                $options .= '<option value="'.$key.'">'.$period.'</option>';
            }

            echo $options;
            exit();
    }

        public function deleteAction()
    {
        $applicationId = $this->_getParam('application_id', 0);
        if ($applicationId) {
            $result = $this->_defaultService->delete($applicationId, $this->needSendMessage());
        }
        if ($result) {
            $this->_flashMessenger->addMessage(_('Персональная заявка успешно удалена'));
        } else {
            $this->_flashMessenger->addMessage(_('При удалении персональной заявки произошли ошибки'));
        }
        $this->_goToIndex();
    }

    public function deleteByAction()
    {
        $grid = $this->_request->getParam('grid');
        $applicationIds = $this->_request->getParam('postMassIds_' . $grid);
        $applicationIds = explode(',', $applicationIds);

        if (is_array($applicationIds) && count($applicationIds)) {
            $count = 0;
            foreach ($applicationIds as $applicationId) {
                $result = $this->_defaultService->delete($applicationId, $this->needSendMessage());
                $count = ($result) ? $count + 1 : $count;
            }
        }
        $this->_flashMessenger->addMessage(sprintf(
            _('Удалено - %d, невозможно удалить - %d'),
            $count, count($applicationIds) - $count)
        );
        $this->_goToIndex();
    }

    public function getSession()
    {
        return $this->_session;
    }


    protected function _getPositionUsers($userIds)
    {
        $select = $this->getService('Orgstructure')->getSelect();
        $select->from(array('so' => 'structure_of_organ'),
            array(
                'user_id' => 'so.mid',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'soid' => 'so.soid',
                'department' => new Zend_Db_Expr('CASE WHEN so.is_manager=0 THEN so.owner_soid ELSE so2.owner_soid END')
            ))
            ->joinInner(array('so2' => 'structure_of_organ'),
                'so2.soid=so.owner_soid',
                array()
            )
            ->joinInner(array('p' => 'People'),
                'so.mid=p.MID',
                array()
            )
            ->where('so.mid in (?)  and so.blocked=0', array($userIds));

        $result = $select->query()->fetchAll();
        $positionUsers = array();
        foreach ($result as $row) {
            $positionUsers[$row['user_id']] = $row;
        }

        return $positionUsers;
    }

    protected function _redirectToIndex()
    {
        $url = array(
            'action'     => 'index',
            'controller' => 'list',
            'module'     => 'application',
            'baseUrl'    => '',
            'session_id' => $this->_session->session_id
        );
        $this->_redirector->gotoUrl($this->view->url($url));
    }
    
    protected function needSendMessage()
    {
        $sessionId = $this->getRequest()->getParam('session_id');
        $session   = $this->getService('TcSession')->getOne(
            $this->getService('TcSession')->find($sessionId)
        );
        
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $currntState = $this->getService('State')->fetchAll(array(
            'item_id = ?' => $session->session_id,
            'process_type = ?' => 10
        ))->current();
        return ($currentUserId == $session->responsible_id) && ($currntState->current_state == 'HM_Tc_Session_State_Analysis');
    }
}