<?php
class Costs_PlannedCostsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_PlannedCosts;
        $this->_setForm($form);
        parent::init();
    }
    
    public function indexAction(){
        
        $sorting = $this->_request->getParam("ordergrid");
        
//        if ($sorting == ""){
//            $this->_request->setParam("ordergrid", 'name_ASC');
//        }
        
        $select = $this->getService('PlannedCosts')->getPlannedCostsIndexSelect();
//        exit($select->__toString());
        $columnsOptions = array(
            'planned_cost_id' => array('hidden' => true),
            'period'          => array(
                'title' => _('Период'),
                'callback' => array(
                    'function' => array($this, 'updatePeriod'),
                    'params'   => array('{{period}}')
                )
            ),
            'provider_name'   => array('title' => _('Провайдер')),
            
            'base_sum'        => array(
                'title' => _('Базовая сумма плановых затрат, руб'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{base_sum}}')
                )
            ),
            'corrected_sum'   => array(
                'title' => _('Скорректированная сумма плановых затрат, руб'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{corrected_sum}}')
                )
            ),
            'status'          => array(
                'title'    => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params'   => array('{{status}}')
                )
            ),
            'year' => array('hidden' => true),
            'month' => array('hidden' => true)
        );

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            array(
                'period' => array('render' => 'Period'),
                'provider_name' => null,
                'base_sum'      => null,
                'corrected_sum' => null,
                'status'        => array(
                    'values'     => HM_Recruit_PlannedCosts_PlannedCostsModel::getStatuses(),
                    'searchType' => '='
                ),
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_RecruiterModel::ROLE_HR_LOCAL
        ))) {
            $grid->addAction(array(
                'module'     => 'costs',
                'controller' => 'planned-costs',
                'action'     => 'edit',
            ),
                array('planned_cost_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module'     => 'costs',
                'controller' => 'planned-costs',
                'action'     => 'delete',
            ),
                array('planned_cost_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array(
                    'module'     => 'costs',
                    'controller' => 'planned-costs',
                    'action'     => 'delete-by',
                ),
                _('Удалить'),
                _('Вы уверены, что хотите удалить запись?')
            );

            $grid->addMassAction(
                array(
                    'module'     => 'costs',
                    'controller' => 'planned-costs',
                    'action'     => 'change-status',
                    'status'     => HM_Recruit_PlannedCosts_PlannedCostsModel::STATUS_ACCEPTED,
                ),
                _('Установить статус: "принят"'),
                _('Вы уверены, что хотите изменить статус?')
            );
        }
        
        $this->view->grid          = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }
    
    public function changeStatusAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $status      = $this->_getParam('status', '');
        
        $statuses = HM_Recruit_PlannedCosts_PlannedCostsModel::getStatuses();
        
        if (strlen($postMassIds) && array_key_exists($status, $statuses)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $values = array(
                        'planned_cost_id' => $id,
                        'status'          => $status,
                    );
                    $this->getService('PlannedCosts')->update($values);
                }
                $this->_flashMessenger->addMessage('Статус успешно установлен!');
            }
        }
        $this->_redirectToIndex();
    }
    
    private function _preparePeriod($values){
        if($values['period']){
            $period = explode('_', $values['period']);
            $values['month'] = $period[0];
            $values['year']   = $period[1];
        }
        unset($values['period']);
        return $values;
    }
    
    public function create(\Zend_Form $form) {
        $values = $form->getValues();
        $values = $this->_preparePeriod($values);
        $this->getService('PlannedCosts')->insert($values);
    }

    public function update(\Zend_Form $form) {
        $plannedCostId = $this->_getParam('planned_cost_id', 0);
        $plannedCost = $this->getService('PlannedCosts')->find($plannedCostId)->current();
        $values = $form->getValues();
        $values['planned_cost_id'] = $plannedCostId;
        $values = $this->_preparePeriod($values);
        if($plannedCost->status == HM_Recruit_PlannedCosts_PlannedCostsModel::STATUS_ACCEPTED){
            unset($values['base_sum']);
            unset($values['month']);
            unset($values['year']);
            unset($values['provider_id']);
        }
        $this->getService('PlannedCosts')->update($values);
    }
    
    public function setDefaults(\Zend_Form $form) {
        $plannedCostId = $this->_getParam('planned_cost_id', 0);
        $plannedCost = $this->getService('PlannedCosts')->find($plannedCostId)->current();
        $data = $plannedCost->getData();
        $data['period'] = $data['month'] . '_' . $data['year'];
        unset($data['month']);
        unset($data['year']);
        $form->populate($data);
    }
    
    public function delete($id) {
        $this->getService('PlannedCosts')->delete($id);
    }   

    public function updatePeriod($period) {
        $monthYear = explode('_', $period);
        $months = HM_Recruit_PlannedCosts_PlannedCostsModel::getMonths();
        return $months[intval($monthYear[1])] . ' ' . $monthYear[0];
    }
    
    public function updateStatus($status) {
        return HM_Recruit_PlannedCosts_PlannedCostsModel::getStatus($status);
    }
    
    public function updateFloatField($value) {
        return round($value, 2);
    }
    
}
