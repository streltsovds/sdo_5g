<?php
/**
 * Created by PhpStorm.
 * User: cuthuk
 * Date: 13.10.2014
 * Time: 7:30
 */


class Application_ImpersonalController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    /** @var HM_Tc_ApplicationImpersonal_ApplicationImpersonalService $_defaultService */
    protected $_defaultService;

    protected $_session;
    protected $_department;
    protected $_sessionDepartment;

    public function init()
    {
        $this->_defaultService = $this->getService('TcApplicationImpersonal');

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
        parent::init();
    }

    public function indexAction()
    {
        $currentUserRole = $this->getService('User')->getCurrentUserRole();
        $options = array(
            'sessionId' => $this->_session->session_id,
        );
        
        if ($this->getService('Acl')->inheritsRole($currentUserRole, array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $position = $this->getService('Orgstructure')->fetchAll(array('mid =?' => $this->_user->MID))->current();
            $parent = $this->getService('Orgstructure')->fetchAll(array('soid =?' => $position->owner_soid))->current();
            $options['departmentId'] = array($parent->soid, $parent->owner_soid);
            $options['departmentId'] = $options['departmentId'] + $this->getService('Orgstructure')->getDescendants(
                    $parent->soid,
                    false, 
                    HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
        }

        $listSource = $this->_defaultService->getClaimantListSource($options);
        $grid = HM_Application_Grid_ApplicationImpersonalGrid::create(
            array_merge(array('controller' => $this), $options)
        );


        $this->view->assign(array(
            'isGridAjaxRequest' => $this->isGridAjaxRequest(),
            'session'           => $this->_session,
            'grid'              => $grid->init($listSource),
        ));
    }

    public function deleteAction()
    {
        $applicationImpersoalId = $this->_getParam('application_impersonal_id', 0);
        if ($applicationImpersoalId) {
            $result = $this->_defaultService->delete($applicationImpersoalId);
        }
        if ($result) {
            $this->_flashMessenger->addMessage(_('Обезличенная заявка успешно удалена'));
        } else {
            $this->_flashMessenger->addMessage(_('При удалении обезличенной заявки произошли ошибки'));
        }
        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $grid = $this->_request->getParam('grid');
        $applicationImpersoalIds = $this->_request->getParam('postMassIds_' . $grid);
        $applicationImpersoalIds = explode(',', $applicationImpersoalIds);

        if (is_array($applicationImpersoalIds) && count($applicationImpersoalIds)) {
            $count = 0;
            foreach ($applicationImpersoalIds as $applicationId) {
                $result = $this->_defaultService->delete($applicationId);
                $count = ($result) ? $count + 1 : $count;
            }
        }
        $this->_flashMessenger->addMessage(sprintf(
            _('Удалено - %d, невозможно удалить - %d'),
            $count, count($applicationImpersoalIds) - $count)
        );
        $this->_redirectToIndex();
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function createAction()
    {
        $this->view->setHeader(_('Создание обезличенной заявки'));

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_ApplicationImpersonal();

        // только месяцы, относящиеся к периоду квартального планирования
        $form->getElement('period')->setMultiOptions($tcApplicationService->getSessionPeriodsForForm($this->_session));
        // все статьи
        $form->getElement('cost_item')->setMultiOptions(
            array(0 => '') +
            HM_Tc_Application_ApplicationModel::getCostItems()
        );
        // все курсы, сгруппированные по провайдерам
        $form->getElement('subject_id')->setMultiOptions($tcApplicationService->getSubjectsForForm());

        $post = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($post)) {

                // стоимость - либо из курса, либо из формы, если это мероприятие
                if ($post['subject_id']) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                    $price = $subject->price;
                } else {
                    $price = $post['price'];
                }

                // для единообразия определяем $sessionDepartmentId (консолидированная заявка)
                // хотя непосредственно в конс.заявке не отображается
                $department = $post['department'];
                $sessionDepartmentId = 0;
                $sessionDepartments = $this->_session->departments->getList('department_id', 'session_department_id');
                foreach ($sessionDepartments as $soid => $sessionDepartmentId) {
                    if ($this->getService('Orgstructure')->isGrandOwner($post['department'], $soid)) {
                        break;
                    }
                }

                $insert = array(
                    'session_id' => $this->_session->session_id,
                    'department_id' => $department,
                    'session_department_id' => $sessionDepartmentId ? : null,
                    'criterion_id' => $subject ? $subject->criterion_id : null,
                    'criterion_type' => $subject ? $subject->criterion_type : null,
                    'subject_id' => $subject ? $subject->subid : null,
                    'provider_id' => $subject ? $subject->provider_id : null,
                    'period' => $post['period'],
                    'category' => $subject ? $subject->category : $post['category'],
                    'created' => date('Y-m-d'),
                    'status' => HM_Tc_Application_ApplicationModel::STATUS_COMPLETE,
                    'event_name' => $post['event_name'],
                    'price' => $price,
                    'quantity' => (int)$post['empty_positions'],
                    'cost_item' => $post['cost_item'],
                );

                $this->getService('TcApplicationImpersonal')->insert($insert);

                $this->_flashMessenger->addMessage(_('Заявка успешно создана'));
                $this->_redirect($this->view->url(
                    array(
                        'baseUrl' => false,
                        'module' => 'application',
                        'controller' => 'impersonal',
                        'action' => 'index',
                        'session_id' => $this->_session->session_id,
                    )));

            }
        }

        $this->view->form = $form;
    }

    public function editAction()
    {
        $this->view->setHeader(_('Редактирование заявки'));

        $applicationId = $this->_getParam('application_impersonal_id');
        $application   = $this->getService('TcApplicationImpersonal')->getOne(
            $this->getService('TcApplicationImpersonal')->find($applicationId)
        );

        $sessionId = $this->getRequest()->getParam('session_id');
        $session   = $this->getService('TcSession')->getOne(
            $this->getService('TcSession')->find($sessionId)
        );
        $cycle     = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($session->cycle_id)
        );

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_ApplicationImpersonal();

        // только месяцы, относящиеся к периоду квартального планирования
        $form->getElement('period')->setMultiOptions($tcApplicationService->getSessionPeriodsForForm($this->_session, $application));
        $form->getElement('cost_item')->setMultiOptions(
            array(0 => '') +
            HM_Tc_Application_ApplicationModel::getCostItems()
        );
        // все курсы, сгруппированные по провайдерам
        $form->getElement('subject_id')->setMultiOptions($tcApplicationService->getSubjectsForForm());

        $form->addElement('hidden', 'application_id', array('value' => $application->application_id));

        $form->getElement('category')->setAttribs(array(
            'onchange' => "getInitialCoursesList();if(this.value == 1) { $('fieldset#fieldset-typegroup').hide(); addPeriodOptions(true,".$cycle->year.",".$sessionId.",".$applicationId.");} else { $('fieldset#fieldset-typegroup').show(); addPeriodOptions(false,".$cycle->year.",".$sessionId.",".$applicationId."); }",
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getParams();

            if ($form->isValid($post)) {

                if ($post['subject_id']) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                    $price = $subject->price;
                } else {
                    $price = $post['price'];
                }

                $department = $post['department'];
                $sessionDepartmentId = 0;
                $sessionDepartments = $this->_session->departments->getList('department_id', 'session_department_id');
                foreach ($sessionDepartments as $soid => $sessionDepartmentId) {
                    if ($this->getService('Orgstructure')->isGrandOwner($post['department'], $soid)) {
                        break;
                    }
                }

                $update = array_merge($application->getData(), array(
                    'department_id' => $department,
                    'session_department_id' => $sessionDepartmentId ? : null,
                    'criterion_id' => $subject ? $subject->criterion_id : null,
                    'criterion_type' => $subject ? $subject->criterion_type : null,
                    'subject_id' => $subject ? $subject->subid : null,
                    'provider_id' => $subject ? $subject->provider_id : null,
                    'period' => $post['period'],
                    'category' => $post['category'], //$subject ? HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION : null,
                    'event_name' => $post['event_name'],
                    'price' => $price,
                    'quantity' => (int)$post['empty_positions'],
                    'cost_item' => $post['cost_item'],
                ));

                $this->getService('TcApplicationImpersonal')->update($update, false);

                $this->_flashMessenger->addMessage(_('Заявка успешно отредактирована'));
                $this->_redirect($this->view->url(
                    array(
                        'baseUrl' => false,
                        'module' => 'application',
                        'controller' => 'impersonal',
                        'action' => 'index',
                        'session_id' => $this->_session->session_id,
                    )));
            }

        } else {
            $data = array(
                'period'            => $application->period,
                'cost_item'         => $application->cost_item,
                'event_name'        => $application->event_name,
                'price'             => $application->price,
                'subject_id'        => $application->subject_id,
                'empty_positions'   => $application->quantity,
                'department'        => $application->department_id,
                'category'          => $application->category,
            );

            $form->populate($data);

            $element = $form->getElement('department');
            $positionIdJQueryParams = $element->getAttrib('jQueryParams');

            if ($collection = $this->getService('Orgstructure')->find($application->department_id)) {
                $department = $collection->current();
                $positionIdJQueryParams['selected'] = $department->soid;
                $positionIdJQueryParams['itemId'] = $department->owner_soid;
                $positionIdJQueryParams['ignoreDefaultSelectedValue'] = true;

                $element->setAttrib('jQueryParams', $positionIdJQueryParams);
            }
        }
        $this->view->form = $form;
        $this->view->costItem = $application->cost_item;
    }



    public function monthDate($date, $checkSession = true)
    {
        $tst = strtotime($date);
        if (!$date || !$tst || (date('Y-m-d', $tst) == '1900-01-01')) {
            return '';
        }
        if (($checkSession && $date<$this->_session->date_begin)) {
            return '';
        }

        return month_name((int) date('m', $tst)) . " " . date('Y', $tst);
    }

    protected function _redirectToIndex()
    {
        $url = array(
            'action'     => 'index',
            'controller' => 'impersonal',
            'module'     => 'application',
            'baseUrl'    => '',
            'session_id' => $this->_session->session_id
        );
        $this->_redirector->gotoUrl($this->view->url($url));
    }
}
