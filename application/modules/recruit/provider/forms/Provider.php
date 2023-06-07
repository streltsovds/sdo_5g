<?php
class HM_Form_Provider extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
      
        $this->setName('providers');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );


        $this->addElement($this->getDefaultTextElementName(), 'name',
            array(
                'Label' => _('Название'),
                'Required' => true,
                'Filters' => array('StripTags'),
            )
        );

        $this->addElement('hidden', 'status',
            array(
                'Required' => true,
                'Value' => HM_Recruit_Provider_ProviderModel::STATUS_ACTUAL
            )
        );

        /*
        $this->addElement($this->getDefaultSelectElementName(), 'status',
            array(
                'Label' => _('Статус'),
                'Required' => true,
                'multiOptions' => HM_Recruit_Provider_ProviderModel::getStatuses(),
                'Filters' => array('StripTags'),
            )
        );
        */

        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'status',
        ),
            'providers',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}