<?php
class HM_Form_ActualCosts extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
      
        $this->setName('actualCosts');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array(
                    'module'     => 'costs',
                    'controller' => 'actual-costs',
                    'action'     => 'index',
                ), null, true)
            )
        );  
        
        $this->addElement($this->getDefaultSelectElementName(), 'period',
            array(
                'Label' => _('Период'),
                'Required' => true,
                'multiOptions' => HM_Recruit_ActualCosts_ActualCostsModel::getPeriods(),
                'Filters' => array('StripTags'),
            )
        );
        

        $this->addElement($this->getDefaultSelectElementName(), 'provider_id',
            array(
                'Label' => _('Провайдер'),
                'Required' => true,
                'multiOptions' => $this->getService('RecruitProvider')->getList('cost'),
                'Filters' => array('int'),
                'Validators' => array(
                    'int',
                    array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
                ),
            )
        );
        
        
        $this->addElement($this->getDefaultTextElementName(), 'document_number',
            array(
                'Label' => _('№ платежного документа'),
                'Required' => true,
                'Filters' => array('StripTags'),
            )
        );
        
        $this->addElement('RadioGroup', 'payment_type',
            array(
                'Label' => _('Тип платежа'),
                'Required' => true,
                'multiOptions' => HM_Recruit_ActualCosts_ActualCostsModel::getPaymentTypes(),
                'Filters' => array('StripTags'),
                'form' => $this,
                'dependences' => array(
                    HM_Recruit_ActualCosts_ActualCostsModel::PAYMENT_TYPE_DOCUMENT   => array('pay_date_document'),
                    HM_Recruit_ActualCosts_ActualCostsModel::PAYMENT_TYPE_ACTUAL => array('pay_date_actual'),
                )
            )
        );
        
        $this->addElement($this->getDefaultDatePickerElementName(), 'pay_date_document', array(
            'Label' => _('Дата оплаты по платежному документу'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        ));
        
        $this->addElement($this->getDefaultDatePickerElementName(), 'pay_date_actual', array(
            'Label' => _('Дата оплаты по факту'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'pay_amount',
            array(
                'Label' => _('Сумма к оплате, руб'),
                'Required' => true,
                'Filters' => array('StripTags'),
                'Validator' => array('float'),
            )
        );
        
        
        $this->addDisplayGroup(array(
            'payment_type',
            'cancelUrl',
            'document_number',
            'pay_amount',
            'period',
            'provider_id',
            'pay_date_document',
            'pay_date_actual',
        ),
            'main',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}