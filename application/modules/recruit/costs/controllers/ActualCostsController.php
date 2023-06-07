<?php
class Costs_ActualCostsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_ActualCosts;
        $this->_setForm($form);
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
        
        $select = $this->getService('RecruitActualCosts')->getActualCostsIndexSelect();
        
        $columnsOptions = array(
            'actual_cost_id'    => array('hidden' => true),
            'period'            => array(
                'title' => _('Период'),
                'callback' => array(
                    'function' => array($this, 'updatePeriod'),
                    'params'   => array('{{period}}')
                )
            ),
            'provider_name'     => array('title' => _('Провайдер')),
            'document_number'   => array('title' => _('№ платежного документа')),
            'pay_date_document' => array(
                'title'  => _('Дата оплаты по платежному документу'),
                'format' => array(
                    'date',
                    array('date_format' => Zend_Locale_Format::getDateFormat())
                ),
            ),
            'pay_date_actual'   => array(
                'title'  => _('Дата оплаты по факту'),
                'format' => array(
                    'date',
                    array('date_format' => Zend_Locale_Format::getDateFormat())
                ),
            ),
            'pay_amount'        => array(
                'title' => _('Сумма к оплате, руб'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{pay_amount}}')
                )
            ),
            'payment_type'      => array(
                'title' => _('Тип платежа'),
                'callback' => array(
                    'function' => array($this, 'updateType'),
                    'params'   => array('{{payment_type}}')
                )
            ),
        );

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            array(
                'period' => array('render' => 'Period'),
                'provider_name'     => null,
                'document_number'   => null,
                'pay_date_document' => array('render' => 'DateSmart'),
                'pay_date_actual'   => array('render' => 'DateSmart'),
                'pay_amount'        => null,
                'payment_type'      => array(
                    'values'     => HM_Recruit_ActualCosts_ActualCostsModel::getPaymentTypes(),
                    'searchType' => '=',
                ) 
                
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_RecruiterModel::ROLE_HR_LOCAL
        ))) {
            $grid->addAction(array(
                'module'     => 'costs',
                'controller' => 'actual-costs',
                'action'     => 'edit',
            ),
                array('actual_cost_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module'     => 'costs',
                'controller' => 'actual-costs',
                'action'     => 'delete',
            ),
                array('actual_cost_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array(
                    'module'     => 'costs',
                    'controller' => 'actual-costs',
                    'action'     => 'delete-by',
                ),
                _('Удалить'),
                _('Вы уверены, что хотите удалить запись?')
            );
        }
        
        $this->view->grid          = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
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


    protected function _prepareDate($values)
    {
        if (isset($values['pay_date_document']) && $values['pay_date_document']) {
            $date = new DateTime($values['pay_date_document']);
            $values['pay_date_document'] = $date->format('Ymd 00:00:00');
        } else {
            $values['pay_date_document'] = null;
        }

        if (isset($values['pay_date_actual']) && $values['pay_date_actual']) {
            $date = new DateTime($values['pay_date_actual']);
            $values['pay_date_actual'] = $date->format('Ymd 00:00:00');
        } else {
            $values['pay_date_actual'] = null;
        }

        return $values;
    }


    public function create(\Zend_Form $form) {
        $values = $form->getValues();
        $values = $this->_preparePeriod($values);
        $values = $this->_prepareDate($values);

        $this->getService('RecruitActualCosts')->insert($values);
    }

    public function update(\Zend_Form $form) {
        $actualCostId = $this->_getParam('actual_cost_id', 0);
        $values = $form->getValues();
        $values['actual_cost_id'] = $actualCostId;
        $values = $this->_preparePeriod($values);
        $values = $this->_prepareDate($values);

        $this->getService('RecruitActualCosts')->update($values);
    }
    
    public function setDefaults(\Zend_Form $form) {
        $actualCostId = $this->_getParam('actual_cost_id', 0);
        $actualCost = $this->getService('RecruitActualCosts')->find($actualCostId)->current();
        $data = $actualCost->getData();
        $data['period'] = $data['month'] . '_' . $data['year'];
        unset($data['month']);
        unset($data['year']);
        $form->populate($data);
    }
    
    public function delete($id) {
        $this->getService('RecruitActualCosts')->delete($id);
    }   

    public function updatePeriod($period) {
        $monthYear = explode('_', $period);
        $months = HM_Recruit_ActualCosts_ActualCostsModel::getMonths();
        return $months[intval($monthYear[1])] . ' ' . $monthYear[0];
    }

    public function updateType($type) {
        return HM_Recruit_ActualCosts_ActualCostsModel::getPaymentType($type);
    }
    
    public function updateFloatField($value) {
        return round($value, 2);
    }
    
}
