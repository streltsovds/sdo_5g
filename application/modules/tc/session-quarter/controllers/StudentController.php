<?php

class SessionQuarter_StudentController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;



    protected $_defaultService;

    public $_sessionQuarter = null;
    public $_sessionQuarterId = 0;


    public function init()
    {
        /** @var HM_Tc_SessionQuarter_SessionQuarterService $this->_defaultService */
        $this->_defaultService = $this->getService('TcSessionQuarter');

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
        $view = $this->view;

        $grid = HM_SessionQuarter_Grid_StudentsGrid::create(array(
            'controller' => $this,
        ));

        $options= array(
            'sessionQuarterId'   => $this->_sessionQuarterId
        );

        /** @var HM_Tc_SessionQuarter_SessionQuarterService $this->_defaultService */
        $listSource = $this->_defaultService->getStudentsListSource($options);

        $view->assign(array(
            'grid'          => $grid->init($listSource),
            'gridAjaxRequest' => $this->isGridAjaxRequest()
        ));
    }
}