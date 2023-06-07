<?php
class HM_Form_Hh extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('hh');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'assign', 'controller' => 'recruiter', 'action' => 'index'))
        ));

        $this->addElement('hidden', 'recruiter_id', array(
            'required' => true,
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'hh_email', array(
            'Label' => _('Логин'),
            'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('max' => 50, 'min' => 3)
                )
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'hh_password', array(
            'Label' => _('Пароль'),
            'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('max' => 50, 'min' => 3)
                )
            ),
            'type' => 'password'
        ));

        $this->addElement($this->getDefaultTextElementName(), 'hh_managerId', array(
            'Label' => _('Идентификатор учётной записи менеджера'),
            'Required' => true,
            'Filters' => array(
                'Int'
            ),
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('max' => 10, 'min' => 1)
                )
            )
        ));

        $this->addElement('hidden', 'hh_region', array(
            'Label' => _('Домен HH'),
            'Required' => true,
            'value' => Zend_Registry::get('config')->vacancy->hh->region, // похоже он ни на что не влияет, берём из настроек
        ));

        $this->addDisplayGroup(
            array(
                'hh_email',
                'hh_password',
                'hh_managerId',
                'hh_region'
            ),
            'hhGroup',
            array('legend' => _('Данные учётной записи на hh.ru'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init();
	}

}