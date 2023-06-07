<?php
class HM_Form_PlannedCosts extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
      
        $this->setName('plannedCosts');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array(
                    'module'     => 'costs',
                    'controller' => 'planned-costs',
                    'action'     => 'index',
                ), null, true)
            )
        );  
        
        $plannedCostId = $this->getParam('planned_cost_id', 0);
        $plannedCost = $this->getService('PlannedCosts')->find($plannedCostId)->current();
        
        $isAccepted = false;
        if($plannedCost->planned_cost_id){
            $isAccepted = ($plannedCost->status == HM_Recruit_PlannedCosts_PlannedCostsModel::STATUS_ACCEPTED);
        }
        
        
        $this->addElement($this->getDefaultSelectElementName(), 'period',
            array(
                'Label' => _('Период'),
                'Required' => $isAccepted ? false : true,
                'disabled' => $isAccepted ? true : null,
                'multiOptions' => HM_Recruit_PlannedCosts_PlannedCostsModel::getPeriods(),
                'Filters' => array('StripTags'),
            )
        );
        
        $providerService = $this->getService('RecruitProvider');
        $providers = $providerService->getList('cost');
        $this->addElement($this->getDefaultSelectElementName(), 'provider_id',
            array(
                'Label' => _('Провайдер'),
                'Required' => $isAccepted ? false : true,
                'disabled' => $isAccepted ? true : null,
                'multiOptions' => $providers,
                'Filters' => array('StripTags'),
            )
        );
        
        
        $this->addElement($this->getDefaultTextElementName(), 'base_sum',
            array(
                'Label' => _('Базовая сумма плановых затрат, руб'),
                'Required' => $isAccepted ? false : true,
                'disabled' => $isAccepted ? true : null,
                'Filters' => array('StripTags'),
                'Validator' => array('float'),
            )
        );
        
        
        $this->addElement($this->getDefaultTextElementName(), 'corrected_sum',
            array(
                'Label' => _('Скорректированная сумма плановых затрат, руб'),
                'Filters' => array('StripTags'),
                'Validator' => array('float'),
            )
        );
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'period',
            'provider_id',
            'base_sum',
            'corrected_sum',
        ),
            'main',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
    

}