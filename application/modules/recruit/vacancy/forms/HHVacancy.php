<?php
class HM_Form_HHVacancy extends HM_Form {

    /**
     * @var HM_Recruit_RecruitingServices_Rest_Hh
     */
    protected $hh;

    /**
     * @param HM_HeadHunter $hh
     */
    protected function setHh($hh)
    {
        $this->hh = $hh;
    }
    
    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vacancy_profile');
        
        $vacancyId = $this->getParam('vacancy_id');
        $vacancy   = Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->getOne(Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->find($vacancyId));

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'report', 'action' => 'card'))
            )
        );
        
        $groupField = array();


        /*
         * Вакансия - открытая или закрытая
         * vacancyType:0
         */
        $this->addElement(
            'radio',
            $groupField[] = 'type',
            array(
                'Label'        => _('Тип вакансии'),
                'Required'     => true,
                'MultiOptions' => array(
                    'open'      => _('Открытая'),
                    'closed'    => _('Закрытая'),
                    'anonymous' => _('Анонимная')
                )
            )
        );

        $this->addElement(
            'select',
            $groupField[] = 'billing_type',
            array(
                'Label'        => _('Тип биллинга'),
                'Required'     => false,
                'MultiOptions' => $this->hh->getVacancyBillingTypes()
            )
        );

        /*
         * Название вакансии
         * name:Nazvanie
         */
        $this->addElement(
            'text',
            $groupField[] = 'name',
            array(
                'Label'    => _('Название вакансии'),
                'Required' => true,
                'Filters'  => array('StripTags')
            )
        );
        
        $this->addElement(
            'text',
            $groupField[] = 'custom_employer_name',
            array(
                'Label'    => _('Название компании для анонимных вакансий'),
                'Required' => false,
                'Filters'  => array('StripTags')
            )
        );
        

        $departments = $this->hh->getDepartments();
        if ($departments) {
            $this->addElement($this->getDefaultSelectElementName(),
                $groupField[] = 'departmentCode',
                array(
                    'Label'       => 'Подразделение',
                    'Required'     => true,
                    'multiOptions' => $departments,
                )
            );
        } else {
            $this->addElement('hidden',
                'departmentCode',
                array(
                    'Required' => false,
                    'Value' => $this->hh->getEmployerId()
                )
            );
        }


        $this->addDisplayGroup($groupField, 'vacancies', array(
            'legend' => _('Главные свойства')
        ));

        $groupField = array();

        $this->addElement($this->getDefaultMultiSelectElementName(), $groupField[] = 'specializations', array(
            'required' => true,
            'Label' => _('Специализации'),
            'remoteUrl' => $this->getView()->url(array('module' => 'vacancy', 'controller' => 'hh', 'action' => 'specializations'))
        ));

        $this->addDisplayGroup($groupField, 'specializationIds', array(
            'legend' => _('Специализации')
        ));

        //Город
        $this->addElement($this->getDefaultTagsElementName(), 'area', array(
            'required' => true,
            'Label' => _('Вакансия в городе'),
            'json_url' => '/recruit/vacancy/hh/region-search',
            'newel' => false,
            'maxitems' => 10
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('area', array(
//                'required' => true,
//                'Label' => _('Вакансия в городе'),
//                'json_url' => '/recruit/vacancy/hh/region-search',
//                'newel' => false,
//                'maxitems' => 10
//            )
//        ));

        $this->addDisplayGroup(array('area'), 'region_target', array(
            'legend' => _('Вакансия в городе')
        ));
        

        $groupField = array();
        
        /*
         * Зарплата
         * compensationFrom:1000
         * compensationTo:2000
         */
        
        $this->addElement(
            'text',
            $groupField[] = 'compensationFrom',
            array(
                'Label'    => _('Зарплата, от'),
                'Required' => false,
                'Filters'  => array('StripTags')
            )
        );

        $this->addElement(
            'text',
            $groupField[] = 'compensationTo',
            array(
                'Label'    => _('Зарплата, до'),
                'Required' => false,
                'Filters'  => array('StripTags')
            )
        );
        
        /*
         * Селект со списком валют, скорее всего отправлять не надо
         * thisAreaCurrency:EUR
         * thisAreaCurrency:RUR
         * thisAreaCurrency:USD
         * 
         * Выбранная валюта для зарплаты
         * currencyCode:USD
         */
        $this->addElement(
            'select',
            $groupField[] = 'currency',
            array(
                'Label'        => _('Валюта'),
                'Required'     => false,
                'Filters'      => array('StripTags'),
                'multiOptions' => $this->hh->getCurrency(),
                'Value'        => 'RUR'
            )
        );
        
        $this->addDisplayGroup($groupField, 'compensation', array(
            'legend' => _('Зарплата')
        ));

        $groupField = array();


        //Описание вакансии description:Обязанности:<br/>Требования:<br/>Условия: /
        $this->addElement($this->getDefaultWysiwygElementName(), $groupField[] = 'description', array(
            'Label'    => _('Описание'),
            'Required' => true,
            'toolbar'  => 'hmToolbarHH',
            'Validators' => array(
                array('StringLength', 0, 200)
            ),
        ));
        // Опыт работы (выбрано 3-6 лет) vacancy.experience:2
        $this->addElement($this->getDefaultRadioElementName(), $groupField[] = 'experience', array(
           'Label'        => _('Опыт работы'),
           'multiOptions' => $this->hh->getWorkExperience()
        ));
        // График работы (гибкий график) scheduleId:2 /
        $this->addElement($this->getDefaultRadioElementName(), $groupField[] = 'schedule', array(
            'Label'        => _('График работы'),
            'multiOptions' => $this->hh->getSchedule()
        ));
        //Тип занятости (Волонтёрство) employmentId:3
        $this->addElement($this->getDefaultRadioElementName(), $groupField[] = 'employment', array(
            'Label'        => _('Тип занятости'),
            'multiOptions' => $this->hh->getEmployment()
        ));
        // Доступна для инвалидов vacancy.acceptHandicapped:true
        $this->addElement($this->getDefaultCheckboxElementName(), $groupField[] = 'accept_handicapped', array(
            'Label'        => _('Вакансия доступна для соискателей с инвалидностью')
        ));

        // Требовать сопроводительное письмо vacancy.response_letter_required:true
        $this->addElement($this->getDefaultCheckboxElementName(), $groupField[] = 'response_letter_required', array(
            'Label'        => _('Требовать сопроводительное письмо')
        ));

        $managers = $this->hh->getManagers();
        if ($managers) {
            $this->addElement($this->getDefaultSelectElementName(),
                $groupField[] = 'manager',
                array(
                    'Label'        => _('Менеджер вакансии'),
                    'multiOptions' => $managers,
                )
            );            // Уведомлять о новых откликах vacancy.notify:on
            $this->addElement($this->getDefaultCheckboxElementName(), $groupField[] = 'notify', array(
                'Label'        => _('Уведомлять о новых откликах')
            ));
        }

        $tests = array(0 => _('Выберите тест')) + $this->hh->getTests();
        if ($tests) {
            $this->addElement($this->getDefaultSelectElementName(),
                $groupField[] = 'test',
                array(
                    'Label'       => _('Прикрепить вопросы к вакансии'),
                    'multiOptions' => $tests,
                )
            );

            //Принимать отклики только с заполненными тестами vacancy.testSolutionRequired:1
            $this->addElement($this->getDefaultCheckboxElementName(), $groupField[] = 'testSolutionRequired', array(
                'Label'        => _('Принимать отклики только с заполненными тестами')
            ));
        }

        $this->addDisplayGroup($groupField, 'add_info', array(
            'legend' => _('Дополнительная информация о вакансии')
        ));
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Создать')));

        parent::init();
    }
}