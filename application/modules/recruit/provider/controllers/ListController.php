<?php
class Provider_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_Provider;
        $this->_setForm($form);
        parent::init();
    }
    
    public function indexAction(){
        
        $sorting = $this->_request->getParam("ordergrid");
        
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }        
        
        if (!$this->isGridAjaxRequest() && $this->_request->getParam('statusgrid') == "") {
             $this->_request->setParam('statusgrid', HM_Recruit_Provider_ProviderModel::STATUS_ACTUAL);
        }
        
        $select = $this->getService('RecruitProvider')->getSelect('list', 'index');
        
        $columnsOptions = array(
            'provider_id' => array('hidden' => true),
            'locked' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
            ),
            'status' => array(
                'title'    => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params'   => array('{{status}}')
                )
            ),
        );

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            array(
                'name'   => null,
                'status' => array(
                    'values'     => HM_Recruit_Provider_ProviderModel::getStatuses(),
                    'searchType' => '='
                ),
            )
        );
        
        $grid->addAction(array(
            'module'     => 'provider',
            'controller' => 'list',
            'action'     => 'edit',
        ),
            array('provider_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module'     => 'provider',
            'controller' => 'list',
            'action'     => 'delete',
        ),
            array('provider_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'delete-by',
            ), 
            _('Удалить'), 
            _('Вы уверены, что хотите удалить провайдера? (провайдер не будет физически удален из базы, а лишь сменит свой статус)')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{locked}}')
            )
        );
        
        $this->view->grid          = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }
    
    public function create(\Zend_Form $form) {
        $this->getService('RecruitProvider')->insert($form->getValues());
    }

    public function update(\Zend_Form $form) {
        $providerId = $this->_getParam('provider_id', 0);
        $values = $form->getValues();
        $values['provider_id'] = $providerId;
        $this->getService('RecruitProvider')->update($values);
    }


    public function setDefaults(\Zend_Form $form) {
        $providerId = $this->_getParam('provider_id', 0);
        $provider = $this->getService('RecruitProvider')->find($providerId)->current();
        $data = $provider->getData();
        $form->populate($data);
    }
    
    public function delete($id) {
        $this->getService('RecruitProvider')->updateWhere(array(
            'status' => HM_Recruit_Provider_ProviderModel::STATUS_NOT_ACTUAL,
        ),
            array(
                'provider_id = ?' => $id,
                'locked = ?' => HM_Recruit_Provider_ProviderModel::LOCKED_NOT_LOCKED,
            )
        );
    }   

    public function updateStatus($status) {
        return HM_Recruit_Provider_ProviderModel::getStatus($status);
    }

    public function updateActions($locked, $actions ) {

        if ($locked == HM_Recruit_Provider_ProviderModel::LOCKED_LOCKED) {
            $this->unsetAction($actions, array('module' => 'provider', 'controller' => 'list', 'action' => 'edit', 'baseUrl' => 'recruit'));
        }

        if ($locked == HM_Recruit_Provider_ProviderModel::LOCKED_LOCKED) {
            $this->unsetAction($actions, array('module' => 'provider', 'controller' => 'list', 'action' => 'delete', 'baseUrl' => 'recruit'));
        }
        return $actions;
    }
}
