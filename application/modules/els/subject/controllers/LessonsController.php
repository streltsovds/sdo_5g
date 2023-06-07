<?php
class Subject_LessonsController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    protected $_formName      = 'HM_Form_Generate_Lesson';
    protected $_module        = 'subject';
    protected $_controller    = 'lessons';
    protected $generatedCount = 0;
    protected $formData       = [
        'GroupDate'           => HM_Lesson_LessonModel::TIMETYPE_FREE,
        'Condition'           => HM_Lesson_LessonModel::CONDITION_NONE,
        'isfree'              => HM_Lesson_LessonModel::MODE_PLAN,
        'students'            => [],
        'teacher'             => 0,
        'moderator'           => 0,
        'gid'                 => 0,
        'formula'             => 0,
        'formula_group'       => 0,
        'formula_penalty'     => 0,
        'recommend'           => 1,
        'all'                 => 1,
        'beginDate'           => '',
        'endDate'             => '',
        'currentDate'         => '',
        'beginTime'           => '',
        'endTime'             => '',
        'beginRelative'       => '',
        'endRelative'         => '',
        'cond_sheid'          => '',
        'cond_mark'           => '',
        'cond_progress'       => '',
        'cond_avgbal'         => '',
        'cond_sumbal'         => ''
    ];

    protected $_importManagerClass = 'HM_Lesson_Import_Manager';
    private   $_importService      = null;

    public function init()
    {
        parent::init();

        $this->view->replaceSidebar('subject', 'subject-extras', [
            'model' => $this->_subject,
            'order' => 100, // после Subject
        ]);
    }

    /*
     *  План занятий в виде списка для слушателя
     */
    public function indexAction()
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $currentUserId = $userService->getCurrentUserId();
        $isEndUser = $this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ENDUSER]);

        $lessonAssignService = $this->getService('LessonAssign');

        $select = $this->getService('Section')->getSelect();
        $select->from(
            array('s' => 'sections'),
            array('s.section_id', 's.name')
        )
            ->joinInner(
                array('sch' => 'schedule'),
                's.section_id = sch.section_id',
                array('sch.SHEID')
            )
            ->where($this->getService('Section')->quoteInto(
                array(
                    'sch.CID = ? AND ',
                    'sch.typeID NOT IN (?) AND ',
                    'sch.isfree = ?'
                ),
                array(
                    $this->_subjectId,
                    array_keys(HM_Event_EventModel::getExcludedTypes()),
                    HM_Lesson_LessonModel::MODE_PLAN
                )
            ))
            ->order(array('s.order', 'sch.order'));

        $sectionLessons = $select->query()->fetchAll();

        $sections = array();
        if (count($sectionLessons)) {
            $lessonsIds = array_column(array_values($sectionLessons), 'SHEID');

            $lessonAssigns = $lessonAssignService->fetchAllDependenceJoinInner(
                'Lesson',
                $lessonAssignService->quoteInto(
                    array(
                        'self.MID = ? AND ',
                        'Lesson.SHEID IN (?)',
                    ),
                    array(
                        $currentUserId,
                        $lessonsIds
                    )
                )
            );

            if (count($lessonAssigns)) {
                $lessonsAsgn = array();
                foreach ($lessonAssigns as $la) {
                    $lessonsAsgn[$la->SHEID] = $la;
                }

                foreach ($sectionLessons as $sl) {

                    if (array_key_exists($sl['SHEID'], $lessonsAsgn)) {
                        if (!array_key_exists($sl['section_id'], $sections)) {
                            $sections[$sl['section_id']] = array(
                                'section_id' => $sl['section_id'],
                                'name' => $sl['name'],
                                'deleteUrl' => $this->view->url(array(
                                    'module' => 'subject',
                                    'controller' => 'section',
                                    'action' => 'delete',
                                    'section_id' => $sl['section_id']
                                )),
                                'expanded' => false
                            );
                        }

                        $sections[$sl['section_id']]['lessonAssigns'][] = $lessonsAsgn[$sl['SHEID']];
                    }
                }

                $expanded = false;
                foreach ($sections as &$s) {
                    $s = $this->indexPlainify($s, $this->view);

                    foreach ($s['lessonAssigns'] as $v) {
                        if (!$expanded && $v['lessonScore']['score'] === HM_Scale_Value_ValueModel::VALUE_NA) {
                            $expanded = $s['expanded'] = true;
                        }
                    }
                }

                $sections = array_values($sections);
            }
        }

        if ($isEndUser) {
            $this->view->assign([
                'currentUserId' => $currentUserId,
                'subjectMark' => $this->getSubjectMark($currentUserId),
                'lessonAssigns' => $this->getLessonAssigns($currentUserId),
                'sections' => $sections,
                'subjectProgress' => $this->getSubjectProgress($currentUserId),
                'subjectRoughProgress' => $this->getSubjectRoughProgress($currentUserId),
                'subjectIsAccessible' => $this->getSubjectIsAccessible($currentUserId)
            ]);
        } else {
            $this->_redirector->gotoSimple(
                'edit',
                $this->_controller,
                $this->_module,
                ['subject_id' => $this->_subjectId]);
        }
    }

    protected function getSubjectMark($currentUserId)
    {
        // оценка за курс
        /** @var HM_Subject_Mark_MarkService $subjectMarkService */
        $subjectMarkService = $this->getService('SubjectMark');
        $collection = $subjectMarkService->fetchAll(
            $subjectMarkService->quoteInto(
                array('cid = ?', ' AND mid = ?'),
                array($this->_subjectId, $currentUserId))
        );
        $subjectMark = count($collection) ? $this->getOne($collection)->mark : HM_Scale_Value_ValueModel::VALUE_NA;

        return $subjectMark;
    }

    protected function getLessonAssigns($currentUserId)
    {
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonAssignService = $this->getService('LessonAssign');

        $where = $lessonAssignService->quoteInto(array(
            'self.MID = ? AND ',
            'Lesson.CID = ? AND ',
            'Lesson.typeID NOT IN (?) AND ',
            'Lesson.isfree = ? AND ', // @deprecated, только для обратной совместимости
        ), array(
            $currentUserId,
            $this->_subjectId,
            array_keys(HM_Event_EventModel::getExcludedTypes()),
            HM_Lesson_LessonModel::MODE_PLAN,
        )) . '(Lesson.section_id = 0 OR Lesson.section_id IS NULL)';

        $lessonAssigns = $lessonAssignService->fetchAllDependenceJoinInner(
            'Lesson',
            $where
        );

        $lessonIds = $lessonAssigns->getList('SHEID');
        if (count($lessonIds)) {

            // кэш для отображения условий занятий
            $lessonTitles = array();
            foreach ($lessonAssigns as $lessonAssign) {
                $lesson = $lessonAssign->getLesson();
                $lessonTitles[$lesson->SHEID] = $lesson->title;
            }

            // кэш для отображения преподов
            $collection = $this->getService('Lesson')->fetchAllDependence('Teacher', array('SHEID IN (?)' => $lessonIds));
            $teacherUsers = $collection->getList('SHEID', 'teacher');

            // кэш для подробных настроек тестов
            // как минимум, для отображения/неотображения ссылок на подробный отчёт
            $questSettings = array();
            $collection = $this->getService('QuestSettings')->fetchAll(array(
                'scope_id IN (?)' => $lessonIds,
                'scope_type = ?' => HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON,
            ));
            foreach ($collection as $item) $questSettings[$item->scope_id] = $item->getValues();

            // кэш для истории оценок
            $markHistoryService = $this->getService('LessonAssignMarkHistory');
            $markHistory = $markHistoryService->getMarkHistory($this->_subjectId, $currentUserId);
        }

        $lessonAssigns
            ->addCache('lessonId2Title', $lessonTitles)
            ->addCache('lessonId2TeacherUser', $teacherUsers)
            ->addCache('lessonId2QuestSettings', $questSettings)
            ->addCache('lessonId2MarkHistory', $markHistory)
            ->init() // important!
            ->sort(function($item1, $item2) {
                return ($item1->getLesson()->order < $item2->getLesson()->order) ? -1 : 1;
            });

        return $lessonAssigns;
    }

    protected function getSubjectProgress($currentUserId)
    {
        // @todo: оно не работает, перепутаны $lessons и $lessonAssigns
        $subjectProgress = 0;// $lessonService->countPercents($lessonAssigns, $currentUserId);

        return $subjectProgress;
    }

    protected function getSubjectRoughProgress($currentUserId)
    {
        /* прогресс по курсу */
        $lessonService = $this->getService('Lesson');
        $subjectRoughProgress = $lessonService->countPercentsAllSubjects($currentUserId, array($this->_subjectId));

        return $subjectRoughProgress[$this->_subjectId];
    }

    protected function getSubjectIsAccessible($currentUserId)
    {
        /** @var HM_Subject_User_UserModel $subjectUser */
        $subjectUser = $this->getService('SubjectUser')->getOne($this->getService('SubjectUser')->fetchAllDependence('Subject', [
            'user_id = ?'    => $currentUserId,
            'subject_id = ?' => $this->_subjectId,
        ]))->init();

        return $subjectUser ? $subjectUser->isSubjectAccessible() : false;
    }

    public function indexPlainify($data, $view = null)
    {
        $lessonAssignPlain = [];
        /** @var HM_Lesson_Assign_AssignModel $lessonAssign */
        foreach ($data['lessonAssigns'] as $lessonAssign) {

            /** @var HM_Lesson_LessonModel $lesson */
            $lesson = $lessonAssign->getLesson();

            $lessonAssignPlain[] = [
                'lessonId' => $lesson->SHEID,
                'lessonTitle' => $lesson->getName(),
                'lessonType' => HM_Event_EventModel::getTypeTitle($lesson),
                'lessonDescription' => trim($lesson->getDescription()),
                'lessonDate' => $lessonAssign->getBeginEnd(),
                'lessonCondition' => $lessonAssign->getCachedLaunchCondition(),
                'lessonComment' => $lessonAssign->getComment(),
                'lessonScore' => [
                    'score' => $lessonAssign->getScore(),
                    'scale_id' => $lesson->getScale(),
                ],
                'lessonScoreHistory' => $lessonAssign->getCachedMarkHistory(),

                'isNewWindow' => $lesson->isNewWindow(),
                'isNotStrict' => $lesson->recommend,
                'isPenalty' => (bool)$lesson->getFormulaPenaltyId(),
                'isScoreable' => (bool) $lesson->vedomost,
                'isResultURLEnabled' => $lessonAssign->getCachedLogEnabled() && $lesson->getResultsUrl() && $lesson->isResultInTable(),
                'isPassed' => $lessonAssign->getScore() != HM_Scale_Value_ValueModel::VALUE_NA,
                'isUnaccessible' => $lesson->isUnaccessible(),

                'iconUrl' => $lesson->getUserIcon() ? $lesson->getUserIcon() : $lesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM),
                'executeUrl' => $view->url(Zend_Registry::get('serviceContainer')->getService('Lesson')->getExecuteUrl($lesson->SHEID, $lesson->CID)),
                'resultUrl' => $lesson->getResultsUrl(),
            ];
        }

        $data['lessonAssigns'] = $lessonAssignPlain;
        return $data;
    }

    /*
     *  План занятий в виде списка для менеджера
     */
    public function editAction()
    {
        if(!$this->getService('User')->isEndUser()) {
            $this->getService('Storage')->createSubjectDirs($this->_subjectId);
        }
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');
        $lessons = $lessonService->fetchAllDependence(
            'Teacher',
            array(
                'CID = ?' => $this->_subjectId,
                'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN, // @deprecated, только для обратной совместимости
                '(section_id = 0 OR section_id IS NULL)'
            ),
            $lessonService->getLessonOrderFields()
        );

        $lessonIds = $lessons->getList('SHEID');
        $lessonTitles = [];
        if (count($lessonIds)) {
            // кэш для отображения условий занятий
            foreach ($lessons as $lesson) {
                $lessonTitles[$lesson->SHEID] = $lesson->title;
            }
        }

        // important!
        $lessons
            ->addCache('lessonId2Title', $lessonTitles)
            ->init();

        $sectionLessons = $this->getService('Section')->fetchAllDependence(
            'Lesson',
            array(
                'subject_id = ?'    => $this->_subjectId,
            ),
            'order'
        );

        $sections = array();
        if (count($sectionLessons)) {
            foreach ($sectionLessons as $section) {
                $_section = array(
                    'section_id' => $section->section_id,
                    'name' => $section->name,
                    'deleteUrl' => $this->view->url(array(
                        'module' => 'subject',
                        'controller' => 'section',
                        'action' => 'delete',
                        'section_id' => $section->section_id
                    )),
                    'expanded' => false
                );

                if ($section->lessons && count($section->lessons)) {
                    foreach ($section->lessons as $lesson) {
                        $_section['lessons'][] = $lesson;
                    }

                    usort($_section['lessons'], function ($a, $b) {
                        return strcmp($a->order, $b->order);
                    });

                    $_section = self::editPlainify($_section, $this->view);
                } else {
                    $_section['lessons'] = array();
                }
                $sections[] = $_section;
            }
        }

        $this->view->assign(array(
            'lessons' => $lessons,
            'sections' => $sections,
            'folderHash' => Zend_Registry::get('config')->elFinder->root_hash,
        ));
    }

    public static function editPlainify($data, $view)
    {
        $lessonsPlain = [];
        $returnUrl = urlencode($view->getRequest()->getServer('REQUEST_URI'));

        /** @var HM_Lesson_LessonModel $lesson */
        foreach ($data['lessons'] as $lesson) {
            $planData = $lesson->getEditPlainData();
            $planData['editMaterialUrl']['url'] .= '?redirectUrl=' . $returnUrl;
            $lessonsPlain[] = $planData;
        }

        $data['lessons'] = $lessonsPlain;
        return $data;

    }

    /*
     *  План занятий в виде грида
     */
    public function gridAction()
    {
        $this->view->replaceSidebar('subject', 'subject-extras', [
            'model' => $this->_subject,
            'order' => 100, // после Subject
        ]);

        $select = $this->getService('Lesson')->getSelect();
        $select->from(
            array('l' => 'lessons'),
            array(
                'lesson_id' => 'l.SHEID',
                'l.SHEID',
                'TypeID2' => 'l.typeID',
                'l.title',
                'l.typeID',
                'l.begin',
                'l.end',
                'l.timetype',
                'l.condition',
                'l.cond_sheid',
                'l.cond_mark',
                'l.cond_progress',
                'l.cond_avgbal',
                'l.cond_sumbal',
                'l.isfree',
                'sort_order' => 'l.order'
            )
        )
            ->joinLeft(
                array('sch' => 'schedule'),
                'l.SHEID = sch.SHEID',
                array()
            )
            ->joinLeft(
                array('s' => 'sections'),
                's.section_id = sch.section_id',
                array('section_name' => 's.name')
            );
        $select->where('l.CID = ?', $this->_subjectId)
            ->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
            ->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN) // @deprecated, только для обратной совместимости
            ->order(array('sort_order'));

        $grid = $this->getGrid($select, array(
            'sort_order' => array('order' => true, 'hidden' => true),
            'SHEID' => array('hidden' => true),
            'TypeID2' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'title' => array('title' => _('Название')),
            'typeID' => array('title' => _('Тип')),
            'begin' => array('title' => _('Ограничение по времени')),
            'condition' => array('title' => _('Условие')),
            'section_name' => array('title' => _('Раздел')),
            'end' => array('hidden' => true),
            'timetype' => array('hidden' => true),
            'cond_sheid' => array('hidden' => true),
            'cond_mark' => array('hidden' => true),
            'cond_avgbal' => array('hidden' => true),
            'cond_sumbal' => array('hidden' => true),
            'cond_progress' => array('hidden' => true),
            'isfree' => array('hidden' => true),
        ), array(
                'title' => null,
                'typeID' => array('values' => HM_Event_EventModel::getAllTypes(false)),
                'begin' => array('render' => 'DateTimeStamp'),
                'condition' => array('values' => array('0' => _('Нет условия'), '1' => _('Есть условие')))
            )
        );

        $grid->updateColumn('typeID', array('searchType' => '='));

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'lesson',
            'action' => 'edit',
            'subject_id' => $this->_subjectId,
        ),
            array('lesson_id'),
            $this->view->svgIcon('edit', _('Редактировать занятие'))
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'material',
            'action' => 'edit',
            'subject_id' => $this->_subjectId,
        ),
            array('lesson_id'),
            $this->view->svgIcon('reports', _('Редактировать материал'))
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'lesson',
            'action' => 'change-material',
            'subject_id' => $this->_subjectId,
        ),
            array('lesson_id'),
            $this->view->svgIcon('MaterialEdit', _('Заменить материал'))
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'lesson',
            'action' => 'edit-assign',
            'subject_id' => $this->_subjectId,
        ),
            array('lesson_id'),
            $this->view->svgIcon('staff-recruitment', _('Назначить участников'))
        );

        $grid->addAction(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('lesson_id'),
            $this->view->svgIcon('delete', _('Удалить'))
        );

        $grid->addAction(
            array(
                'module' => 'subject',
                'controller' => 'results',
                'action' => 'index',
                'gridmod' => null,
            ),
            array('lesson_id'),
            $this->view->svgIcon('Result', _('Результаты'))
        );

        $grid->addAction(
            array(
                'module' => 'lesson',
                'controller' => 'list',
                'action' => 'proctored',
            ),
            array('lesson_id'),
            _('Контролировать прохождение')
        );

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActionsProctoring'),
                'params' => array('{{TypeID2}}', '{{has_proctoring}}')
            )
        );

        $grid->addAction(
            array(
                'module' => 'eclass',
                'controller' => 'video',
                'action' => 'index'
            ),
            array('lesson_id'),
            $this->view->svgIcon('Play', _('Просмотр записей'))
        );

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{TypeID2}}')
            )
        );

        $grid->addMassAction(
            array(
                'module' => 'subject',
                'controller' => 'lesson',
                'action' => 'delete-by'
            ), _('Удалить'),
            _('Вы подтверждаете удаление отмеченных занятий? Если занятие было создано на основе информационного ресурса или учебного модуля, эти материалы вновь станут доступными всем слушателям курса в меню <Материалы курса>.')
        );

        $grid->updateColumn('typeID', array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateType'),
                        'params' => array('{{typeID}}')
                    )
            )
        );

        $grid->updateColumn('begin', array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateDateTime'),
                        'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
                    )
            )
        );

        $grid->updateColumn('title', array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateName'),
                        'params' => array('{{title}}', '{{lesson_id}}', '{{typeID}}')
                    )
            )
        );

        $grid->updateColumn('condition', array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateCondition'),
                        'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
                    )
            )
        );

        $exportVariantsUrl = array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'export-variants',
        );

        $grid->addMassAction($exportVariantsUrl, _('Cгенерировать варианты теста'));
        $grid->addSubMassActionInput(array($this->view->url($exportVariantsUrl)), 'variant_count');

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateActions($type, $actions) {
        if ($type != HM_Event_EventModel::TYPE_ECLASS || !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
            $this->unsetAction($actions, [
                'module' => 'eclass',
                'controller' => 'video',
                'action' => 'index'
            ]);
        }
        return $actions;
    }

    public function updateActionsProctoring($typeID, $hasProctoring, $actions) {
        $lesson = HM_Lesson_LessonModel::factory(array('typeID' => $typeID));
        $result = explode('<li>', $actions);

        if(!empty($lesson) && !$lesson->isResultInTable()) {
            unset($result[1]);
        }

        if(!$hasProctoring) {
            unset($result[2]);
        }

        return implode('<li>', $result);
    }


    public function updateName($field, $id, $type)
    {
        $target = ($type == HM_Event_EventModel::TYPE_ECLASS) ? ' target="_blank" ' : '';

        if(HM_Event_EventModel::TYPE_EMPTY == $type) {
            return $field;
        } else {
            return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'lesson', 'action' => 'index', 'lesson_id' => $id, 'subject_id' => $this->_getParam('subject_id'))) . '" title="' . _('Просмотр занятия') . '"' . $target . '>' . $field . '</a>';
        }
    }

    public function updateCondition($condSheid, $condMark, $condProgress, $condAvg, $condSum)
    {
        $conditions = HM_Lesson_LessonModel::getConditionTypes();
        if ($condSheid > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_LESSON];
        }
        if ($condProgress > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_PROGRESS];
        }
        if ($condAvg > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_AVGBAL];
        }
        if ($condSum > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_SUMBAL];
        }
        return _('Нет');
    }

    public function updateType($typeId)
    {
        $types = HM_Event_EventModel::getAllTypes();
        if (isset($types[$typeId])) {
            return $types[$typeId];
        }
    }

    public function updateDateTime($begin, $end, $timetype)
    {
        switch ($timetype) {
            case 1:
                if (($end == 0) || ($begin == 0)) {
                    $beginOrEnd = ($begin == 0) ? $end : $begin;
                    return sprintf(_('%s-й день'), floor($beginOrEnd / 60 / 60 / 24));
                } elseif ($begin != $end) {
                    return sprintf(_('%s-й день - %s-й день'), floor($begin / 60 / 60 / 24), floor($end / 60 / 60 / 24));
                } else {
                    return sprintf(_('%s-й день'), floor($begin / 60 / 60 / 24));
                }
                break;
            case 2:
                return _('Без ограничений');
                break;
            default:
                $begin = new HM_Date($begin);
                $end = new HM_Date($end);
                return sprintf('%s - %s', $begin->get(Zend_Date::DATETIME_SHORT), $end->get(Zend_Date::DATETIME_SHORT));
                break;
        }
    }

    // @todo: сюда слать результаты упорядочивания плана (#32524)
    public function orderAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;

        $user = $this->getService('User')->getCurrentUser();
        if (!$user) {
            return $this->responseJson(array('result' => $result, 'error' => _('Вы не авторизованы')));
        }

        $request = $this->getRequest();

        if ($request->isXmlHttpRequest() && $request->isPost()) {

            try {
                $inputJSON = $request->getRawBody();
                $requestParams = Zend_Json::decode($inputJSON);
            } catch (\Throwable $e) {
                return $this->responseJson(array('result' => $result, 'error' => _('Неизвестная ошибка. Обратитесь к администратору')));
            }

            $order = !empty($requestParams['order']) ? $requestParams['order'] : [];

            if (count($order)) {
                $lessons = !empty($order['lessons']) ? $order['lessons'] : [];
                $sections = !empty($order['sections']) ? $order['sections'] : [];

                if ($this->getService('Lesson')->setOrder($lessons, true)) {
                    $result = true;
                }

                if (count($sections)) {
                    foreach ($sections as $k => $section) {
                        $this->getService('Section')->setSectionOrder($section['id'], $k + 1);

                        if ($this->getService('Section')->setLessonsOrder($section['id'], $section['lessons'])) {
                            $result = true;
                        }
                    }
                }
            }

            return $this->responseJson(array('result' => $result));
        } else {
            return $this->responseJson(array('result' => $result, 'error' => _('Запрос неверный')));
        }
    }

    public function generateAction()
    {
        $lessonTypes = array_keys(HM_Event_EventModel::getTypes());
        foreach ($lessonTypes as $lessonType) {

            // пока не ясно что делать с этими типами ресурсов, пропускаем их
            if (in_array($lessonType, [HM_Event_EventModel::TYPE_EMPTY, HM_Event_EventModel::TYPE_ECLASS]))
                continue;

            // формируем имя метода инициализации в форме используя названия констант для компактности
            list(,$constName) = explode('_', HM_Event_EventModel::getTypeConstant($lessonType));
            $method = 'init' . (($lessonType == HM_Event_EventModel::TYPE_TASK) ? 'Test' : ucfirst(strtolower($constName)));

            // эти значения параметра 'vedomost' взяты из старого экшена Lesson_ListController::generateAction()
            $vedomost = in_array($lessonType, [HM_Event_EventModel::TYPE_POLL, HM_Event_EventModel::TYPE_TASK]) ? 0 : 1;

            // Цитата из условия задачи #33162 :
            // "... для генерации достаточно выбрать все элементы с текущим subject_id из вьюхи materials.
            //      Дальше всё без изменений - генерятся занятия с дефолтными параметрами."
            $subjectMaterials = $this->getService('Material')->fetchAll([
                'subject_id = ?' => $this->_subjectId,
                'type = ?'       => $lessonType
            ]);

            $lessons = $this->getService('Lesson')->fetchAll([
                    'typeID in (?)' => $lessonTypes,
                    'CID = ?' => $this->_subject->subid
                ]);

            $existedLessons = [];

            /** @var HM_Lesson_LessonModel $lesson */
            foreach ($lessons as $lesson) {
                $existedLessons[] = $lesson->getModuleId();
            }

            if (count($subjectMaterials)) {
                foreach ($subjectMaterials as $material) {
                    if(in_array($material->id, $existedLessons))
                        continue;

                    /** @var HM_Form $form */
                    $form = new $this->_formName();

                    $this->formData['module']   = $material->id;
                    $this->formData['title']    = $material->title;
                    $this->formData['event_id'] = $lessonType;
                    $this->formData['vedomost'] = $vedomost;
                    $form->getSubForm('step2')->$method();
                    $form->populate($this->formData);
                    $this->addLesson($form);
                    unset($form);
                    $this->generatedCount++;
                }
            }
        }

        $this->_flashMessenger->addMessage(sprintf(_('Сгенерировано занятий: %s'), $this->generatedCount));
        $this->_redirector->gotoSimple('edit', $this->_controller, $this->_module, array('subject_id' => $this->_subjectId));
    }

    public function importAction()
    {
        $source = $this->_getParam('source', 'csv');
        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

        $this->view->form = false;
        if ($this->_importService->needToUploadFile()) {
            $this->_valid = false;
            $form = $this->_importService->getForm($this->_subjectId);
            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $this->_importService->setFileName($form->file->getFileName());
                        $this->_valid = true;
                    }
                }
            } else {
                $this->view->form = $form;
            }
        }

        $importManager = null;
        try {
            $class                = $this->_importManagerClass;
            $importManager        =
            $this->_importManager = new $class();
            if ($this->_valid) $importManager->init($this->_importService->fetchAll());
        } catch (Exception $e) {
            $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }

        $lessons = _('Будут добавлены следующие занятия:');
        foreach ($importManager->getInserts() as $insert) {
            $lessons .= "\n" . $insert->order . ". " . $insert->title;
        }

        $form2 = new HM_Form();
        $form2->setMethod(Zend_Form::METHOD_POST);
        $form2->setName('import');
        $form2->addElement('hidden', 'cancelUrl', [
            'required' => false,
            'value' => $this->view->url([
                'module'     => 'subject',
                'controller' => 'lessons',
                'action'     => 'edit',
                'subject_id' => $this->_subjectId
            ])
        ]);

        $form2->addElement('textarea', 'info', ['value' => $lessons]);
        $form2->getElement('info')->setAttrib('disabled', 'disabled');
        $form2->addDisplayGroup(
            ['info', 'cancelUrl'],
            'importForm',
            ['legend' => sprintf(_('Будет добавлено занятий: %d'), $importManager->getInsertsCount())]
        );

        $url = $this->view->url([
            'module'     => 'subject',
            'controller' => 'lessons',
            'action'     => 'process',
            'subject_id' => $this->_subjectId,
            'source'     => $source
        ]);

        $form2->addElement('button', 'submit', [
            'Label' => _('Далее'),
            'onclick' => "window.location='$url'"
        ]);
        $form2->init();
        $this->view->form2         = $form2;
        $this->view->importManager = $importManager;
        $this->view->source        = $source;
        $this->view->subjectId     = $this->_subjectId;
    }

    public function processAction()
    {
        $source = $this->_getParam('source', 'csv');
        if (!$source || !method_exists($this, $source)) throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));

        call_user_func(array($this, $source));

        $importManagerClass = $this->_importManagerClass;
        $importManager = new $importManagerClass();
        if ($importManager->restoreFromCache())  $importManager->init(array());
        else $importManager->init($this->_importService->fetchAll());

        if (!$importManager->getInsertsCount()) $this->_flashMessenger->addMessage(_('Занятия не найдены'));
        $importManager->import($this->_subjectId);

        if ($importManager->getInsertsCount())
            $this->_flashMessenger->addMessage(
                sprintf(_('Было добавлено занятий: %d'), $importManager->getInsertsCount())
            );

        if ($importManager->getNotProcessedCount())
            $this->_flashMessenger->addMessage(
                sprintf(_('Не было добавлено занятий: %d'), $importManager->getNotProcessedCount())
            );

        $this->_redirector->gotoSimple('edit', 'lessons', 'subject', ['subject_id' => $this->_subjectId]);
    }

    public function csv()
    {
        $this->view->setSubSubHeader(_('Импорт плана занятий'));
        $this->_importService = $this->getService('LessonPlanCsv');
    }

    /*
     * Эти фантастические методы скопированы из ListController
     * Что они делают никто не знает
     *
     */
    /**
     * @param HM_Form $form
     * @return HM_Model_Abstract
     * @throws Zend_Exception
     */
    protected function addLesson($form)
    {
        $activities = '';
        if (null !== $form->getValue('activities')) {
            if (is_array($form->getValue('activities')) && count($form->getValue('activities'))) {
                $activities = serialize($form->getValue('activities'));
            }
        }

        $tool = '';
        if ($form->getValue('event_id') < 0) {
            $event = $this->getOne($this->getService('Event')->find(-$form->getValue('event_id')));
            if ($event) $tool = $event->tool;
        }

        $typeId = $form->getValue('event_id');
        $moduleId = $form->getValue('module');
        if ($typeId == HM_Event_EventModel::TYPE_LECTURE || $tool == HM_Event_EventModel::TYPE_LECTURE) {
            $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
            $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
        }

        $this->getService('Lesson')->setLessonFreeMode(
            $moduleId,
            $typeId,
            $this->_subjectId,
            HM_Lesson_LessonModel::MODE_FREE_BLOCKED
        );

        $data = [
            'title'         => $form->getValue('title'),
            'CID'           => $this->_subjectId,
            'createDate'    => date('Y-m-d H:i:s'),
            'params'        => 'module_id=' . $moduleId,
            'material_id'   => $moduleId,
            'typeID'        => $form->getValue('event_id'),
            'teacher'       => $form->getValue('teacher'),
            'moderator'     => $form->getValue('moderator'),
            'createID'      => $this->getService('User')->getCurrentUserId(),
            'recommend'     => $form->getValue('recommend'),
            'all'           => (int) $form->getValue('all'),
            'notify_before' => $form->getValue('notify') ? $form->getValue('notify_before') : 0,
            'GroupDate'     => $form->getValue('GroupDate'),
            'beginDate'     => $form->getValue('beginDate'),
            'endDate'       => $form->getValue('endDate'),
            'currentDate'   => $form->getValue('currentDate'),
            'beginTime'     => $form->getValue('beginTime'),
            'endTime'       => $form->getValue('endTime'),
            'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
            'endRelative'   => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
            'Condition'     => $form->getValue('Condition'),
            'cond_sheid'    => (string) $form->getValue('cond_sheid'),
            'cond_mark'     => (string) $form->getValue('cond_mark'),
            'cond_progress' => (string) $form->getValue('cond_progress'),
            'cond_avgbal'   => (string) $form->getValue('cond_avgbal'),
            'cond_sumbal'   => (string) $form->getValue('cond_sumbal'),
            'gid'           => $form->getValue('subgroups'),
            'notice'        => $form->getValue('notice'),
            'notice_days'   => (int) $form->getValue('notice_days'),
            'activities'    => $activities,
            'descript'      => $form->getValue('descript'),
            'tool'          => $tool,
            'threshold'     => (string) $form->getValue('threshold') ? $form->getValue('threshold') : '0',
            'isfree'        => HM_Lesson_LessonModel::MODE_PLAN,
            'vedomost'      => (
                -$form->getValue('event_id') == HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY ||
                -$form->getValue('event_id') == HM_Event_EventModel:: TYPE_OLYMPOX_EXAM) ? 1 :
                 $form->getValue('vedomost')
        ];

        $lessons = $this->getService('Lesson')->fetchAll(['CID = ?' => $this->_subjectId]);
        $lessonsOrders = $lessons->getList('order');
        if ($lessonsOrders) {
            $highestValue = max(array_values($lessonsOrders));
            $highestValue++;
            $data['order'] = $highestValue;
        }
        $lesson = $this->getService('Lesson')->insert($data);
        if ($lesson) {
            $this->_preProcessTest($lesson, $form);
            $params = $lesson->getParams();
            if ($form->getValue('module')) {
                $params['module_id'] = $form->getValue('module');
            }

            if ($form->getValue('assign_type')) {
                $params['assign_type'] = $form->getValue('assign_type');
            } elseif (isset($params['assign_type']) && $params['assign_type']) {
                unset($params['assign_type']);
            }

            if ($form->getValue('is_hidden', 0)) {
                $params['is_hidden'] = $form->getValue('is_hidden');
            } elseif (isset($params['is_hidden']) && $params['is_hidden']) {
                unset($params['is_hidden']);
            }

            if ($form->getValue('formula')) {
                $params['formula_id'] = $form->getValue('formula');
            } elseif (isset($params['formula_id'])) {
                unset($params['formula_id']);
            }

            if ($form->getValue('formula_group')) {
                $params['formula_group_id'] = $form->getValue('formula_group');
            }

            if ($form->getValue('formula_penalty')) {
                $params['formula_penalty_id'] = $form->getValue('formula_penalty');
            }

            if ($lesson->getType() == HM_Event_EventModel::TYPE_LECTURE) {
                $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
            }

            $lesson->setParams($params);
            $this->getService('Lesson')->update($lesson->getValues());

            $students = $form->getValue('students');
            $groupId = $form->getValue('subgroups');

            //**//
            $group = explode('_', $groupId);

            /* TODO Отписываем людей которые в ручном выборе, если выбрана группа подгруппа? */
            if ($group[0] == 'sg' || $group[0] == 's') {
                $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
            }

            /* Параметр Учебная группа */
            if ($group[0] == 'sg') {
                $groupId = (int) $group[1];
                $students = $this->getService('StudyGroup')->getUsers($groupId);


                /* Добавляем запись что группа подписана на урок */
                $this->getService('StudyGroupCourse')->addLessonOnGroup($this->_subjectId, $lesson->SHEID, $groupId);
            }
            /* Параметр Подгруппа */
            if ($group[0] == 's') {
                $groupId = (int) $group[1];
                if ($groupId > 0) {
                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                    $res = array();
                    foreach ($students as $value) {
                        $res[] = $value->mid;
                    }
                    $students = $res;
                }
            }
            //**//

            if (!$form->getValue('switch')) {
                $students = $lesson->getService()->getAvailableStudents($this->_subjectId);
            }
            $formUserVariant = $form->getValue('user_variant');
            $userVariants = array_filter(is_null($formUserVariant) ? array() : $formUserVariant);

            if ($form->getValue('assign_type', HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                $students = array_keys($userVariants);
            }

            if (is_array($students) && count($students)
                && (($this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                    || ($this->_subject->state == HM_Subject_SubjectModel::STATE_ACTUAL ))) {
                $this->assignStudents($lesson->SHEID, $students, $userVariants);
            }

            $this->getService('Subject')->update(array(
                'last_updated' => $this->getService('Subject')->getDateTime(),
                'subid' => $this->_subjectId
            ));

            if (!is_array($students)) {
                $students == 0 ? array() : array($students);
            }
            $this->_postProcess($lesson, $form, $students);
        }
        if ($lesson) {//перечитываем, тк в постпроцессе, напр., вебинара, может быть создаваемое занятие удалено
            $lesson = $this->getService('Lesson')->find($lesson->SHEID)->current();
        }

        return $lesson;
    }

    protected function assignStudents($lessonId, $students, $taskUserVariants = null)
    {
        if (is_array($students) && count($students)) {
            $this->getService('Lesson')->assignStudents($lessonId, $students, true, $taskUserVariants);
        } else {
            $this->getService('Lesson')->unassignAllStudents($lessonId);
        }
    }

    private function _postProcess(HM_Lesson_LessonModel $lesson, Zend_Form $form, $students) {
        switch ($lesson->getType()) {
            case HM_Event_EventModel::TYPE_TASK:
                //$abstract = $this->getOne($this->getService('Task')->find($form->getValue('module')));
                //$this->_postProcessTest($abstract, $lesson, $form);
                break;
            case HM_Event_EventModel::TYPE_ECLASS:
                $this->_postProcessEclass($lesson, $form, $students);
                break;
            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:
                /** @var HM_Quest_QuestModel $quest */
                $quest = $this->getOne($this->getService('Quest')->find($form->getValue('module')));
                $this->_postProcessQuest($quest, $lesson, $form);
                break;
            default:
                $this->getService('Test')->deleteBy($this->getService('Test')->quoteInto('lesson_id = ?', $lesson->SHEID));

                $activities = HM_Activity_ActivityModel::getActivityServices();
                if (isset($activities[$lesson->typeID])) {
                    $activityService = HM_Activity_ActivityModel::getActivityService($lesson->typeID);
                    if (strlen($activityService)) {
                        $service = $this->getService($activityService);
                        if ($service instanceof HM_Service_Schedulable_Interface) {
                            $service->onLessonUpdate($lesson, $form);
                        }
                    }
                }
        }
    }

    private function _postProcessEclass($lesson, $form, $students) {
        $eclassService = $this->getService('Eclass');

        $eclassService->webinarPush(array(
                'lesson'   => $lesson,
                'students' => $students,
            )
        );
    }
    /**
     * @param HM_Quest_QuestModel $quest
     * @param HM_Lesson_LessonModel $lesson
     * @param Zend_Form $form
     */
    private function _postProcessQuest($quest, $lesson, $form) {
        // Будем копировать настройки из области видимости курса
        $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $lesson->CID);

        /** @var HM_Quest_Settings_SettingsService $questSettingsService */
        $questSettingsService = $this->getService('QuestSettings');

        // Устанавливаем свою область видимости для занятий
        /** @var HM_Quest_Settings_SettingsModel $settings */
        $settings = $questSettingsService->copyToScope($quest, HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON, $lesson->SHEID);

        /**
         * Индивидульная настройка теста для занятия.
         * Раскомментировать при необходимости.
         */
        list($dataQuest, $dataSettings) = HM_Quest_Settings_SettingsModel::split($form->getValues());

        $dataSettings['quest_id']   = $settings->quest_id;
        $dataSettings['scope_type'] = $settings->scope_type;
        $dataSettings['scope_id']   = $settings->scope_id;


        if ($dataSettings['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $dataSettings['mode_selection_questions'] = $dataSettings['mode_selection_questions_cluster'];
        }
        unset($dataSettings['mode_selection_questions_cluster']);

        if(!$dataSettings['mode_display']) {
            $dataSettings['mode_display'] = $settings->mode_display;
        }

        if(is_null($dataSettings['mode_display'])) {
            $dataSettings['mode_display'] = $settings->mode_test_page;
        }

        if ($dataSettings['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS) {
            $dataSettings['mode_display_clusters'] = new Zend_Db_Expr('NULL');
        }

        if ($dataSettings['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS) {
            $dataSettings['mode_display_questions'] = new Zend_Db_Expr('NULL');
        } elseif($settings->mode_display_questions && !$dataSettings['mode_display_questions']) {
            $dataSettings['mode_display_questions'] = $settings->mode_display_questions;
        }


        if (!$dataSettings['cluster_limits']) {
            $dataSettings['cluster_limits'] = $settings->cluster_limits;
        }
        if (!$dataSettings['limit_time']) {
            $dataSettings['limit_time'] = new Zend_Db_Expr('NULL');
        }
        if (!$dataSettings['limit_attempts']) {
            $dataSettings['limit_attempts'] = new Zend_Db_Expr('NULL');
        }
        if (!$dataSettings['limit_clean']) {
            $dataSettings['limit_clean'] = new Zend_Db_Expr('NULL');
        }
        $questSettingsService->update($dataSettings);

        /*
        $elements = $form->getSubForm('step2')->getDisplayGroup('quest_settings')->getElements();
        foreach ($elements as $element) {
            $elementName = $element->getName();
            $elementValue = $element->getValue();
            if (isset($elementValue) && isset($settings->$elementName)) {
                $settings->$elementName = $elementValue;
            }
        }

        $questSettingsService->update($settings->getData());
        */
    }

    /**
     * Проверяет если Задание ещё не создано, то создаёт
     * @param $lesson
     * @param Zend_Form $form
     */
    private function _preProcessTest(HM_Lesson_LessonModel $lesson, Zend_Form $form) {

        if ($lesson->getType() == HM_Event_EventModel::TYPE_TASK) {
            /** @var HM_Task_TaskService $abstractTest */
            $taskService = $this->getService('Task');
            /** @var HM_Test_TestService $testService */
            $testService = $this->getService('Test');
            /** @var HM_Task_TaskModel $abstractTest */
            $abstractTest = $this->getOne($taskService->find($form->getValue('module')));

            if ($abstractTest) {
                $test = $this->getOne($testService->fetchAll(
                    $testService->quoteInto(
                        array('lesson_id = ?', ' AND test_id = ?', ' and datatype = ?'),
                        array($lesson->SHEID, $form->getValue('module'), HM_Test_TestModel::TYPE_TASK)
                    )
                ));

                if (!$test) {
                    $testService->deleteBy($this->getService('Test')
                        ->quoteInto(
                            array('lesson_id = ?', ' and datatype = ?'),
                            array($lesson->SHEID, HM_Test_TestModel::TYPE_TASK)
                        )
                    );

                    $testService->insert(
                        array(
                            'cid' => $lesson->CID,
                            'datatype' => HM_Test_TestModel::TYPE_TASK,
                            'sort' => 0,
                            'free' => 0,
                            'rating' => 0,
                            'status' => 1,
                            'last' => 0,
                            'cidowner' => $lesson->CID,
                            'title' => $lesson->title,
                            'data' => $abstractTest->getValue('data'),
                            'lesson_id' => $lesson->SHEID,
                            'test_id' => $form->getValue('module'),
                            'mode' => $form->getValue('mode'),
                            'lim' => $form->getValue('lim'),
                            'qty' => $form->getValue('qty'),
                            'startlimit' => $form->getValue('startlimit'),
                            'limitclean' => $form->getValue('limitclean'),
                            'timelimit' => $form->getValue('timelimit'),
                            'random' => $form->getValue('random'),
                            'adaptive' => (int) ($form
                                    ->getValue('questions') == HM_Test_TestModel::QUESTIONS_ADAPTIVE),
                            'questres' => $form
                                ->getValue('questres') !== null ? $form->getValue('questres') : 0,
                            'showurl' => $form
                                ->getValue('showurl') !== null ? $form->getValue('showurl') : 0,
                            'endres' => $form
                                ->getValue('endres') !== null ? $form->getValue('endres') : 0,
                            'skip' => $form->getValue('skip'),
                            'allow_view_log' => $form->getValue('allow_view_log'),
                            'comments' => $form->getValue('comments'),
                            'type' => $abstractTest->getTestType(),
                            'threshold' => $form->getValue('threshold'),
                        )
                    );
                }
            }
        }
    }
}
