<?php
class HM_Form_Remember extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('remember');

        $this->addElement($this->getDefaultTextElementName(), 'email', array('Label' => _('E-mail'),
                'Required' => true,
                'Validators' => array(
                    array('EmailAddress')
                ),
                'Filters' => array('StripTags')
            )
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Восстановить')));

        $this->addDisplayGroup(
            array(
                'email',
                'submit'
            ),
            'rememberGroup',
            array('legend' => _('Восстановление пароля'))
        );
        parent::init();
    }
}