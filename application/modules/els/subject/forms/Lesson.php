<?php
/*
 * 5G
 *
 */
class HM_Form_Lesson extends HM_Form
{
    /** @var HM_Lesson_LessonModel $_lesson  */
    protected $_lesson;

    /** @var HM_Subject_SubjectModel $_subject  */
    protected $_subject;

	public function init()
	{
        /** @var HM_Event_EventService $eventService */
        $eventService = $this->getService('Event');

        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');

        $lessonId = $this->getParam('lesson_id', 0);
        /** @var HM_Lesson_LessonModel $lesson */
        $this->_lesson = $lesson = $lessonService->getOne(
            $lessonService->findDependence('Subject', $lessonId)
        );

        $newType = $this->getParam('typeID', $this->_lesson->typeID);
        if($newType !== $this->_lesson->typeID) {
            $data = $this->_lesson->getData();

            $data['tool'] = $newType < 0 ? HM_Event_EventModel::TYPE_TEST : '';
            $data['typeID'] = $newType;

            $this->_lesson = $lesson = HM_Lesson_LessonModel::factory($data);
        }

        /** @var HM_Subject_SubjectModel $subject */
        $this->_subject = $subject = $lesson->subject->current();
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->addElement('hidden', 'subject_id', array(
            'Required' => false,
        ));
        $this->addElement('hidden', 'lesson_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        /********* Общие свойства *********/

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название занятия'),
            'Description' => _('Отображается на странице "Все занятия".'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'has_proctoring', array(
            'Label' => _('Режим прокторинга'),
            'Description' => _('Режим прокторинга позволяет визуально контролировать процесс прохождения занятия слушателем'),
            'Required' => false,
            'Filters' => array('Int'),
        ));


        $types = $subject->getEventTypes();
        $currentBaseType = $lessonService->getLessonTool($lesson->typeID);

        // не позволяем менять тип;
        // только выбрать произвольный тип на базе того же инструмента обучения
        $typeKeys = array_filter(array_keys($types), function($type) use ($currentBaseType, $eventService) {
            return ($type == $currentBaseType) || $eventService->inheritsType($type, $currentBaseType);
        }); // ARRAY_FILTER_USE_KEY нет в php5.6
        $types = array_intersect_key($types, array_flip($typeKeys));

        $this->addElement($this->getDefaultSelectElementName(), 'typeID', array(
            'Label' => _('Тип занятия'),
            'Required' => true,
            'Validators' => [],
            'Filters' => [],
            'refresh' => [
                'enabled' => true,
                'description' => _('Будет изменён тип занятия, изменения на странице не сохранятся')
            ],
            'MultiOptions' => $types,
        ));

        //является заменой старого module
        // multiOptions определяются ниже в initXXX()
//        $this->addElement($this->getDefaultSelectElementName(), 'material_id', array(
//            'Label' => _('Материал'),
//            'Required' => true,
//            'Validators' => array(
//                'Int',
//                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
//            ),
//            'Filters' => array('Int'),
//            'MultiOptions' => [],
//        ));

        $this->addElement($this->getDefaultTextAreaElementName(),
            'descript',
            array(
                'Label'      => _('Краткое описание'),
                'Required'   => false,
                'Validators' => array(),
                'Filters'    => array('StripTags')
            ));

        $this->addElement($this->getDefaultSelectElementName(), 'vedomost', array(
            'Label' => _('Занятие на оценку'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'MultiOptions' => array(
                HM_Lesson_LessonModel::MARK_OFF => _('Нет'),
                HM_Lesson_LessonModel::MARK_ON => _('Да')
            )
        ));

        $sections = $this->getService('Section')->fetchAll($lessonService->quoteInto('subject_id = ?', $subject->subid))->getList('section_id', 'name');
        $sections[0] = '[' . _('Без раздела') . ']';
        $this->addElement(
            $this->getDefaultSelectElementName(),
            'section_id',
            array(
                'Label' => _('Раздел'),
                'Required' => false,
                'Filters' => array('Int'),
                'multiOptions' => $sections,
            )
        );

        // проверка на зависимость.
        if (count($lessonService->fetchAll($lessonService->quoteInto('cond_sheid = ?', $lesson->SHEID)))) {
            $vedomostElement = $this->getElement('vedomost');
            $vedomostElement->setOptions(array('OnChange' => "if (this.value == 0) { alert(`" . _('У данного занятия имеются зависимости.') . "``); this.value = 1;}"));
        }

        $this->addDisplayGroup(
            array(
                'title',
                'typeID',
                'has_proctoring',
                'descript',
                'vedomost',
                'section_id',
                'submit',
                'submit_and_redirect',
                'cancel',
            ),
            'lesson',
            array('legend' => _('Общие свойства'))
        );


        /********* Дата и время выполнения *********/

        $groupDateArray = HM_Lesson_LessonModel::getDateTypes();

        if ($this->_lesson && $this->_lesson->getType() == HM_Event_EventModel::TYPE_ECLASS) {
            unset($groupDateArray[HM_Lesson_LessonModel::TIMETYPE_FREE]);
        }

        $this->addElement('RadioGroup', 'GroupDate',
            [
                'Label' => '',
                'Value' => HM_Lesson_LessonModel::TIMETYPE_DATES,
                'MultiOptions' => $groupDateArray,
                'form' => $this,
                'dependences' => [
                    HM_Lesson_LessonModel::TIMETYPE_FREE => [],
                    HM_Lesson_LessonModel::TIMETYPE_DATES => ['beginDate', 'endDate'],
                    HM_Lesson_LessonModel::TIMETYPE_TIMES => ['currentDate', 'beginTime', 'endTime'],
                    HM_Lesson_LessonModel::TIMETYPE_RELATIVE => ['beginRelative', 'endRelative']
                ]
            ]);

        // Диапазон дат
        $this->addElement($this->getDefaultDatePickerElementName(), 'beginDate', array(
            'Label' => _('Дата начала'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1),
                array('DateLessThanFormValue', false, array('name' => 'endDate'))
            ),
            'id' => "beginDate",
            'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'endDate', array(
            'Label' => _('Дата окончания'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1),
                array('DateGreaterThanFormValue', false, array('name' => 'beginDate'))
            ),
            'id' => "endDate",
            'Filters' => array('StripTags')
        ));

        // Диапазон времени
        $this->addElement($this->getDefaultDatePickerElementName(), 'currentDate', array(
            'Label' => _('Дата'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1)
            ),
            'id' => "beginDate2",
            'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultTimePickerElementName(), 'beginTime', array(
            'Label' => _('Время начала'),
            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/')
            ),
            'Filters' => array(
            )
       ));

        $this->addElement($this->getDefaultTimePickerElementName(), 'endTime', array(
            'Label' => _('Время окончания'),
            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/')
            ),
            'Filters' => array()
        ));

        // Относительный диапазон
        $this->addElement($this->getDefaultTextElementName(), 'beginRelative',
            array(
                'Label' => _('День начала'),
                'Required' => true,
                'Description' => _('Эти дни отсчитываются от даты начала обучения конкретного слушателя по курсу. Если использовать отрицательные значения, дни будут отсчитываться от плановой даты окончания обучения слушателя.'),
                'Validators' => array(
                    'Int'
                ),
                'Value' => 1,
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'endRelative',
            array(
                'Label' => _('День окончания'),
                'Required' => true,
                'Description' => _('Эти дни отсчитываются от даты начала обучения конкретного слушателя по курсу. Если использовать отрицательные значения, дни будут отсчитываться от плановой даты окончания обучения слушателя.'),
                'Validators' => array(
                    'Int'
                ),
                'Value' => 1,
                'Filters' => array('Int')
            )
        );

        // Общие параметры
        $this->addElement($this->getDefaultCheckboxElementName(), 'recommend', array(
            'Label' => _('Нестрогое ограничение'),
            'Description' => _('При установке флажка все фиксированные значения приобретают статус рекомендуемых'),
            'Required' => false,
            'Filters' => array('Int'),
        ));

        // todo: Для 5.0 делаем такое поведение пока не сделаем страницу настройки персональных дат
        /*
        if ($lessonId) {
            $this->addElement($this->getDefaultCheckboxElementName(), 'reassignDates', array(
                'Label' => _('Заново назначить даты занятия всем участникам'),
                'Description' => _('При установке флажка все индивидуальные настройки дат занятий (если таковые есть) будут отменены. Всем участникам данное занятие будет назначено на указанные выше даты.'),
                'Required' => false,
                'checked' => 1,
                'Filters' => array('Int'),
            ));
        }*/

        // Если базовый то скрываем ненужные поля
        if ($subject->isBase()) {

            unset($groupDateArray[HM_Lesson_LessonModel::TIMETYPE_DATES]);
            unset($groupDateArray[HM_Lesson_LessonModel::TIMETYPE_TIMES]);

//            $this->removeElement('beginDate');
//            $this->removeElement('currentDate');
//            $this->removeElement('beginTime');
//            $this->removeElement('endDate');
//            $this->removeElement('endTime');

            $this->addElement('hidden', 'all', array(
                'Required' => false,
                'Value' => true
            ));
        }

        $this->addDisplayGroup(
            array('GroupDate',
                'beginDate',
                'currentDate',
                'beginTime',
                'endDate',
                'endTime',
                'recommend',
                //'reassignDates',
                'beginRelative',
                'endRelative'
            ),
            'DateLessonGroup',
            array('legend' => _('Время проведения'))
        );


        /********* Условия выполнения *********/

        $this->addElement('RadioGroup', 'Condition',
            [
                'Label' => '',
                'Value' => HM_Lesson_LessonModel::CONDITION_NONE,
                'MultiOptions' => HM_Lesson_LessonModel::getConditionTypes(),
                'form' => $this,
                'dependences' => [
                    HM_Lesson_LessonModel::CONDITION_NONE => [],
                    HM_Lesson_LessonModel::CONDITION_PROGRESS => ['cond_progress'],
                    HM_Lesson_LessonModel::CONDITION_AVGBAL => ['cond_avgbal'],
                    HM_Lesson_LessonModel::CONDITION_SUMBAL => ['cond_sumbal'],
                    HM_Lesson_LessonModel::CONDITION_LESSON => ['cond_sheid', 'cond_mark']
                ]
            ]);

        $this->addElement($this->getDefaultTextElementName(), 'cond_progress',
            array(
//            	'Label' => _('Занятие доступно, если процент выполнения занятий >= '),
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'cond_avgbal',
            array(
//            	'Label' => _('Занятие доступно, если средний балл по курсу >= '),
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'cond_sumbal',
            array(
//            	'Label' => _('Занятие доступно, если суммарный балл по курсу >= '),
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array('Int')
            )
        );

        $lessons = array(0 => _('Выберите занятие'));
        $collection = $lessonService->fetchAll($lessonService->quoteInto(
            array(
                'CID = ?', 
                ' AND typeID NOT IN (?)', 
                ' AND SHEID <> ?', 
                ' AND vedomost = ?', 
                ' AND isfree = ?
            '),  array(
                $lesson->CID, 
                array(HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER, HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT, HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER, HM_Event_EventModel::TYPE_POLL), 
                $lesson->SHEID, 
                1, 
                HM_Lesson_LessonModel::MODE_PLAN
            )
        ), 'title');
        if (count($collection)) {
            $lessons = $collection->getList('SHEID', 'title', _('Выберите занятие'));
        }

        $this->addElement($this->getDefaultSelectElementName(), 'cond_sheid', array(
            'id' => 'cond_sheid',
            'Label' => _('занятие'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array('Int'),
            'MultiOptions' => $lessons
        ));

        $this->addElement($this->getDefaultTextElementName(), 'cond_mark',
            array(
                'id' => 'cond_mark',
                'Label'      => _('оценка'),
                'Validators' => array(
                    'Int',
                    array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Оценка должна быть больше нуля"))))
                ),
                'Filters'    => array('Int'),
                'Value'      => 1
            )
        );

        $this->addDisplayGroup(
            array('Condition',
                'cond_progress',
                'cond_avgbal',
                'cond_sumbal',
                'cond_sheid',
                'cond_mark'
            ),
            'ConditionLessonGroup',
            array('legend' => _('Условия запуска'))
        );

        /******************/

        /********* Специфичные настройки *********/

        switch($currentBaseType) {
            case HM_Event_EventModel::TYPE_EMPTY:
                $this->initEmpty();
                break;
            case HM_Event_EventModel::TYPE_TEST:
                $this->initTest();
                break;
            case HM_Event_EventModel::TYPE_POLL:
                $this->initPoll();
                break;
            case HM_Event_EventModel::TYPE_TASK: // @todo
                $this->initTask();
                break;
            case HM_Event_EventModel::TYPE_LECTURE: // DEPRECATED
            case HM_Event_EventModel::TYPE_COURSE:
                $this->initCourse();
                break;
            case HM_Event_EventModel::TYPE_ECLASS:
                $this->initEclass();
                break;
            case HM_Event_EventModel::TYPE_RESOURCE:
                $this->initResource();
                break;
            case HM_Event_EventModel::TYPE_FORUM:
                $this->initForum();
                break;
            default:
                // Создание занятия на основе сервиса взаимодействия
                $activities = HM_Activity_ActivityModel::getActivityServices();
                if (isset($activities[$currentBaseType])) {
                    $activityService = HM_Activity_ActivityModel::getActivityService($currentBaseType);
                    if (strlen($activityService)) {
                        $service = $this->getService($activityService);
                        if ($service instanceof HM_Service_Schedulable_Interface) {
                            $service->onCreateLessonForm($this, 'subject', $subject->subid, '');
                        }
                    }
                }
                break;
        }


        $this->addElement($this->getDefaultSubmitElementName(), 'submit',[
            'label' => _('Сохранить'),
        ]);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к назначению участников'),
                    'url' => $this->getView()->url([
                        'module' => 'subject',
                        'controller' => 'lesson',
                        'action' => 'edit-assign',
                        'subject_id' => $subject->subid,
                    ]),
                ],
                [
                    'label' => _('к редактированию материала'),
                    'url' => $this->getView()->url([
                        'module' => 'subject',
                        'controller' => 'material',
                        'action' => 'edit',
                        'subject_id' => $subject->subid
                    ]) . '?returnUrl='. urlencode($this->getRequest()->getServer('REQUEST_URI'))
                ],
            ]
        ]);

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'edit',
                'subject_id' => $subject->subid,
            ))
        ));

        parent::init(); // required!
	}

    public function initEmpty()
    {
        foreach (['ConditionLessonGroup'] as $displayGroup) {
            $elements = $this->getDisplayGroup($displayGroup)->getElements();
            foreach ($elements as $element) {
                $this->removeElement($element->getName());
            }
            $this->removeDisplayGroup($displayGroup);
        }

        // @todo: похоже это не работает
        if ($submitAndRedirect = $this->getElement('submit_and_redirect')) {
            $urls = $submitAndRedirect->getAttrib('redirectUrls');
            unset($urls['material']);
            $submitAndRedirect->setAttrib('redirectUrls', $urls);
        }
    }

    public function initCourse()
    {
        $scaleId = $this->_lesson->isCustomType() ?  $this->_lesson->getScale() : HM_Lesson_Course_CourseModel::getDefaultScale();
        $description = '';
        switch ($scaleId) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                $description = _('Если данная опция включена, то при получении слушателем статуса "passed" соответствующая оценка выставляется в ведомость (применимо только к модулям в формате SCORM и AICC).');
                break;
            case HM_Scale_ScaleModel::TYPE_TERNARY:
                $description = _('Если данная опция включена, то при получении слушателем статуса "passed" или "failed" соответствующая оценка выставляется в ведомость (применимо только к модулям в формате SCORM и AICC).');
                break;
            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                $description = _('Если данная опция включена, то балл, полученный слушателем за прохождение модуля, автоматически выставляется в ведомость (применимо только к модулям в формате SCORM и AICC).');
                break;
        }

        $currentBaseType = $this->getService('Lesson')->getLessonTool($this->_lesson->typeID);
        if($currentBaseType != HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY) {
            $this->addElement($this->getDefaultCheckboxElementName(), 'formula', array(
                'Label' => _('Автоматически выставлять оценку за занятие'),
                'Description' => $description,
                'required' => false,
                'validators' => array('Int'),
                'filters' => array('int'),
                'value' => 1
            ));

            $this->addDisplayGroup(array(
                'formula',
            ),
                'formulaGroup',
                array('legend' => _('Автоматическое выставление оценки'))
            );
        }

        $this->addElement($this->getDefaultCheckboxElementName(), 'chat_enabled', array(
            'Label' => _('Чат'),
            'Description' => _('К занятию будет подключен чат, которым смогут пользоваться все участники.'),
            'Required' => false,
            'Filters' => array('Int'),
        ));

        $this->addDisplayGroup(
            ['chat_enabled'],
            'ExtraTools',
            ['legend' => _('Дополнительные инструменты')]
        );

    }

    public function initTest()
    {
        $scaleId = $this->_lesson->isCustomType() ?  $this->_lesson->getScale() : HM_Lesson_Test_TestModel::getDefaultScale();
        switch ($scaleId) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                $this->addElement($this->getDefaultTextElementName(), 'threshold', array(
                    'Label' => _('Автоматически выставлять оценку за занятие при достижении порога'),
                    'Description' => _('Пороговое значение (в процентах), при достижении которого оценка "Пройдено" автоматически выставляется в ведомость.'),
                    'validators' => array(
                        'Int',
                        array('GreaterThan', false, array(-1)),
                        array('LessThan', false, array(100))
                    ),
                    'filters' => array('int'),
                ));

                break;
            case HM_Scale_ScaleModel::TYPE_TERNARY:
                $this->addElement($this->getDefaultTextElementName(), 'threshold', array(
                    'Label' => _('Автоматически выставлять оценку за занятие при достижении порога'),
                    'Description' => _('Пороговое значение (в процентах), при достижении которого оценка "Пройдено успешно" автоматически выставляется в ведомость. Если пользователь окончил тестирование, но не достиг порогового значения, выставляется оценка "Пройдено неуспешно".'),
                    'validators' => array(
                        'Int',
                        array('GreaterThan', false, array(-1)),
                        array('LessThan', false, array(100))
                    ),
                    'filters' => array('int'),
                ));

                break;
            case HM_Scale_ScaleModel::TYPE_CONTINUOUS: // @todo: добавить TYPE_DISCRETE; расчет по формуле характерен для TYPE_DISCRETE
                $collection = $this->getService('Formula')->fetchAll(
                    $this->getService('Formula')->quoteInto(
                        array('type = ?', ' AND  (cid = ? OR cid = 0)'),
                        array(HM_Formula_FormulaModel::TYPE_MARK, $this->_subject->subid)
                    ),
                    'name'
                );
                $formulas = $collection->getList('id', 'name', _('Нет'));

                $this->addElement($this->getDefaultSelectElementName(), 'formula', array(
                    'Label' => _('Автоматически выставлять оценку за занятие по формуле'),
                    'required' => false,
                    'validators' => array(
                        'int',
                        array('GreaterThan', false, array(-1))
                    ),
                    'filters' => array('int'),
                    'multiOptions' => $formulas
                ));

                break;
        }

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND (cid = ? OR cid = 0)'),
                array(HM_Formula_FormulaModel::TYPE_GROUP, $this->_subject->subid)
            ),
            'name'
        );

        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement($this->getDefaultSelectElementName(), 'formula_group', array(
            'Label' => _('Автоматически распределять по подгруппам с использованием формулы'),
            'required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'multiOptions' => $formulas
        ));

        $this->addDisplayGroup(array(
            'formula',
            'threshold',
            'formula_group',
        ),
            'formulaGroup',
            array('legend' => _('Формулы'))
        );

        $params = $this->_lesson->getParams();
        $questId = $this->_lesson->material_id ? : $params['module_id'];
        $quest = $this->getService('Quest')->findOne($questId);

        if ($quest) {
            $this->addElement('RadioGroup', 'mode_display', array(
                'Label' => _('Разбиение на страницы'),
                'MultiOptions' => HM_Quest_QuestModel::getDisplayModes(),
                'separator' => '',
                'form' => $this,
                'dependences' => array(
                    HM_Quest_QuestModel::MODE_DISPLAY_BY_CLUSTERS => array(),
                    HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS => array('mode_display_questions'),
                    HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS => array('mode_display_clusters'),
                )
            ));

            $this->addElement($this->getDefaultTextElementName(), 'mode_display_questions', array(
                'Label' => _('Количество вопросов, отображаемых на одной странице'),
                'Description' => _('В этом случае количество страниц будет зависеть от общего количества вопросов и количества вопросов, отображаемых на одной странице.'),
                'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
                'Required'    => true
            ));

            $this->addElement($this->getDefaultTextElementName(), 'mode_display_clusters', array(
                'Label' => _('Количество страниц'),
                'Description' => _('В этом случае количество вопросов, отображаемых на одной странице будет зависеть от общего количества вопросов и количества страниц.'),
                'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
                'Required'    => true
            ));


            $this->addDisplayGroup(array(
                'mode_display',
                'mode_display_questions',
                'mode_display_clusters',
            ),
                'group_display',
                array('legend' => _('Отображение вопросов'))
            );


            $clusters      = array();
            $clusterIds    = array();
            if ($questId) {
                $clusters = $this->getService('QuestCluster')->fetchAll(array('quest_id=?' => $questId))->getList('cluster_id', 'name');
                foreach ($clusters as $clusterId => $clusterName) {
                    $clusterIds[] = 'cluster_limit_'. $clusterId;
                }
            }

            $selectionModes = HM_Quest_QuestModel::getSelectionModes();
            if(!$clusterIds) {
                unset(
                    $selectionModes[HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER],
                    $selectionModes[HM_Quest_QuestModel::MODE_SELECTION_LIMIT_CLUSTER]
                );
            }
            $this->addElement('RadioGroup', 'mode_selection', array(
                'Label' => _('Выборка вопросов'),
                'MultiOptions' => $selectionModes,
                'separator'    => '',
                'form'         => $this,
                'dependences'  => array(
                    HM_Quest_QuestModel::MODE_SELECTION_ALL => array('mode_selection_all_shuffle'),
                    HM_Quest_QuestModel::MODE_SELECTION_LIMIT => array('mode_selection_questions'),
                    HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER => array('mode_selection_questions_cluster'),
                    HM_Quest_QuestModel::MODE_SELECTION_LIMIT_CLUSTER => $clusterIds,
                ),
                'Filters'  => array('Int'),
            ));

            foreach ($clusters as $clusterId => $clusterName) {
                $this->addElement($this->getDefaultTextElementName(), 'cluster_limit_'. $clusterId, array(
                    'Label'    => $clusterName,
                    'Description' => _('Чтобы выбрать все вопросы данного блока, оставьте поле пустым.'),
                    'Required' => false,
                    'Validators'  => array('Int', array('GreaterThan', false, array('min' => -1))),
                ));
            }

            $this->addElement($this->getDefaultTextElementName(), 'mode_selection_questions', array(
                'Label' => _('Количество вопросов, выбранных случайным образом'),
                'Description' => _('Если общее количество вопросов в тесте меньше, чем данный параметр, в таком случае будут выбраны все имеющиеся вопросы.'),
                'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
                'Required'    => true
            ));
            $this->addElement($this->getDefaultTextElementName(), 'mode_selection_questions_cluster', array(
                'Label'       => _('Количество вопросов, выбранных случайным образом'),
                'Description' => _('Если общее количество вопросов в каком-то блоке вопросов меньше, чем данный параметр, в таком случае будут выбраны все имеющиеся вопросы.'),
                'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
                'Required'    => true
            ));

            $this->addElement($this->getDefaultCheckboxElementName(), 'mode_selection_all_shuffle', array(
                'Label' => _('Перемешивать вопросы'),
            ));

            $this->addElement($this->getDefaultTextElementName(), 'limit_attempts', array(
                'Label' => _('Ограничение по количеству попыток'),
                'Description' => _('Чтобы убрать ограничение по попыткам, оставьте поле пустым.'),
                'Required' => false,
                'Value' => '',
                'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            ));

            $this->addElement($this->getDefaultTextElementName(), 'limit_clean', array(
                'Label'       => _('Количество дней, после которых обнуляется счетчик попыток'),
                'Description' => _('Чтобы счетчик не обнулялся, оставьте поле пустым.'),
                'Required'    => false,
                'Value'       => '',
                'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            ));

            $this->addElement($this->getDefaultTextElementName(), 'limit_time', array(
                'Label' => _('Ограничение по времени выполнения, мин.'),
                'Description' => _('Чтобы убрать ограничение по времени, оставьте поле пустым.'),
                'Required' => false,
                'Value' => '',
                'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            ));


            $this->addElement('RadioGroup', 'mode_test_page', array(
                'Label' => _('Переключение между страницами теста'),
                'MultiOptions' => HM_Quest_QuestModel::getPageModes(),
                'separator'    => '',
                'form'         => $this,
                'Required'    => true,
                'Filters'  => array('Int'),
            ));


            $this->addElement($this->getDefaultCheckboxElementName(), 'show_log', array(
                'Label' => _('Разрешить просмотр подробного отчёта пользователем'),
            ));

            $this->addElement($this->getDefaultCheckboxElementName(), 'show_result', array(
                'Label' => _('По окончании отображать результат тестирования'),
            ));

            $this->addElement($this->getDefaultCheckboxElementName(), 'mode_self_test', array(
                'Label' => _('Режим самопроверки'),
            ));

            $elementIds = array(
                'mode_selection',
                'mode_selection_questions',
                'mode_selection_questions_cluster',
                'mode_selection_all_shuffle',
                'limit_attempts',
                'limit_clean',
                'limit_time',
                'mode_test_page',
                'show_result',
                'show_log',
                'mode_self_test'
            );
            foreach ($clusters as $clusterId => $clusterName) {
                $elementIds[] = 'cluster_limit_'. $clusterId;
            };

            $this->addDisplayGroup(
                $elementIds,
                'quest_settings',
                array('legend' => _('Настройки теста'))
            );

            if ($quest) {

                $lessonScope = HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON;
                $subjectScope = HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT;

                if ($this->_lesson->SHEID && $quest->hasScopeSettings($lessonScope, $this->_lesson->SHEID)) {
                    $quest->setScope($lessonScope, $this->_lesson->SHEID);
                } elseif ($quest->hasScopeSettings($subjectScope, $this->_subject->subid)) {
                    $quest->setScope($subjectScope, $this->_subject->subid);
                } else {
                    $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL);
                }

                if ($quest->getSettings()) {
                    $questData = $quest->getSettings()->getData();
                    if ($questData['cluster_limits']) {
                        $clusterLimits = explode(';', $questData['cluster_limits']);
                        for ($i = 0; $i < count($clusterLimits); $i += 2) {
                            $questData['cluster_limit_' . $clusterLimits[$i]] = $clusterLimits[$i + 1];
                        }
                    }

                    if ($questData['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
                        $questData['mode_selection_questions_cluster'] = $questData['mode_selection_questions'];
                        unset($questData['mode_selection_questions']);
                    }

                    $questData['show_result'] = true;
                    $questData['mode_self_test'] = $quest->mode_self_test;
                    $this->populate($questData);
                }

                if (!isset($questData['show_result'])) $questData['show_result'] = true;
                $this->populate($questData);
            }
        }
    }

    public function initPoll()
    {
        $params = $this->_lesson->getParams();
        $questId = $this->_lesson->material_id ? : $params['module_id'];
        $quest = $this->getService('Quest')->findOne($questId);

        if ($quest) {
            if ($quest->scale_id) {
                $this->addElement('hidden',
                    'poll_mode',
                    array(
                        'Required' => false,
                        'Value' => HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE
                    )
                );

                $this->addElement($this->getDefaultSelectElementName(), 'scale_id', array(
                        'Required' => true,
                        'Label' => _('Шкала оценивания'),
                        'multiOptions' => $this->getService('Scale')->fetchAll(array('mode=?' => HM_Scale_ScaleModel::MODE_FEEDBACK), 'scale_id')->getList('scale_id', 'name'),
                        'Validators' => array('Int'),
                        'Filters' => array('Int')
                    )
                );
            }
            else {
                $this->addElement('hidden',
                    'poll_mode',
                    array(
                        'Required' => false,
                        'Value' => HM_Quest_Type_PollModel::QUESTIONS_TYPE_MANUAL
                    )
                );
                $this->addElement('hidden',
                    'scale_id',
                    array(
                        'Required' => false,
                        'Value' => 0
                    )
                );
            }

        } else {
            $this->addElement('RadioGroup', 'poll_mode', array(
                'Label' => _('Оценивание вопросов'),
                'MultiOptions' => HM_Quest_Type_PollModel::getPollQuestionsTypes(),
                'separator' => '',
                'form' => $this,
                'dependences' => array(
                    HM_Quest_Type_PollModel::QUESTIONS_TYPE_MANUAL => array(),
                    HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE => array('scale_id'),
                )
            ));
            $this->addElement($this->getDefaultSelectElementName(), 'scale_id', array(
                    'Label' => _('Шкала оценивания'),
                    'multiOptions' => $this->getService('Scale')->fetchAll(array('mode=?' => HM_Scale_ScaleModel::MODE_FEEDBACK), 'scale_id')->getList('scale_id', 'name', ('Выберите шкалу')),
                    'Validators' => array('Int'),
                    'Filters' => array('Int')
                )
            );
        }

//        $this->addElement($this->getDefaultCheckboxElementName(), 'displaycomment', array(
//                'Required' => false,
//                'Validators' => array(
//                    'Int'),
//                'Filters' => array(
//                    'Int'),
//                'Label' => _('Возможность оставить комментарий'))
//        );

        $this->addElement('hidden',
            'displaycomment',
            array(
                'Required' => false,
            )
        );

        if (!$quest) {
            $this->addDisplayGroup(array(
                'poll_mode',
                'scale_id',
                'displaycomment',
            ),
                'pollGroup',
                array('legend' => _('Настройки опроса'))
            );
        }
    }

    public function initResource()
    {
        // #36606, 5-й комментарий, вырезали специфику "автоматического выставления оценки" для инфоресов

        $this->addElement($this->getDefaultCheckboxElementName(), 'chat_enabled', array(
            'Label' => _('Чат'),
            'Description' => _('К занятию будет подключен чат, которым смогут пользоваться все участники.'),
            'Required' => false,
            'Filters' => array('Int'),
        ));

        $this->addDisplayGroup(
            ['chat_enabled'],
            'ExtraTools',
            ['legend' => _('Дополнительные инструменты')]
        );

    }

    public function initTask()
    {
        $this->addElement($this->getDefaultSelectElementName(), 'assign_type', array(
            'Label' => _('Назначение вариантов задания'),
            'Description' => _('Ручное назначение вариантов происходит на странице "Назначение участников".'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'multiOptions' => HM_Lesson_Task_TaskModel::getAssignTypes(),
            'value' => 0
        ));

        $this->addDisplayGroup(array(
            'assign_type',
        ),
            'group_display',
            array('legend' => _('Настройки задания'))
        );
    }

    public function initEclass(){
        //$this->clearElements();
    }

    public function initForum()
    {
        $params = $this->_lesson->getParams();
        $this->addElement($this->getDefaultCheckboxElementName(), 'is_hidden', array(
            'Label' => _('Включить режим скрытых ответов в теме форума'),
            'Description' => _('Сообщения участников в режиме скрытых ответов видит только автор темы. Сообщения же автора видят все участники.'),
            'Required' => false,
            'Filters' => array('Int'),
            'value' => !empty($params['is_hidden']) ? 1 : 0
        ));

        $this->addDisplayGroup(
            ['is_hidden'],
            'forumGroup',
            ['legend' => _('Настройки форума')]
        );
    }

}
