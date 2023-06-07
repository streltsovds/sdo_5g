<?php
class HM_Form_VacancyProfile extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vacancy_profile');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $profiles = $this->getService('Profile')->getProfiles();
        array_unshift($profiles, '');
        $this->addElement($this->getDefaultSelectElementName(), 'profile',
            array(
                'Label' => _('Выберите профиль успешности:'),
                'Required' => true,
                'Filters' => array(
                    'Int'
                ),
                'multiOptions' => $profiles,
            )
        );

        $this->addDisplayGroup(array(
            'cancelUrl',
            'profile',
        ),
            'vacancies',
            array('legend' => _('Профиль успешности'))
        );

//        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}