<?php
class HM_Form_LessonStep2 extends HM_Form_SubForm
{
    private $_event;
    private $_step1;

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('lessonStep2');
 
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($this->getParam('subject_id', 0)));

        $this->addElement('hidden', 'prevSubForm', array(
            'Required' => false,
            'Value' => 'step1'
        ));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)), null, true)
        ));

        $this->addElement('hidden', 'redirectUrl', array(
            'Required' => false
        ));

        $this->addElement('hidden', 'lesson_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $session = $this->getSession();
        $this->_step1 = $session['step1'];

        $eventId = $session['step1']['event_id'];
        if ($eventId < 0) {
            $event = $this->getService('Event')->getOne(
                $this->getService('Event')->find(-$eventId)
            );

            if ($event) {
                $eventId = $event->tool;
                $this->_event = $event;
            }
        }

        switch($eventId) {
            case HM_Event_EventModel::TYPE_EMPTY:
                $this->clearElements();
                return;
                break;
            case HM_Event_EventModel::TYPE_LECTURE:
                $this->initLecture();
                break;
            case HM_Event_EventModel::TYPE_EXERCISE:
            case HM_Event_EventModel::TYPE_TEST:
                $this->initTest();
                break;
            case HM_Event_EventModel::TYPE_POLL:
                $this->initPoll();
                break;
            case HM_Event_EventModel::TYPE_TASK:
                $this->initTask();
                break;
            case HM_Event_EventModel::TYPE_COURSE:
                $this->initCourse();
                break;
            case HM_Event_EventModel::TYPE_WEBINAR:
                $this->initWebinar();
                break;
            case HM_Event_EventModel::TYPE_ECLASS:
                $this->initEclass();
                break;
            case HM_Event_EventModel::TYPE_RESOURCE:
                $this->initResource();
                break;
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                $this->initDeanPoll();
                break;
            default:
                // Создание занятия на основе сервиса взаимодействия
                $activities = HM_Activity_ActivityModel::getActivityServices();
                if (isset($activities[$session['step1']['event_id']])) {
                    $activityService = HM_Activity_ActivityModel::getActivityService($session['step1']['event_id']);
                    if (strlen($activityService)) {
                        $service = $this->getService($activityService);
                        if ($service instanceof HM_Service_Schedulable_Interface) {
                            $service->onCreateLessonForm($this, 'subject', $session['step1']['subject_id'],$session['step1']['title']);
                        }
                    }
                }
                break;
        }

        if(count($this->getElements()) == 0) {
            return;
        }

        $elements = array();

        foreach($this->getElements() as $element) {
            $elements[] = $element->getName();
        }


        if (
            !$this->getDisplayGroup('LessonGroup') &&
            $eventId != HM_Event_EventModel::TYPE_TEST &&
            $eventId != HM_Event_EventModel::TYPE_POLL &&
            $eventId != HM_Event_EventModel::TYPE_TASK &&
            $eventId != HM_Event_EventModel::TYPE_COURSE
        ) {
            $this->addDisplayGroup(
                $elements,
                'LessonGroup',
                array('legend' => _('Предмет занятия'))
            );
        }

        if (in_array($session['step1']['event_id'], array(HM_Event_EventModel::TYPE_COURSE, HM_Event_EventModel::TYPE_RESOURCE))) {
            $this->addElement(
                'multiCheckbox',
                'activities',
                array(
                    'separator' => '<br/><br/>',
                    'Required' => false,
                    'Label' => '',
                    'MultiOptions' => HM_Activity_ActivityModel::getLessonActivities()
                )
            );

            $this->addDisplayGroup(
                array('activities'),
                'LessonGroupActivities',
                array('legend' => _('Используемые сервисы взаимодействия'))
            );


        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Далее')));

        parent::init(); // required!
    }

    public function initCourse()
    {
        $courses = array(_('Выберите учебный модуль'));
        $collection = $this->getService('Subject')->getCourses($this->getParam('subject_id', 0));
        if (count($collection)) {
            $courses = $collection->getList('CID', 'Title', _('Выберите учебный модуль'));
        }

        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Учебный модуль'),
            'Required' => true,
            'Validators' => array(
                'Int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'Filters' => array('Int'),
            'MultiOptions' => $courses,
            'OptionType' => HM_Event_EventModel::TYPE_COURSE,
        ));
        
        $this->addDisplayGroup(
            array('module'),
            'LessonGroup',
            array('legend' => _('Предмет занятия'))
        );        

        if ($this->_step1['vedomost']) {
            $scaleId = isset($this->_event) ?  $this->_event->scale_id : HM_Lesson_Course_CourseModel::getDefaultScale();
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

            if($this->_event->event_id!=HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY) {
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

        }
    }

    public function initLecture()
    {
        $this->addElement($this->getDefaultTreeSelectElementName(), 'module', array(
            'label' => _('Материал'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(0))
            ),
            'filters' => array('int'),
            'params' => array(
                'remoteUrl' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'ajax', 'action' => 'modules-list'))
            )
        ));
        
        $this->addDisplayGroup(
            array('module'),
            'LessonGroup',
            array('legend' => _('Предмет занятия'))
        );
        
        if ($this->_step1['vedomost']) {
            $scaleId = isset($this->_event) ?  $this->_event->scale_id : HM_Lesson_Course_CourseModel::getDefaultScale();
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
    }

    public function initTest()
    {
        //#20525 фикс подгрузки настроек тестов, а то вешался на 'module' у любого типа занятия
        $loadTestSettingsUrl = $this->getView()->url(array('quest_id'=>null));
        $loadTestSettingsScript =<<<SCRIPT
            $(document).ready(function() {
                if($("#step2").length<1) return;
                $("#step2").delegate('#module', 'change', function(e) {
                var \$this = $(this);
                window.location.href = '{$loadTestSettingsUrl}/quest_id/' + \$this.val();
                })
            })
SCRIPT;
        $this->getView()->inlineScript()->appendScript($loadTestSettingsScript);

        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Quest')->fetchAllDependenceJoinInner(
            'SubjectAssign',
            $this->getService('Quest')->quoteInto(
                array('SubjectAssign.subject_id = ?', ' AND self.type = ?'),
                array($subjectId, HM_Quest_QuestModel::TYPE_TEST)
            )
        );

        $testsExt = $collection->getList('quest_id', 'name');

        $collectionOwn = $this->getService('Quest')->fetchAll(
            $this->getService('Quest')->quoteInto(
                array('subject_id = ?', ' AND type = ?'),
                array($subjectId, HM_Quest_QuestModel::TYPE_TEST)));
        $testsOwn = $collectionOwn->getList('quest_id', 'name', _('Выберите тест'));

        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Тест'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $testsOwn + $testsExt,
            'OptionType' => HM_Event_EventModel::TYPE_TEST
        ));

        if ($this->_step1['vedomost']) {
            
            $scaleId = isset($this->_event) ?  $this->_event->scale_id : HM_Lesson_Test_TestModel::getDefaultScale();
            $description = '';
            switch ($scaleId) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    $description = _('Если данная опция включена, то при достижении слушателем порогового значения в ведомость выставляется оценка "Пройдено".');
                    
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
                    $description = _('Если данная опция включена, то в зависимости от результата пользователя и порогового значения соответствующая оценка ("Пройдено успешно" или "Пройдено неуспешно") выставляется в ведомость.');
                    
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
                    $description = _('Если данная опция включена, то оценка автоматически рассчитывается по формуле.');
                    
                    $collection = $this->getService('Formula')->fetchAll(
                        $this->getService('Formula')->quoteInto(
                            array('type = ?', ' AND  (cid = ? OR cid = 0)'),
                            array(HM_Formula_FormulaModel::TYPE_MARK, $subjectId)
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
        } 

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND (cid = ? OR cid = 0)'),
                array(HM_Formula_FormulaModel::TYPE_GROUP, $subjectId)
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

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND  (cid = ? OR cid = 0)'),
                array(HM_Formula_FormulaModel::TYPE_PENALTY, $subjectId)
            ),
            'name'
        );

        $this->addDisplayGroup(array(
            	'module',
            ),
            'subject',
            array('legend' => _('Предмет занятия'))
        );

        $this->addDisplayGroup(array(
                'formula',
                'threshold',                
                'formula_group',
            ),
            'formulaGroup',
            array('legend' => _('Формулы'))
        );

        $lessonId  = (int) $this->getParam('lesson_id',  0);
        $questId   = $this->getParam('quest_id',   null);

        if ($questId === null) {
            $session = $this->getSession();
            if (!empty($session['step2'])) {
                $questId = $session['step2']['module'];
            } elseif ($lessonId) {
                $lesson = $this->getService('Lesson')->find($lessonId)->current();
                $questId = $lesson->getModuleId();
            }
        }

        if ($questId && (isset($testsOwn[$questId]) || isset($testsExt[$questId]))) {
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

            $this->addElement('RadioGroup', 'mode_selection', array(
                'Label' => _('Выборка вопросов'),
                'MultiOptions' => HM_Quest_QuestModel::getSelectionModes(),
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

            if (!empty($questId)) {
                /** @var HM_Quest_QuestService $questService */
                $questService = $this->getService('Quest');
                /** @var HM_Quest_QuestModel $quest */
                $quest = $questService->find($questId)->current();

                $lessonScope = HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON;
                $subjectScope = HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT;

                if ($lessonId && $quest->hasScopeSettings($lessonScope, $lessonId)) {
                    $quest->setScope($lessonScope, $lessonId);
                } elseif ($quest->hasScopeSettings($subjectScope, $subjectId)) {
                    $quest->setScope($subjectScope, $subjectId);
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
        //#20525 фикс подгрузки настроек тестов, а то вешался на 'module' у любого типа занятия
        $loadTestSettingsUrl = $this->getView()->url(array('quest_id'=>null));
        $loadTestSettingsScript =<<<SCRIPT
            $(document).ready(function() {
                if($("#step2").length<1) return;
                $("#step2").delegate('#module', 'change', function(e) {
                var \$this = $(this);
                window.location.href = '{$loadTestSettingsUrl}/quest_id/' + \$this.val();
                })
            })
SCRIPT;
        $this->getView()->inlineScript()->appendScript($loadTestSettingsScript);

        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Quest')->fetchAllDependenceJoinInner(
            'SubjectAssign',
            $this->getService('Quest')->quoteInto(
                array('SubjectAssign.subject_id = ?', ' AND self.type = ?'),
                array($subjectId, HM_Quest_QuestModel::TYPE_POLL)
            )
        );

        $testsExt = $collection->getList('quest_id', 'name');

        $collectionOwn = $this->getService('Quest')->fetchAll(
            $this->getService('Quest')->quoteInto(
                array('subject_id = ?', ' AND type = ?'),
                array($subjectId, HM_Quest_QuestModel::TYPE_POLL)));
        $testsOwn = $collectionOwn->getList('quest_id', 'name', _('Выберите опрос'));

        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Опрос'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $testsOwn + $testsExt,
            'OptionType' => HM_Event_EventModel::TYPE_POLL
        ));


        $this->addDisplayGroup(array(
                'module',
            ),
            'lessonGroup',
            array('legend' => _('Предмет занятия'))
        );

        $lessonId  = (int) $this->getParam('lesson_id',  0);
        $questId   = $this->getParam('quest_id',   null);

        if ($questId === null) {
            $session = $this->getSession();
            if (!empty($session['step2'])) {
                $questId = $session['step2']['module'];
            } elseif ($lessonId) {
                $lesson = $this->getService('Lesson')->find($lessonId)->current();
                $questId = $lesson->getModuleId();
            }
        }

        $quest = $this->getService('Quest')->getOne($this->getService('Quest')->find($questId));
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


        $this->addElement($this->getDefaultCheckboxElementName(), 'displaycomment', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Возможность оставить комментарий'))
        );

        $this->addDisplayGroup(array(
                'poll_mode',
                'scale_id',
                'displaycomment',
            ),
            'pollGroup',
            array('legend' => _('Настройки опроса'))
        );
    }

    public function initEclass(){
        $this->clearElements();
    }
    
    public function initWebinar()
    {

        $subjectId = (int) $this->getParam('subject_id', 0);

        $webinars = $this->getService('Webinar')->fetchAll(array('subject_id = ?' => $subjectId));

        if($webinars){
            $res = $webinars->getList('webinar_id', 'name', _('Выберите материалы вебинара'));
        }

        $keys = array_keys($res);

        $result = $this->getService('Ppt2Swf')->fetchAll(
            array(
            	'webinar_id IN (?)' => $keys
            )
        );

        $exclude = array();
        foreach($result as $val){
            if($val->status != HM_Ppt2swf_Ppt2swfModel::STATUS_READY){

                $exclude[] = $val->webinar_id;
            }
        }
        foreach($exclude as $value){
            unset($res[$value]);
        }
        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Материалы вебинара'),
            'required' => false, // делаем необязательным ,поскольку новый вебинар сам умеет загружать материалы
            'validators' => array(
                'int',
                //array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $res,
            'OptionType' => HM_Event_EventModel::TYPE_WEBINAR,
        ));


    }

    public function initResource()
    {
        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Resource')->fetchAllDependenceJoinInner(
            'SubjectAssign',
            $this->getService('Resource')->quoteInto('SubjectAssign.subject_id = ?', $subjectId)
        );
        $resources = $collection->getList('resource_id', 'title', _('Выберите информационный ресурс'));
        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Информационный ресурс'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $resources,
            'OptionType' => HM_Event_EventModel::TYPE_RESOURCE
        ));
        
        $this->addDisplayGroup(
            array('module'),
            'LessonGroup',
            array('legend' => _('Предмет занятия'))
        );        

        if ($this->_step1['vedomost']) {
            $scaleId = isset($this->_event) ?  $this->_event->scale_id : HM_Lesson_Resource_ResourceModel::getDefaultScale();
            $description = '';
            $disabled = false;
            switch ($scaleId) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    $description = _('Если данная опция включена, то факт обращения слушателя к данному ресурсу автоматически фиксируется в ведомости.');
                    break;
                break;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:                
                    $description = _('Оценка по данной шкале не может быть выставлена автоматически.');
                    $disabled = true;
                    break;
            }
            $this->addElement($this->getDefaultCheckboxElementName(), 'formula', array(
                'Label' => _('Автоматически выставлять оценку за занятие'),
                'Description' => $description,
                'disabled' => $disabled ? true : null,
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
        
    }



     public function initTask()
    {
        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Task')->fetchAllDependenceJoinInner(
            'SubjectAssign',
            $this->getService('Task')->quoteInto(array('SubjectAssign.subject_id = ?', ' AND questions > ?'), array($subjectId, 0))
        );

        $tests = $collection->getList('task_id', 'title', _('Выберите задание'));
        /*
        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Course')->fetchAllDependenceJoinInner(
            'SubjectAssign',
            $this->getService('Course')->quoteInto('subject_id = ?', $subjectId)
        );

        $courses = $collection->getList('CID', 'Title');

        $tests = array(0 => _('Выберите тест'));
        if (count($courses)) {
            $collection = $this->getService('Test')->fetchAll(
                $this->getService('Test')->quoteInto('cid IN (?)', array_keys($courses)),
                'title'
            );
            $tests = $collection->getList('tid', 'title', _('Выберите тест'));
        }
         *
         */

        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Задание'),
            'description' => _('В списке отображаются только задания с вариантами.'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $tests,
            'OptionType' => HM_Event_EventModel::TYPE_TASK
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'assign_type', array(
            'Label' => _('Назначение вариантов задания'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'multiOptions' => HM_Lesson_Task_TaskModel::getAssignTypes(),
            'value' => 0
        ));

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND cid = ?'),
                array(HM_Formula_FormulaModel::TYPE_MARK, $subjectId)
            ),
            'name'
        );

        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement('hidden', 'formula', array(
            'required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0
        ));

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND cid = ?'),
                array(HM_Formula_FormulaModel::TYPE_GROUP, $subjectId)
            ),
            'name'
        );

        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement('hidden', 'formula_group', array(
            'required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0
        ));

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND cid = ?'),
                array(HM_Formula_FormulaModel::TYPE_PENALTY, $subjectId)
            ),
            'name'
        );

        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement('hidden', 'formula_penalty', array(
            'Label' => _('Штраф за несвоевременное выполнение задания'),
            'required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'multiOptions' => $formulas,
            'Value' => 0
        ));

        $this->addElement('hidden', 'mode', array(
            'required' => true,
            'validators' => array('Int'),
            'filters' => array('int'),
            'multiOptions' => HM_Test_TestModel::getModes(),
            'value' => HM_Test_TestModel::MODE_FORWARD_ONLY,
        ));

        $this->addElement('hidden', 'questions_by_theme');

        $this->addElement('hidden', 'questions', array(
            'required' => false,
			'value' => 0,
        ));

        $this->addElement('hidden', 'lim', array(
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 1
        ));

        $this->addElement('hidden', 'qty', array(
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(0))
            ),
            'filters' => array('int'),
            'Value' => 1
        ));

        $this->addElement('hidden', 'startlimit', array(
			'Label' => _('Сколько попыток имеет слушатель на выполнение задания'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
            'Description' => _('0 - не ограничено')
        ));

        $this->addElement('hidden', 'limitclean', array(
        	'Label' => _('Через сколько дней обнулять счетчик попыток'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
            'Description' => _('0 - никогда не сбрасывать')
        ));

        $this->addElement('hidden', 'timelimit', array(
        	'Label' => _('Сколько минут имеет слушатель на выполнение задания'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
			'Description' => _('0 - не ограничено')
        ));

        $this->addElement('hidden', 'random', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
        	'multiOptions' => HM_Test_TestModel::getTaskVariantAssign(),
        	'value' => 0
        ));

        $this->addElement('hidden', 'endres', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $this->addElement('hidden', 'skip', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'Value' => 0
        ));

        $this->addElement('hidden', 'allow_view_log', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'Value' => 0
        ));

/*        $this->addElement($this->getDefaultWysiwygElementName(), 'comments', array(
            'Label' => _('Комментарий к заданию'),
            'required' => false,
            'validators' => array(),
            'filters' => array()
        ));*/


        $this->addDisplayGroup(array(
            	'module',
                'assign_type'
            ),
            'subject',
            array('legend' => _('Учебный материал'))
        );

       /* $this->addDisplayGroup(array(
                'formula',
                'formula_group',
                'formula_penalty'
            ),
            'formulaGroup',
            array('legend' => _('Формулы'))
        );*/

        /*$this->addDisplayGroup(array(
                'lim',
                'random',
                'questions',
            ),
            'questionSelect',
            array('legend' => _('Назначение вариантов задания'))
        );*/

        /*$this->addDisplayGroup(array(
                'qty',
        		'startlimit',
        		'mode',
                'skip',
        		'limitclean',
                'timelimit',
                'endres',
                'allow_view_log',
            ),
            'progress',
            array('legend' => _('Режим выполнения'))
        );*/
    }


    public function initDeanPoll()
    {

        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Poll')->fetchAll(array('location = ?' => HM_Poll_PollModel::LOCALE_TYPE_GLOBAL));
        $tests = $collection->getList('quiz_id', 'title', _('Выберите опрос'));

        /*
        $subjectId = (int) $this->getParam('subject_id', 0);
        $collection = $this->getService('Course')->fetchAllDependenceJoinInner(
            'SubjectAssign',
            $this->getService('Course')->quoteInto('subject_id = ?', $subjectId)
        );

        $courses = $collection->getList('CID', 'Title');

        $tests = array(0 => _('Выберите тест'));
        if (count($courses)) {
            $collection = $this->getService('Test')->fetchAll(
                $this->getService('Test')->quoteInto('cid IN (?)', array_keys($courses)),
                'title'
            );
            $tests = $collection->getList('tid', 'title', _('Выберите тест'));
        }
         *
         */

        $this->addElement($this->getDefaultSelectElementName(), 'module', array(
            'Label' => _('Опрос'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $tests
        ));

        $this->addDisplayGroup(
            array(
                'prevSubForm',
                'cancelUrl',
                'lesson_id',
                'subject_id',
                'module'
            ),
            'LessonGroup',
            array('legend' => _('Инструмент обучения'))
        );

        $this->addElement('hidden', 'mode', array(
            'Value' => 0,
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            //'multiOptions' => HM_Test_TestModel::getModes()
        ));

/*        $this->addElement('hidden', 'questions_by_theme');

        $this->addElement('ajaxRadioGroup', 'questions', array(
            'Label' => _('Способ выбора вопросов'),
            'required' => false,
            'multiOptions' => HM_Test_TestModel::getQuestionsByThemes(),
            'form' => $this,
            'dependences' => array(
                HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED =>
                        $this->getView()->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'themes', 'test_id' => ''))
                        ."'+$('#module').val()
                "
            )
        ));
*/
        $this->addElement('hidden', 'lim', array(
            'required' => false,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0
        ));

        $this->addElement($this->getDefaultTextElementName(), 'qty', array(
            'Label' => _('Сколько вопросов выводить одновременно на странице'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(0))
            ),
            'filters' => array('int'),
            'Value' => 1
        ));

        $this->addElement('hidden', 'startlimit', array(
            'required' => false,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 1,
        ));

        $this->addElement('hidden', 'limitclean', array(
            'required' => false,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
        ));

        $this->addElement('hidden', 'timelimit', array(
            'required' => false,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
        ));

        $this->addElement('hidden', 'random', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $this->addElement('hidden', 'questres', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'Value' => 0
        ));

        $this->addElement('hidden', 'showurl', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $this->addElement('hidden', 'endres', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $this->addElement('hidden', 'skip', array(
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'allow_view_log', array(
            'Label' => _('Разрешить просмотр подробного отчета слушателем'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'separator' => ' ',
            'value' => 1
        ));
/*
        $this->addElement($this->getDefaultWysiwygElementName(), 'comments', array(
            'Label' => _('Комментарий к заданию'),
            'required' => false,
            'validators' => array(

            ),
            'filters' => array(

            )
        ));*/
        $this->addDisplayGroup(
            array(
                'mode',
                'lim',
                'qty',
                'startlimit',
                'limitclean',
                'timelimit',
                'random',
                'questres',
                'showurl',
                'endres',
                'skip',
                'allow_view_log'
            ),
            'LessonGroup2',
            array('legend' => _('Режим отображения'))
        );
    }

    public function getElementDecorators($alias, $first = 'ViewHelper'){
            if (in_array($alias, array('allow_view_log', 'random', 'endres', 'skip'))) {
            return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );
        } elseif ($alias == 'module') {
            return array (
                array($first),
                array('RedErrors'),
                array('AddOption'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt')),
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }
}
