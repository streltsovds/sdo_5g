<?php
class HM_Form_SuperjobVacancy extends HM_Form {

    protected $superjob;
    
    protected function setSuperjob($superjob)
    {
        $this->superjob = $superjob;
    }
    
    protected function getReference($referenceName){
        $references = $this->superjob->getReferences();
        
        $multiOptions = array();
        foreach($references->$referenceName as $key => $value){
            $multiOptions[$key] = $value;
        }
        return $multiOptions;
    }


    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vacancy_profile');
        
        $vacancyId = $this->getParam('vacancy_id');
        
        $services = Zend_Registry::get('serviceContainer');
        
        $vacancy   = $services->getService('RecruitVacancy')->getOne($services->getService('RecruitVacancy')->find($vacancyId));
        
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'report', 'action' => 'card'))
            )
        );
        
        $groupField = array();
        
        $this->addElement(
            'text',
            $groupField[] = 'profession',
            array(
                'Label'    => _('Название вакансии'),
                'Required' => true,
                'Filters'  => array('StripTags')
            )
        );
        
        
        $towns = $this->superjob->getTowns();
        
        $multiOptions = array();
        foreach($towns->objects as $town){
            $multiOptions[$town->id] = $town->title;
        }
        
        $this->addElement(
            'select',
            $groupField[] = 'town',
            array(
                'Label'    => _('Город'),
                'Required' => true,
                'Filters'  => array('Int'),
                'multiOptions'  => $multiOptions
            )
        );
        
        $multiOptionsPublished = array(
            0 => _('закрытый доступ'),
            1 => _('открытый доступ'),
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'published',
            array(
                'Label'    => _('Тип доступа'),
                'Required' => true,
                'Filters'  => array('Int'),
                'multiOptions'  => $multiOptionsPublished
            )
        );
        
        
        $this->addElement($this->getDefaultMultiSelectElementName(), $groupField[] = 'catalogues', array(
            'required' => true,
            'Label' => _('Сфера деятельности'),
            'Description' => _('Максимум 5 элементов, каталоги не учитываются'),
            'remoteUrl' => $this->getView()->url(array('module' => 'vacancy', 'controller' => 'superjob', 'action' => 'catalogues'))
        ));
        
        $this->addElement(
            'text',
            $groupField[] = 'firm_name',
            array(
                'Label'    => _('Название компании'),
                'Required' => true,
                'Filters'  => array('StripTags')
            )
        );
        
        $this->addElement(
            'textarea',
            $groupField[] = 'firm_activity',
            array(
                'Label'    => _('Описание деятельности компании'),
                'Required' => true,
                'Filters'  => array('StripTags')
            )
        );
        
        $this->addElement(
            'textarea',
            $groupField[] = 'work',
            array(
                'Label'    => _('Должностные обязанности'),
                'Required' => false,
                'Filters'  => array('StripTags')
            )
        );
        
        $this->addElement(
            'textarea',
            $groupField[] = 'compensation',
            array(
                'Label'    => _('Условия работы'),
                'Required' => false,
                'Filters'  => array('StripTags')
            )
        );
        
        $this->addElement(
            'textarea',
            $groupField[] = 'candidat',
            array(
                'Label'    => _('Требования к кандидату'),
                'Required' => false,
                'Filters'  => array('StripTags')
            )
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'type_of_work',
            array(
                'Label'    => _('Тип занятости'),
                'Required' => false,
                'Filters'  => array('Int'),
                'multiOptions'  => $this->getReference('type_of_work')
            )
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'place_of_work',
            array(
                'Label'    => _('Место работы'),
                'Required' => false,
                'Filters'  => array('Int'),
                'multiOptions'  => $this->getReference('place_of_work')
            )
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'education',
            array(
                'Label'    => _('Образование'),
                'Required' => false,
                'Filters'  => array('Int'),
                'multiOptions'  => $this->getReference('education')
            )
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'experience',
            array(
                'Label'    => _('Опыт работы'),
                'Required' => false,
                'Filters'  => array('Int'),
                'multiOptions'  => $this->getReference('experience')
            )
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'maritalstatus',
            array(
                'Label'    => _('Семейное положение'),
                'Required' => false,
                'Filters'  => array('Int'),
                'multiOptions'  => $this->getReference('maritalstatus')
            )
        );
        
        $this->addElement(
            'select',
            $groupField[] = 'children',
            array(
                'Label'    => _('Наличие детей'),
                'Required' => false,
                'Filters'  => array('Int'),
                'multiOptions'  => $this->getReference('children')
            )
        );

        $this->addDisplayGroup($groupField, 'vacancies', array(
            'legend' => _('Главные свойства')
        ));

        
        $groupField = array();
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Создать')));

        parent::init();
    }
}