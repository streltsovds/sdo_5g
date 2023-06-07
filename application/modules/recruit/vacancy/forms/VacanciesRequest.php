<?php
class HM_Form_VacanciesRequest extends HM_Form {
    
    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vacancies_request');
        
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'report', 'action' => 'index'))
            )
        );

        $this->addElement('hidden', 'position_id');
        $this->addElement('hidden', 'profile_id');
        
        $vacancyId = $this->getParam('vacancy_id');
        
        // ========================================================================================================
        //
        //                                            Общие свойства
        //
        // ========================================================================================================

        $groupArray = array();

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'name', array(
            'Label' => _('Название сессии подбора'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'class' => 'wide',
        ));
        
        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'position_name', array(
            'Label' => _('Название должности'),
            'Required' => true,
            'readonly' => true,
            'Filters' => array('StripTags'),
            'class' => 'wide',
        ));
        
        $categories = array();
        if (count($collection = $this->getService('AtCategory')->fetchAll(array(), 'name'))) {
            $categories = $collection->getList('category_id', 'name', ' ');
        }
        $this->addElement($this->getDefaultSelectElementName(), $groupArray[] = 'category_id', array(
            'Label' => _('Категория должности'),
            'filters' => array('int'),
            'multiOptions' => $categories,
            'disabled' => true,
            'class' => 'wide',
        ));     

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'department', array(
            'Label' => _('Структурное подразделение'),
            'Required' => true,
            'readonly' => true,
            'Filters' => array('StripTags'),
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'manager', array(
            'Label' => _('Непосредственный руководитель'),
            'Required' => false,
            'readonly' => true,
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultTagsElementName(), $groupArray[] = 'parent_top_position_id', array(
            'Label' => _('Функциональный руководитель'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
            'json_url' => Zend_Registry::get('view')->url(array('baseUrl' => '','module' => 'user', 'controller' => 'ajax', 'action' => 'users-list')),
            'Required' => false,
            'newel' => false,
            'maxitems' => 1
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete($groupArray[] = 'parent_top_position_id', array(
//            'Label' => _('Функциональный руководитель'),
//            'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
//            'json_url' => Zend_Registry::get('view')->url(array('baseUrl' => '','module' => 'user', 'controller' => 'ajax', 'action' => 'users-list')),
//            'Required' => false,
//            'newel' => false,
//            'maxitems' => 1
//        )));

        $this->addElement($this->getDefaultSelectElementName(), $groupArray[] = 'reason', array(
            'Label' => _('Причины открытия вакансии'),
            'required' => false,
            'filters' => array('int'),
            'multiOptions' => array('') + HM_Recruit_Vacancy_VacancyModel::getReasonVariants()
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), $groupArray[] = 'open_date', array(
            'Label' => _('Дата открытия вакансии'),
            'Description' => _('В этот день заявка будет автоматически опубликована на hh.ru'),
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

        $this->addElement($this->getDefaultDatePickerElementName(), $groupArray[] = 'close_date', array(
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
        
        $this->addElement($this->getDefaultMultiTextElementName(), $groupArray[] = 'tasks', array(
            'Label' => _('Укажите задачи и обязанности:'),
//            'SubLabel' => _('%s.'),
            'Filters' => array('StripTags'),
//            'class' => 'wide',
            //'Required' => true,
        ));
        
        $this->addElement($this->getDefaultRadioElementName(), $groupArray[] = 'subordinates', array(
            'Label' => _('Наличие подчиненных'),
            'Required' => false,
            'value' => ($position->is_manager) ? 1 : 0,
            'multiOptions' => array(
                '0' => _('Нет'),
                '1' => _('Да')
            ),
            'Filters' => array(
                'Int'
            ),
        ));
                
        $this->addElement($this->getDefaultSelectElementName(), $groupArray[] = 'subordinates_count', array(
            'Label' => _('Количество подчиненных'),
            'required' => false,
            'filters' => array('int'),
            'multiOptions' => array('') + HM_Recruit_Vacancy_VacancyModel::getStaffCountVariants()
        ));

        unset($categories[0]); // пустой элемент от верхнего select'а
        $this->addElement($this->getDefaultMultiSelectElementName(), $groupArray[] = 'subordinates_categories', array(
            'Label' => _('Категории должности подчиненных'),
            'multiOptions' => $categories
        ));     
        
        $this->addElement($this->getDefaultTextAreaElementName(), $groupArray[] = 'work_place', array(
            'Label' => _('Место работы (территориальное расположение)'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'value' => $workPlace,
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultSelectElementName(), $groupArray[] = 'work_mode', array(
            'Label' => _('Режим работы'),
            'required' => false,
            'filters' => array('int'),
            'multiOptions' => array('') + HM_Recruit_Vacancy_VacancyModel::getWorkModeVariants()
        ));

        $this->addElement($this->getDefaultSelectElementName(), $groupArray[] = 'trip_mode', array(
            'Label' => _('Командировки (наличие, периодичность, продолжительность)'),
            'required' => false,
            'filters' => array('int'),
            'multiOptions' => array('') + HM_Recruit_Vacancy_VacancyModel::getBusinessTripVariants(),
        ));

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'salary', array(
            'Label' => _('Предполагаемая заработная плата (тарифная ставка/оклад)'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'class' => 'brief2'
        ));

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'bonus', array(
            'Label' => _('Предполагаемая заработная плата (средний процент премии)'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'class' => 'brief2'
        ));
        
        
        $this->addDisplayGroup($groupArray, 'general', array(
            'legend' => _('Общие сведения')
        ));

        // ========================================================================================================
        //
        //                      Дополнительные требования для кандидата (отличные от профиля)
        //
        // ========================================================================================================

        $groupArray = array();

        $this->addElement($this->getDefaultSelectElementName(), $groupArray[] = 'gender', array(
            'Label' => _('Пол'),
            'required' => false,
            'filters' => array('int'),
            'multiOptions' => array('') + HM_At_Profile_ProfileModel::getGenderVariants() 
        ));

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'age_min', array(
            'Label' => _('Возраст (не моложе)'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'brief2'
        ));

        $this->addElement($this->getDefaultTextElementName(), $groupArray[] = 'age_max', array(
            'Label' => _('Возраст (не старше)'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'brief2'
        ));

        //Доп. требования к образованию:

        $this->addElement($this->getDefaultTextAreaElementName(), $groupArray[] = 'education', array(
            'Label' => _('Дополнительные требования к образованию'),
            'Required' => false,
            'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), $groupArray[] = 'requirements', array(
            'Label' => _('Дополнительные требования'),
            'Required' => false,
            'Filters' => array('StripTags')
        ));

        $this->addDisplayGroup($groupArray, 'advanced', array(
            'legend' => _('Дополнительные требования для кандидата (отличные от профиля)')
        ));

        // ========================================================================================================
        //
        //                                              План подбора
        //
        // ========================================================================================================
        //
        //                                          Размещение объявления
        //
        // ========================================================================================================

        $groupArray = array();

        foreach (HM_Recruit_Vacancy_VacancyModel::getSearchChannelVariants() as $key => $element) {
            
            $this->addElement($this->getDefaultCheckboxElementName(), $groupArray[] = $key, array(
                'label' => is_array($element) ? $element[0] : $element,
            ));
        
            if (is_array($element)) {
                $element = array_pop($element);
                foreach ($element as $key => $title) {
                    $this->addElement($this->getDefaultMultiTextElementName(), $groupArray[] = $key, array(
                        'label' => $title,
                        'Filters' => array('StripTags'),
                        'class' => 'wide'
                    )); 
                }
            }
        }
        $this->addDisplayGroup($groupArray, 'search_channels', array(
            'legend' => _('Каналы поиска')
        ));

        // ========================================================================================================
        //
        //                       Опыт в компаниях какой сферы желателен для кандидата?
        //
        // ========================================================================================================

        $groupArray = array();
        
        $this->addElement($this->getDefaultMultiSelectElementName(), $groupArray[] = 'experience', array(
            'Label' => _('Опыт в компаниях какой сферы желателен для кандидата?'),
            'Description' => _('Данный рубрикатор обновляется динамически в момент публикации вакансий на HeadHunter.'),
            'remoteUrl' => $this->getView()->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'experience-list', 'vacancy_id' => $vacancyId))
        ));     
        
        $this->addElement($this->getDefaultMultiTextElementName(), $groupArray[] = 'experience_other', array(
            'Label' => _('или укажите другие сферы деятельности:'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        ));
        
        $this->addElement($this->getDefaultMultiTextElementName(), $groupArray[] = 'experience_companies', array(
            'Label' => 'Названия компаний, опыт работы в которых желателен для кандидата',
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        ));
                
        $this->addDisplayGroup($groupArray, 'companies_group', array(
            'legend' => _('Опыт работы кандидата')
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Комментарий к сессии подбора'),
            'Required' => false,
            'class' => 'wide',
        ));

        $this->addDisplayGroup(array(
            'description',
        ),
            'comment',
            array('legend' => _('Комментарий'))
        );
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}