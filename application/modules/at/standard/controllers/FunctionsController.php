<?php
class Standard_FunctionsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $positionsCache = array();
    protected $_function = array();
    protected $_standard = array();

    public function init()
    {
        $form = new HM_Form_Functions();
        $this->_setForm($form);
        
        if ($functionId = $this->_getParam('function_id')) {
            $this->_function = $this->getOne($this->getService('AtStandardFunction')->find($functionId));        
        }
        if ($standardId = $this->_getParam('standard_id')) {
            $this->_standard = $this->getOne($this->getService('AtStandard')->find($standardId));        
        }

        $this->view->setHeader(_('Обобщённые трудовые функции'));
        $this->view->setSubHeader($this->_standard->name);

        parent::init();
    }

    public function indexAction()
    {
        $gridId = 'grid_'.$this->_standard->standard_id;

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }
        
        $select = $this->getService('AtStandardFunction')->getSelect();

        $select->from(
            array(
                'p' => 'at_ps_function'
            ),
            array(
                'function_id',
                'standard_id',
                'name',
                'count_requirements_education' => new Zend_Db_Expr('SUM(CASE WHEN r.type='.HM_At_Standard_Function_FunctionModel::TYPE_EDUCATION.' then 1 ELSE 0 END)'),
                'count_requirements_working' => new Zend_Db_Expr('SUM(CASE WHEN r.type='.HM_At_Standard_Function_FunctionModel::TYPE_WORKING.' then 1 ELSE 0 END)'),
                'count_requirements_special' => new Zend_Db_Expr('SUM(CASE WHEN r.type='.HM_At_Standard_Function_FunctionModel::TYPE_SPECIAL.' then 1 ELSE 0 END)'),
                'count_sgc_requirements_education' => new Zend_Db_Expr('SUM(CASE WHEN r.type='.HM_At_Standard_Function_FunctionModel::TYPE_SGC_EDUCATION.' then 1 ELSE 0 END)'),
                'count_sgc_requirements_working' => new Zend_Db_Expr('SUM(CASE WHEN r.type='.HM_At_Standard_Function_FunctionModel::TYPE_SGC_WORKING.' then 1 ELSE 0 END)'),
            )
        );

        $select
            ->joinLeft(array('r' => 'at_ps_requirement'), 'r.function_id = p.function_id', array())
            ->where('p.standard_id = ?', $this->_standard->standard_id)
            ->group(array(
                'p.function_id',
                'p.standard_id',
                'p.name',
            ));

        $grid = $this->getGrid($select, array(
            'function_id' => array('hidden' => true),
            'standard_id' => array('hidden' => true),
            'name'=> array('title' => _('Название')),
            'count_requirements_education'=> array('title' => _('Требования к образованию и обучению')),
            'count_requirements_working'=> array('title' => _('Требования к опыту работы')),
            'count_requirements_special'=> array('title' => _('Особые требования')),
            'count_sgc_requirements_education'=> array('title' => _('Требования компании к образовательному уровню')),
            'count_sgc_requirements_working'=> array('title' => _('Требования компании к опыту работы')),
            ),
            array(
                'name' => null,
            ),
            $gridId
        );

        $grid->addAction(array(
            'module' => 'standard',
            'controller' => 'functions',
            'action' => 'edit'
        ),
            array('function_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'standard',
            'controller' => 'functions',
            'action' => 'delete'
        ),
            array('function_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if(!$result){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_refreshRequirements($form->getValues(), $result->function_id);
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            }
        }
        $this->view->form = $form;


    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $data = $form->getValues();
                $this->_refreshRequirements($data, $data['function_id']);
                $this->update($form);
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $defaults = array();
            list($currentRequirementIds, $currentRequirements) = $this->_loadRequirements($this->_function->function_id);
            foreach (HM_At_Standard_Function_FunctionModel::getTypes() as $typeId => $title) {
                if (isset($currentRequirements[$typeId])) {
                    foreach ($currentRequirements[$typeId] as $requirementId => $requirement) {
                        $defaults['requirements_' . $typeId][$requirementId] = array('requirement' => $requirement);
                    }
                }
            }
            $form->setDefaults($defaults);
            $form->populate($this->_function->getData());

        }

        $this->view->form = $form;
    }



    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'functions', 'standard', array('standard_id' => $this->_standard->standard_id));
    }

    public function create($form)
    {
        $values = $form->getValues();
        $values['standard_id'] = $this->_standard->standard_id;
        unset($values['function_id']);

        return $this->getService('AtStandardFunction')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        return $this->getService('AtStandardFunction')->update($values);
    }

    public function deleteAction()
    {
        $id = $this->_getParam('function_id');
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }

    public function delete($id) {
        $this->getService('AtProfileFunction')->deleteBy(array('function_id = ?' => $id));
        $this->getService('AtStandardRequirement')->deleteBy(array('function_id = ?' => $id));
        $this->getService('AtStandardFunction')->delete($id);
    }

    public function _loadRequirements($function_id)
    {
        $currentRequirementIds = $currentRequirements = array();
        $collection = $this->getService('AtStandardRequirement')->fetchAll(array('function_id = ?' => $function_id));
        if (count($collection)) {
            $currentRequirementIds = $collection->getList('requirement_id', 'type');
            foreach ($collection as $currentRequirement) {
                if (!isset($currentRequirements[$currentRequirement->type])) $currentRequirements[$currentRequirement->type] = array();
                $currentRequirements[$currentRequirement->type][$currentRequirement->requirement_id] = $currentRequirement->name;
            }    
        }
        return array($currentRequirementIds, $currentRequirements);
    }



    public function _refreshRequirements($data, $function_id)
    {
        list($currentRequirementIds, $currentRequirements) = $this->_loadRequirements($function_id);

        foreach (HM_At_Standard_Function_FunctionModel::getTypes() as $typeId => $value) {
            if (isset($data['requirements_' . $typeId])) {
                foreach ($data['requirements_' . $typeId] as $requirementId => $dataRequirement) {
                    if ($requirementId != HM_Form_Element_MultiSet::ITEMS_NEW) {
                        $requirement = array(
                            'requirement_id' => $requirementId,
                            'function_id' => $function_id,
                            'type' => $typeId,           
                            'name' => $dataRequirement['requirement'],           
                        );
                        $this->getService('AtStandardRequirement')->update($requirement);
                        unset($currentRequirementIds[$requirementId]);                                
                    } else {
                        foreach ($dataRequirement['requirement'] as $value) {
                            if (!strlen(trim($value))) continue;
                            $requirement = array(
                                'function_id' => $function_id,
                                'type' => $typeId,           
                                'name' => $value,   
                            );                    
                            $this->getService('AtStandardRequirement')->insert($requirement);
                        }
                    }
                }
            }
        }
        if (is_array($currentRequirementIds) && count($currentRequirementIds)) {
            $this->getService('AtStandardRequirement')->deleteBy(array('requirement_id IN (?)' => array_keys($currentRequirementIds)));
        }                
    }

}
