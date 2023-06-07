<?php
class SubjectCosts_ActualCostsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_defaultService;

    protected $_sessionQuarterId;
    protected $_sessionQuarter;

    public function init()
    {
        $form = new HM_Form_SubjectActualCosts;
        $this->_setForm($form);
        if ($this->getRequest()->getActionName() == 'edit') {
            $element = $form->getElement('cycle_id');
            $element->readonly = $element->disabled = true;
        }

        $this->_defaultService = $this->getService('TcSubjectActualCosts');

        $this->_sessionQuarterId = $this->_getParam('session_quarter_id', 0);
        $this->_sessionQuarter   = $this->getOne(
            $this->getService('TcSessionQuarter')->fetchAll(
            //array('Cycle', 'Department'),
                $this->quoteInto('session_quarter_id = ?', $this->_sessionQuarterId))
        );

        if ($this->_sessionQuarterId) {
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
    
    public function indexAction(){
        
        $sorting = $this->_request->getParam("ordergrid");
        
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }        
        
        if (!$this->isGridAjaxRequest() && $this->_request->getParam('statusgrid') == "") {
             $this->_request->setParam('statusgrid', 'actual');
        }
        
        $select = $this->getService('TcSubjectActualCosts')->getActualCostsIndexSelect();
        
        $sessionQuarterId = $this->getRequest()->getParam('session_quarter_id', 0);
        if ($sessionQuarterId) {
            $sessionQuarter = $this->getService('TcSessionQuarter')->getOne(
                $this->getService('TcSessionQuarter')->find($sessionQuarterId)
            );
            $cycle = $this->getService('Cycle')->getOne(
                $this->getService('Cycle')->find($sessionQuarter->cycle_id)
            );
            $select->where('ac.cycle_id = ?', $cycle->cycle_id);
        }
        $columnsOptions = array(
            'actual_cost_id'    => array('hidden' => true),
            'cycle_name' => array('title' => _('Квартал')),
            'provider_name'     => array(
                'title' => _('Провайдер'),
                'position'     => 1,
            ),
            'subject_id'        => array(
                'title' => _('Курс'),
                'callback' => array(
                    'function' => array($this, 'updateSubjects'),
                    'params'   => array('{{subject_id}}')
                ),
                'position'     => 2,
            ),
            'document_number'   => array('title' => _('№ платежного документа')),
            'pay_date_document' => array(
                'title'  => _('Дата платежного документа'),
                'format' => 'Date',
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params'   => array('{{pay_date_document}}')
                )
            ),
            'pay_date_actual'   => array(
                'title'  => _('Дата фактического платежа'),
                'format' => 'Date',
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params'   => array('{{pay_date_actual}}')
                )
            ),
            'pay_amount'        => array(
                'title' => _('Сумма, руб'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{pay_amount}}')
                )
            ),
        );

        if ($this->_sessionQuarterId != 0) {
            $columnsOptions['cycle_name'] = array('hidden' => true);
        }

        $columnFilters = array(
            'cycle_name'        => array('values' => $this->_getTcQuartersNames()),
            'provider_name'     => array('values' => $this->_getTcProvidersNames()),
            'subject_id'        => array('values' => $this->_getSubjectsNames()),
            'document_number'   => null,
            'pay_date_document' => array('render' => 'DateSmart'),
            'pay_date_actual'   => array('render' => 'DateSmart'),
            'pay_amount'        => null,
        );

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            $columnFilters
        );
        
        $grid->addAction(array(
            'module'     => 'subject-costs',
            'controller' => 'actual-costs',
            'action'     => 'edit',
        ),
            array('actual_cost_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module'     => 'subject-costs',
            'controller' => 'actual-costs',
            'action'     => 'delete',
        ),
            array('actual_cost_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );
        
        $grid->addMassAction(
            array(
                'module'     => 'subject-costs',
                'controller' => 'actual-costs',
                'action'     => 'delete-by',
            ), 
            _('Удалить'), 
            _('Вы уверены, что хотите удалить запись?')
        );

        $this->view->grid          = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function editAction() {
        $actualCostId = $this->_getParam('actual_cost_id', 0);
        $ac = $this->getService('TcSubjectActualCosts')->getOne(
            $this->getService('TcSubjectActualCosts')->find($actualCostId)
        );
        $sessionQuarterId = $this->_getParam('session_quarter_id', 0);
        $this->_setParam('cycle_id', $ac->cycle_id);
        $this->_setParam('pay_date_document', date('d.m.Y', strtotime($this->_getParam('pay_date_document'))));
        $this->_setParam('pay_date_actual', date('d.m.Y', strtotime($this->_getParam('pay_date_actual'))));
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action_Crud::ACTION_UPDATE));
                if ($sessionQuarterId) {
                    $this->_redirector->gotoUrl($this->view->url(array('module' => 'subject-costs', 'controller' => 'actual-costs', 'action' => 'index', 'baseUrl' => '', 'session_quarter_id' => $sessionQuarterId)));
                } else {
                    $this->_redirectToIndex();
                }

            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    private function _getSubjectsNames()
    {
        $names = array();

        $collection = $this->getService('Subject')->fetchAll();
        foreach ($collection as $item) {
            $names[] = $item->name;
        }

        return $names;
    }

    private function _getTcProvidersNames()
    {
        $names = array();

        $collection = $this->getService('TcProvider')->fetchAll();
        foreach ($collection as $item) {
            $names[] = $item->name;
        }

        return $names;
    }

    private function _getTcQuartersNames()
    {
        $names = array();

        $collection = $this->getService('Cycle')->fetchAll(
            array(
                'quarter != ?' => HM_Tc_SessionQuarter_SessionQuarterModel::WHOLE_YEAR,
                'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING
            )
        );
        foreach ($collection as $item) {
            $names[] = $item->name;
        }

        return $names;
    }

    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        $sessionQuarterId = $this->_getParam('session_quarter_id', 0);
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action_Crud::ACTION_INSERT));
                }

                if ($sessionQuarterId) {
                    $this->_redirector->gotoUrl($this->view->url(array('module' => 'subject-costs', 'controller' => 'actual-costs', 'action' => 'index', 'baseUrl' => '', 'session_quarter_id' => $sessionQuarterId)));
                } else {
                    $this->_redirectToIndex();
                }
            }
        }
        $this->view->form = $form;
    }

    public function create(\Zend_Form $form) {
        $values = $form->getValues();
        $subjectId = $values['subject_id'];
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        $values['provider_id'] = $subject->provider_id;
        $values['pay_date_document'] = date('Y-m-d', strtotime($values['pay_date_document']));
        $values['pay_date_actual']   = date('Y-m-d', strtotime($values['pay_date_actual']));
        $this->getService('TcSubjectActualCosts')->insert($values);
    }

    public function update(\Zend_Form $form) {
        $actualCostId = $this->_getParam('actual_cost_id', 0);
        $values = $form->getValues();
        $subjectId = $values['subject_id'];
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        $values['provider_id'] = $subject->provider_id;
        $values['actual_cost_id'] = $actualCostId;
        $values['pay_date_document'] = date('Y-m-d', strtotime($values['pay_date_document']));
        $values['pay_date_actual']   = date('Y-m-d', strtotime($values['pay_date_actual']));
        $this->getService('TcSubjectActualCosts')->update($values);
    }
    
    public function setDefaults(\Zend_Form $form) {
        $actualCostId = $this->_getParam('actual_cost_id', 0);
        $actualCost = $this->getService('TcSubjectActualCosts')->find($actualCostId)->current();
        $data = $actualCost->getData();
        $data['period'] = $data['month'] . '_' . $data['year'];
        unset($data['month']);
        unset($data['year']);
        $form->populate($data);
    }
    
    public function delete($id) {
        $this->getService('TcSubjectActualCosts')->delete($id);
    }
    
    public function updateFloatField($value) {
        return round($value, 2);
    }

    public function updateSubjects($subid) {
        $subject = $this->getService('Subject')->getOne(
            $this->getService('Subject')->find($subid)
        );

        $url = $this->view->url(array(
            'baseUrl'    => '',
            'module'     => 'subject',
            'controller' => 'index',
            'action'     => 'card',
            'subject_id' => $subid
        ), null, true);

        return '<a href="'. $url . '">'.$subject->name.'</a>';
    }

    public function updateDate($date)
    {
        return $date != '01.01.1970' ? $date : 'Не указано';
    }
}
