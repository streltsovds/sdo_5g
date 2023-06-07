<?php
class HM_Form_Fulltime extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('subjects');

        $subjectId = (int) $this->getParam('subject_id', $this->getParam('subid', 0));
        $sessionId = (int) $this->getParam('session_id', 0);
        $isNewAdditionalCourse = (int) $this->getParam('is_new_additional_course', 0);

        //subid
        $this->addElement('hidden', 'subid', array(
                'Required' => false,
                'Value' => $subjectId
            )
        );
        $this->addElement('hidden', 'provider_type', array(
                'Required' => false,
                'Value' => HM_Tc_Provider_ProviderModel::TYPE_PROVIDER
            )
        );
        //base_id
/*        $this->addElement('hidden', 'base_id', array(
                'Required' => false
            )
        );
*/

        $view = $this->getView();

        if ($isNewAdditionalCourse) {
            //tc/session/new-subjects/index/session_id/29
            $cancelUrl = $view->url(
                array(
                    'module'     => 'session',
                    'controller' => 'new-subjects',
                    'action'     => 'index',
                    'session_id' => $sessionId
                ),
                null,
                true
            );
        } else {
            $cancelUrl = $view->url(array(
                'module'     => 'subject',
                'controller' => 'fulltime',
                'action'     => 'index',
                'baseUrl'    => 'tc'
            ));
        }

        $providerTypeInUrl = $this->getRequest()->getParam('provider_type', NULL);
        if(isset($providerTypeInUrl)){
            $cancelUrl = $_SERVER['HTTP_REFERER'];
        }

        //cancelUrl
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $cancelUrl
        ));
        //Название - name
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement('text', 'code', array(
                'Label' => _('Код'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        false,
                        array('min' => 1, 'max' => 255)
                    )
                ),
                'Filters' => array('StripTags')
            )
        );

        //Описание - description
        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'class' => 'wide',
            'Filters' => array('HtmlSanitize'),
        ));

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
                'Label' => _('Иконка'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Ignore'    => true,
                'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
                'file_upload_limit' => 1,
                'subject' => null,
            )
        );

        //Провайдер - provider_id
        $providers = $this->getService('TcProvider')->fetchAll(null, 'name')->getList('provider_id', 'name');
        $this->addElement($this->getDefaultSelectElementName(), 'provider_id', array(
                'Label'        => _('Провайдер'),
                'Required'     => true,
                'multiOptions' => $providers,
//                'Validators'   => array('Int'),
//                'Filters'      => array('Int')
            )
        );

        $where = $this->getService('Classifier')->quoteInto(
            array(
                ' level = ?',
                ' AND type = ?'
            ),
            array(
                0,
                HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
            )
        );

        $optGroups = array(
            0 => 'Не задано'
        );
        $classifierElements = $this->getService('Classifier')->fetchAll($where);

        foreach ($classifierElements as $classifierElement) {
//            $where = $this->getService('Classifier')->quoteInto(
//                array(
//                    ' level = ?',
//                    ' AND type = ?',
//                    ' AND lft > ?',
//                    ' AND rgt < ?',
//                ),
//                array(
//                    1,
//                    HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
//                    $classifierElement->lft,
//                    $classifierElement->rgt,
//
//                )
//            );
//            $subElements = $this->getService('Classifier')->fetchAll($where, 'name')->getList('classifier_id', 'name');
            $optGroups[$classifierElement->classifier_id] = $classifierElement->name;
        }

        $this->addElement($this->getDefaultSelectElementName(), 'direction_id', array(
                'Label' => _('Направления обучения'),
                'multiOptions' => $optGroups,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );
        //Категория - category
        $this->addElement($this->getDefaultSelectElementName(), 'category', array(
                'Label'        => _('Категория обучения'),
                'Required'     => true,
                'multiOptions' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeCategories'),
//                'Validators'   => array('Int'),
//                'Filters'      => array('Int')
            )
        );
//        //Первичное/вторичное обучение - primary_type
//        $this->addElement($this->getDefaultSelectElementName(), 'primary_type', array(
//                'Label'        => _('Первичное/вторичное обучение'),
//                'Required'     => true,
//                'multiOptions' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeTypes'),
//                'Validators'   => array('Int'),
//                'Filters'      => array('Int')
//            )
//        );
        //Формат - format
        $this->addElement($this->getDefaultSelectElementName(), 'format', array(
                'Label'        => _('Формат'),
                'Required'     => false,
                'multiOptions' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeFormates'),
                'Validators'   => array('Int'),
                'Filters'      => array('Int')
            )
        );
        //Статус - status
        $this->addElement($this->getDefaultSelectElementName(), 'status', array(
                'Label'        => _('Статус'),
                'Description'  => _('Только утвержденные курсы участвуют в процессе автоматического формирования заявок на внешнее обучение.'),
                'Required'     => true,
                'multiOptions' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeStates'),
//                'Validators'   => array('Int'),
//                'Filters'      => array('Int')
            )
        );
        //Стоимость - price
        $this->addElement($this->getDefaultTextElementName(), 'price', array(
                'Label' => _('Стоимость без НДС на одного участника'),
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('StripTags')
            )
        );
        //Продолжительность - longtime
        $this->addElement($this->getDefaultTextElementName(), 'longtime', array(
                'Label' => _('Длительность курса, дней'),
                'Required' => false,
                'Validators' => array('Int'),
                'Filters' => array('StripTags')
            )
        );

        //Мин. Группа - plan_users
        $this->addElement($this->getDefaultTextElementName(), 'plan_users', array(
                'Label' => _('Минимальная численность группы'),
                'Required' => false,
                'Validators' => array('Int'),
                'Filters' => array('StripTags')
            )
        );
        //Метки - tags
        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Метки'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'tags')),
            'Filters' => array()
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
//                'Label' => _('Метки'),
//                'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
//                'json_url' => $this->getView()->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'tags')),
//                'Filters' => array()
//            )
//        ));

//        //Город - city
//        $this->addElement(new HM_Form_Element_FcbkComplete('city', array(
//                'Label'    => _('Город'),
//                'Required' => true,
//                'json_url' => $this->getView()->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'cities')),
//                'Filters'  => array(),
//                'Validators' => array('Int'),
//                'newel' => false,
//                'maxitems' => 20
//            )
//        ));

//        //Оценка результатов обучения - mark_required
//        $this->addElement($this->getDefaultSelectElementName(), 'mark_required', array(
//            'Label' => _('Оценка результатов обучения'),
//            'Required' => false,
//            'Validators' => array('Int'),
//            'Filters' => array('Int'),
//            'MultiOptions' => array(
//                0 => _('Нет'),
//                1 => _('Да')
//            )
//        ));
//        //Форма контроля результатов обучения - check_form
//        $this->addElement($this->getDefaultSelectElementName(), 'check_form', array(
//                'Label'        => _('Форма контроля результатов обучения'),
//                'Required'     => false,
//                'multiOptions' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeCheckFormes'),
//                'Validators'   => array('Int'),
//                'Filters'      => array('Int')
//            )
//        );

//        //даты
        $this->addElement($this->getDefaultDatePickerElementName(), 'begin', array(
                'Label' => _('Дата начала'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );
        $this->addElement($this->getDefaultDatePickerElementName(), 'end', array(
                'Label' => _('Дата окончания'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    ),
                    array(
                        'DateGreaterThanFormValue',
                        false,
                        array('name' => 'begin')
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );

        $restrictions = HM_Tc_Subject_SubjectModel::getPeriodRestrictionTypes();
        unset($restrictions[HM_Tc_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL]);
        $this->addElement($this->getDefaultSelectElementName(), 'period_restriction_type', array(
                'Label' => _('Тип ограничения'),
                'Required' => false,
                'Description' => nl2br(_('При строгом ограничении времени курса слушатели и прошедшие обучение могут входить в учебный курс строго в указанный диапазон дат. В остальное время доступ пользователей блокируется, слушатели прошедшие обучение не могут оставлять отзывы. В момент наступления даты окончания курса все слушатели автоматически переводятся в прошедшие обучение.
При нестрогом ограничении даты начала и окончания курса носят рекомендательный характер. Слушатели могут входить в курс до даты начала и после даты окончания курса. По истечении времени курса слушатели не переводятся автоматически в прошедшие обучение.')),
                'multiOptions' => $restrictions,
            )
        );

//        //Посттренинговые мероприятия - after_training
//        $this->addElement($this->getDefaultCheckboxElementName(), 'after_training', array(
//                'Label'        => _('Посттренинговые мероприятия'),
//                'Required'     => false,
//            )
//        );
//        //Обратная связь - feedback
//        $this->addElement($this->getDefaultCheckboxElementName(), 'feedback', array(
//                'Label'        => _('Обратная связь'),
//                'Required'     => false,
//            )
//        );
        //Файлы - files
        $this->addElement($this->getDefaultFileElementName(), 'files', array(
                'Label' => _('Файлы'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Description' => _('Максимальный размер файла &ndash; 10 Mb'),
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_upload_limit' => 100,
                'subject' => null,
            )
        );


        //Компетенция/квалификация
        $this->addElement($this->getDefaultTagsElementName(), 'criterion', array(
            'Label'      => _('Компетенция/квалификация'),
            'Description'=> _('Для обязательных курсов следует выбирать только обязательные квалификации.'),
            'Required'   => false,
            'json_url'   => $this->getView()->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'criteria')),
            'Filters'    => array(),
            'newel'      => false,
            'maxitems'   => 1
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('criterion', array(
//                'Label'      => _('Компетенция/квалификация'),
//                'Description'=> _('Для обязательных курсов следует выбирать только обязательные квалификации.'),
//                'Required'   => false,
//                'json_url'   => $this->getView()->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'criteria')),
//                'Filters'    => array(),
//                'newel'      => false,
//                'maxitems'   => 1
//            )
//        ));
        //Компетенция/квалификация
        $this->addElement($this->getDefaultTextElementName(), 'criterion_text', array(
                'Label' => _('Компетенция/квалификация'),
                'Disabled' => true
            )
        );

        // тип обучения
        $this->addElement('hidden', 'education_type', array(
                'Label'        => _('Тип обучения'),
                'Required'     => true,
                'Value' => HM_Tc_Subject_StudyCenter_SubjectModel::TYPE_EDUCATION_OUTER,
                'Validators'   => array('Int'),
                'Filters'      => array('Int')
            )
        );

        $this->addDisplayGroup(array(
//                'cancelUrl',
                'name',
                'code',
                'status',
                'education_type',
                'description',
                'icon',
                'direction_id',
                'provider_id',
                'price',
                'category',
                'criterion',
                'criterion_text'
            ),
            'Fulltime1',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(array(
//                'city',
//                'primary_type',
                'format',
                'plan_users',
//                'mark_required',
//                'check_form',
//                'after_training',
//                'feedback'
            ),
            'Fulltime2',
            array('legend' => _('Организация обучения'))
        );


        $fulltimeItems3 = array(
            'longtime',
            'begin',
            'end',
            'period_restriction_type'
        );

        $this->addDisplayGroup(
            $fulltimeItems3,
            'Fulltime3',
            array('legend' => _('Ограничение времени обучения'))
        );

        $this->addDisplayGroup(array(
                'tags',
                'files',
            ),
            'Fulltime4',
            array('legend' => _('Дополнительная информация'))
        );

        $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $subjectId);
        $this->addClassifierDisplayGroup($classifierElements);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'icon') {
            $decorators = parent::getElementDecorators($alias, 'SubjectImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }

    public function getFieldsArray()
    {
        $classifierData = array();
        $result = array();

        $allGroups = $this->getDisplayGroups();
        foreach ($allGroups as $group) {
            $viewGroup = array(
                'title'  => $group->getLegend(),
                'fields' => array()
            );
            $elements = $group->getElements();
            foreach ($elements as $element) {
                if ($element->getName() == 'icon') {
                    continue;
                }
                if ($group->getName() == 'classifiers') {
                    $value = $element->getName();

                } else {
                    $value = $element->getValue();
                    switch ($element->getType()) {
                        case 'Zend_Form_Element_Hidden':
                            continue(2);
                            break;
                        case 'Zend_Form_Element_Select':
                            $value = $element->options[$value];
                            break;
                        case 'Zend_Form_Element_Checkbox':
                            $value = $value ? _('Да') : _('Нет');
                            break;
                        case 'ZendX_JQuery_Form_Element_DatePicker':
                            $value   = ($value && strtotime($value)) ? date('d.m.Y', strtotime($value)) : '';
                            break;
                        case 'HM_Form_Element_Html5File' :
                            if (is_array($value)) {
                                $files = array();
                                foreach ($value as $file) {
                                    $files[] = ' <a href="'.$file->getUrl().'">'.$file->getDisplayName().'</a>';
                                }
                                $value = $files;
                            }
                    }
                }


/*                $viewGroup['fields'][$element->getName()] = array(
                    'id'    => $element->getName(),
                    'type'  => $element->getType(),
                    'title' => $element->getLabel(),
                    'value' => $value
                );
*/

                $viewGroup['fields'][$element->getLabel()] = is_array($value) ? implode("<br>",  $value) : $value;
            }

            $result[$group->getName()] = $viewGroup;
        }

        return $result;
    }

}