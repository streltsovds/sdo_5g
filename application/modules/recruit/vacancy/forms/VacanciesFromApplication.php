<?php
class HM_Form_VacanciesFromApplication extends HM_ParentForm_VacancyDataFields {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vacancies');

        $recruitApplicationId = $this->getParam('recruit_application_id');


        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );
        
        // ========================================================================================================
        //
        //                                            Общие свойства
        //
        // ========================================================================================================


        $this->addElement($this->getDefaultTextElementName(), $groupArrayDescription[] = 'vacancy_name', array(
            'Label' => _('Название сессии подбора'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), $groupArrayDescription[] = 'close_date', array(
            'Label' => _('Желательная дата начала работы'),
            'required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    50,
                    10
                )
            ),
            'Value' => date('d.m.Y'),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true',
                'buttonText' => _('Нажмите для выбора даты')
            )
        ));

//        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'salary', array(
//            'Label' => _('Предполагаемая заработная плата (постоянная часть)'),
//            'Required' => true,
//            'Filters' => array('StripTags'),
//            'class' => 'brief2'
//        ));

//        $this->addElement($this->getDefaultTextElementName(), $groupArrayDescription[] = 'bonus', array(
//            'Label' => _('Предполагаемая заработная плата (переменная часть)'),
//            'Required' => true,
//            'Filters' => array('StripTags'),
//            'class' => 'brief2'
//        ));
        
//        $this->addDisplayGroup($groupArray, 'general', array(
//            'legend' => _('Общие сведения')
//        ));

//        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init($groupArrayDescription); // required!
    }

}