<?php
class HM_Form_RecruitPublicationOptions extends HM_Form
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('AtOptions')
            ->setAttrib('class', 'all-fieldsets-collapsed');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'publicationCompanyName', array(
            'Label' => _('Название Компании'),
            'Required' => false,
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'publicationCompanyDescription', array(
            'Label' => _('Описание деятельности Компании'),
            'Required' => false,
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'publicationCompanyConditions', array(
            'Label' => _('Условия работы'),
            'Required' => false,
        ));



        $this->addDisplayGroup(array(
            'publicationCompanyName',
            'publicationCompanyDescription',
            'publicationCompanyConditions',
        ),
            'general',
            array('legend' => _('Общие настройки'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_AT_OPTIONS);
        $this->getService('EventDispatcher')->filter($event, $this);
        
        parent::init(); // required!
    }
}