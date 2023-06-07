<?php

class HM_Form_ChangeStudent extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('change-student');

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'assign', 'controller' => 'student', 'action' => 'index'))
        ));

        $this->addElement($this->getDefaultTagsElementName(), 'new', array(
            'required' => true,
            'Label' => _('Пользователь'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
            'json_url' => '/user/ajax/users-list',
            'newel' => false,
            'maxitems' => 1,
            'fullPreload' => false,
            'itemText' => 'key',
            'returnIdsNotText' => true
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'new',
                'submit'
            ),
            'messageGroup',
            array('legend' => _('Пользователь для замены'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Заменить')));

        parent::init();
    }
}