<?php
class HM_Form_Vacancies extends HM_ParentForm_VacancyDataFields {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vacancies');
        $groupArray = array();
        $this->addElement('hidden', 'cancelUrl',
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
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('datePicker', $groupArrayDescription[] = 'close_date', array(
            'Label' => _('Желательная дата начала работы'),
            'required' => false,
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




        parent::init($groupArrayDescription); // required!
    }

}