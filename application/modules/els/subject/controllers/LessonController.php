<?php

class Subject_LessonController extends HM_Controller_Action_Subject
{
    /** @var HM_Lesson_LessonModel $_lesson */
    protected $_lesson;

    protected $_isEnduser = true;

    protected $_returnUrl = true;

    public function init()
    {
        parent::init();

        $this->setActiveContextMenu($this->_isEnduser ? 'mca:subject:lessons:index' : 'mca:subject:lessons:edit');

        $this->_returnUrl = $this->view->url([
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => $this->_isEnduser ? 'index' : 'edit',
            'subject_id' => $this->_subjectId,
        ], null, true);

        if ($lessonId = $this->_getParam('lesson_id', 0)) {

            $this->_lesson = $this->getService('Lesson')->getOne(
                $this->getService('Lesson')->findDependence('Subject', $lessonId)
            );

            if ($this->_lesson) {

                $this->view->setHeader($this->_lesson->title);

                $this->view->setBackUrl($this->_returnUrl);

                $switcherData = $this->getService('Lesson')->getContextSwitcherData($this->_lesson);
                $this->view->setSwitchContextUrls($switcherData);

            } else {

                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Занятие не найдено')
                ));
                $this->_redirectToIndex();
            }
        }
    }

    public function indexAction()
    {
        $lessonId = $this->view->lessonId = (int) $this->_getParam('lesson_id');
        $subjectId = $this->view->subjectId = (int) $this->_getParam('subject_id', 0);

        $isFinalLink = (int) $this->_getParam('final_link', 0);

        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));

        if(!$isFinalLink && $lessonId && $lesson->has_proctoring && $this->getService('User')->isEnduser()) {
            if( $lesson->typeID==HM_Event_EventModel::TYPE_COURSE ||
                $lesson->typeID==HM_Event_EventModel::TYPE_RESOURCE ||
                $lesson->typeID==HM_Event_EventModel::TYPE_TASK) {
                $this->_redirector->gotoSimple('start', 'index', 'lesson', array('lesson_id'=>$lessonId, 'subject_id'=>$subjectId));
            }
        }

        if ($this->_lesson) {
            try{
                if($lesson->has_proctoring and !$this->getService('Proctoring')->isValidBrowser()) {
                    $this->_flashMessenger->addMessage(array(
                        'message' => $this->getService('Proctoring')->getInvalidBrowserMessage(),
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR
                    ));

                } elseif ($this->getService('Lesson')->isExecutable($this->_lesson)) {
                    if ($this->_isEnduser) {

                        // проверка даты начала/окончания курса и занятия
                        $currentDate = new HM_Date();

                        // фиксированная дата курса
                        if ($this->_subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT) {
                            $subjectBegin = new HM_Date($this->_subject->begin);
                            $subjectEnd   = new HM_Date($this->_subject->end);
                            if ($subjectBegin->getTimestamp() > $currentDate->getTimestamp() || $subjectEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($subjectBegin->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала курса не наступила') : _('Курс завершен')));
                                $this->_redirector->gotoUrl($this->_returnUrl);
                            }
                        }
                    }

                    /* Логирование захода пользователя в занятие */
                    $this->getService('Session')->toLog(array('lesson_id' => $this->_lesson->SHEID, 'course_id' => $this->_subjectId, 'lesson_type' => $this->_lesson->typeID));

                    $this->getService('LessonAssign')->onLessonStart($this->_lesson);

                    if (!$this->_lesson->isExecutable()) {

                        $this->_flashMessenger->addMessage([
                            'message' => _('Данное занятие невозможно запустить'),
                            'type' => HM_Notification_NotificationModel::TYPE_ERROR
                        ]);

                    } else {

                        $isCurrentEnduser = $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
                        if ($isCurrentEnduser) {
                            $this->getService('LessonLog')->logCurrentUser($this->_lesson->SHEID);
                        }

                        if ($this->_lesson->isExternalExecuting()) {
                            // здесь все типы занятий, отображающиеся в отдельном интерфейсе
                            // (вне контекста курса и требующие своего отдельного контроллера)

                            Zend_Registry::get('session_namespace_default')->lesson['execute']['returnUrl'] = $this->_returnUrl;
                            $this->_redirector->gotoUrl($this->_lesson->getExecuteUrl());
                        } else {
                            // @todo: если появятся другие типы кроме занятий, не требующие отдельного контроллера - добавлять здесь

                            $resourceId = $this->_lesson->material_id;
                            $resource = $this->getService('Resource')->findOne($resourceId);

                            $sidebar = $this->_lesson->chat_enabled ? 'subject-chat' : 'subject-lesson';
                            $this->view->replaceSidebar('subject', $sidebar, [
                                'model' => $this->_lesson,
                                'order' => 100, // после Subject
                            ]);

                            $this->view->lesson = $this->_lesson;
                            $this->view->material = $resource;

                            if ($this->isMobile()) {
                                $this->view->appContentFullscreen = true;
                                $this->view->layoutContentFullWidth = true;
                            }
                        }
                    }
                }

            } catch (HM_Exception $exception) {
                $this->_flashMessenger->addMessage([
                    'message' => $exception->getMessage(),
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR
                ]);
            }
        }
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoUrl($this->_returnUrl);
    }

    public function createByMaterialAction()
    {
        $materials = $this->_getParam('postMassIds_grid');
        $materials = explode(',', $materials);
        $hasErrors = false;

        foreach ($materials as $material) {
            $materialId = strtok($material, '-');
            $materialType = strtok('-');

            /** @var HM_Material_MaterialService $materialService */
            $materialService = $this->getService('Material');
            $materialModel = $materialService->findMaterial($materialId, $materialType);

            if ($materialModel) {
                $lesson = $materialModel->becomeLesson($this->_subjectId);

                if (!$lesson) {
                    $hasErrors = true;
                }
            }
        }

        if (!$hasErrors) {
            $this->_redirector->gotoUrl($this->view->url([
                'module'     => 'subject',
                'controller' => 'lessons',
                'action'     => 'edit',
                'subject_id' => $this->_subjectId
            ], null, true));
            $this->_flashMessenger->addMessage(_('Занятия успешно созданы'));
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Ошибка при создании занятий')));
            $this->_redirectToIndex();
        }
    }

    public function createAction()
    {
        $this->view->setSubSubHeader(_('Создание занятия'));

        $form = new HM_Form_LessonMaterial();
        $request = $this->getRequest();
        $materials = $lesson = $createType = null;
        $createType = $this->_getParam('create_type');
        /** @var HM_Material_MaterialService $materialService */
        $materialService = $this->getService('Material');

        if ($request->isPost()) {

            // add this for enable file validation
            if ($createType == HM_Form_LessonMaterial::CREATE_TYPE_AUTODETECT and !$form->getValue('code')) {
                $fileElement = $form->getElement('file');
                $fileElement->setRequired(true);
            }

            if ($form->isValid($request->getParams())) {

                $title = $form->getValue('title') ? : _('[Без названия]');

                switch ($createType) {
                    case HM_Form_LessonMaterial::CREATE_TYPE_AUTODETECT:
                        if ($insertValue = $form->getValue('code')) {
                            // пока отключено
                            try {
                                $materials[] = $materialService->insert($insertValue, $title, $this->_subjectId);
                            } catch (HM_Exception_Upload $e) {
                                $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()]);
                            }
                        } else {
                            /** @var HM_Form_Element_Vue_File $fileElement */
                            $fileElement = $form->getElement('file');

                            $fileElement->receive();
                            $filename = $fileElement->getFileName();
                            $pathinfo = pathinfo($filename);
                            $title = $title = $form->getValue('title') ? : $pathinfo['filename'];

                            if ($fileElement->isReceived()) {
                                $insertValue = realpath($filename);
                                try {
                                    $materials[] = $materialService->insert($insertValue, $title, $this->_subjectId);
                                } catch (HM_Exception_Upload $e) {
                                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()]);
                                }
                            }

                        }
                        break;
                    case HM_Form_LessonMaterial::CREATE_TYPE_MATERIAL:
                        // создать в конструкторе
                        $materialType = $this->_getParam('material_type');
                        $materials[] = $materialService->createDefault(
                            $materialType,
                            $title,
                            $this->_subjectId
                        );
                        break;
                    case HM_Form_LessonMaterial::CREATE_TYPE_EMPTY:
                        // без материала
                        $lesson = $this->getService('Lesson')->createEmpty($title, $this->_subjectId);
                        break;
                    default:

                        // вкладки 2 и 3
                        $subjectMaterialIdType = $this->_getParam('subject_material_id_type');
                        $kbMaterialIdType = $this->_getParam('kb_material_id_type');
                        $materialIdType = $subjectMaterialIdType ?: $kbMaterialIdType;

                        if(!is_array($materialIdType))
                            $materialIdType = [$materialIdType];

                        foreach ($materialIdType as $materialIdTypeRow) {
                            $materialId = strtok($materialIdTypeRow, '-');
                            $materialEventType = strtok('-');

                            // @todo: рефакторить
                            if ($materialEventType == 'scorm') $materialEventType = 'course';
                            $materials[] = $materialService->findMaterial($materialId, $materialEventType);
                        }
                }


                if ($materials) {
                    foreach ($materials as $material) {
                        $lesson = $material->becomeLesson($this->_subjectId);

                        if (HM_Event_EventModel::TYPE_ECLASS == $lesson->typeID) {
                            $students = $lesson->getService()->getAvailableStudents($this->_subjectId);
                            $material->webinarPush(['lesson' => $lesson, 'students' => $students]);
                            $lesson = $this->getService('Lesson')->fetchRow(['SHEID = ?' => $lesson->SHEID]);
                        }
                        // Получаем созданный форум
                        elseif (HM_Event_EventModel::TYPE_FORUM == $lesson->typeID) {
                            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($this->_subjectId));
                            $forum = $this->getService('Forum')->getForumBySubject($subject, null, $lesson);
                        }
                    }
                }

                if ($lesson) {
                    $this->_flashMessenger->addMessage(_('Занятие успешно создано'));
                } else {
                    $this->_flashMessenger->addMessage([
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Произошла ошибка при создании занятия')
                    ]);
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'lesson_id')) $redirectUrl = sprintf('%s/lesson_id/%d', trim($redirectUrl, "/"), $lesson->SHEID);
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                } elseif (($createType == HM_Form_LessonMaterial::CREATE_TYPE_MATERIAL)) {
                    // к конструктору материалов
                    if ($lesson && !in_array($lesson->typeID,
                        [HM_Event_EventModel::TYPE_ECLASS, HM_Event_EventModel::TYPE_FORUM])
                    ) {
                        $this->_redirector->gotoUrl($this->view->url([
                            'controller' => 'material',
                            'action' => 'edit',
                            'lesson_id' => $lesson->SHEID,
                        ]));
                    } elseif ($lesson && $forum && $lesson->typeID == HM_Event_EventModel::TYPE_FORUM) {
                        // Занятие типа Форум - в редактирование темы форума
                        $this->_redirector->gotoSimple('edit', 'themes', 'forum', [
                            'forum_id'   => $forum->forum_id,
                            'section_id' => $forum->section->section_id
                        ]);
                    } else {
                        // К плану занятий
                        $this->_redirector->gotoUrl($this->_returnUrl);
                        // сразу в чёрную комнату
                        // $this->_redirector->gotoUrl($this->view->url([
                        //     'controller' => 'lesson',
                        //     'action' => 'index',
                        //     'subject_id' => $this->_subjectId,
                        //     'lesson_id' => $lesson->SHEID,
                        // ]));
                    }
                } else {

                    $this->_redirectToIndex();
                }
            }
        }

        $this->view->form = $form;
    }

    public function changeMaterialAction()
    {
        $this->view->setSubSubHeader(_('Замена материала'));

        $form = new HM_Form_ChangeMaterial();
        $request = $this->getRequest();
        $material = $lesson = $createType = null;
        $createType = $this->_getParam('create_type');
        /** @var HM_Material_MaterialService $materialService */
        $materialService = $this->getService('Material');

        $lessonId = $this->getRequest()->getParam('lesson_id', 0);
        $lesson = $this->getService('Lesson')->find($lessonId);
        if (count($lesson)) {
            if ($lesson->current()->typeID == HM_Event_EventModel::TYPE_EMPTY) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Для данного типа занятия изменение материала не предусмотрено.')
                ));
                $this->_redirectToIndex();
            } elseif ($lesson->current()->typeID == HM_Event_EventModel::TYPE_FORUM) {

                // Перенаправить на редактирование темы форума
                $forum = $this->getService('Forum')->getForumBySubject($this->_subject, null, $this->_lesson);
                if ($forum) {
                    $this->_redirector->gotoSimple('edit', 'themes', 'forum', [
                        'forum_id'   => $forum->forum_id,
                        'section_id' => $forum->section->section_id
                    ]);
                } else {
                    $this->_flashMessenger->addMessage([
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Тема форума для данного занятия не найдена')
                    ]);
                    $this->_redirectToIndex();
                }
            }
        }

        if ($request->isPost()) {
            if (count($lesson)) {
                $lesson = $lesson->current();
                if ($form->isValid($request->getParams())) {
                    $title = $form->getValue('title') ? : $lesson->title;

                    switch ($createType) {
                        case HM_Form_ChangeMaterial::CREATE_TYPE_AUTODETECT:
                            if ($insertValue = $form->getValue('code')) {
                                // пока отключено
                                try {
                                    $material = $materialService->insert($insertValue, $title, $this->_subjectId);
                                } catch (HM_Exception_Upload $e) {
                                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()]);
                                }
                            }
                            break;
                        case HM_Form_ChangeMaterial::CREATE_TYPE_MATERIAL:
                            // создать в конструкторе
                            $materialType = $this->_getParam('material_type');
                            $material = $materialService->createDefault(
                                $materialType,
                                $title,
                                $this->_subjectId
                            );
                            break;
                        case HM_Form_ChangeMaterial::CREATE_TYPE_EMPTY:
                            // без материала
                            $lesson->typeID = HM_Event_EventModel::TYPE_EMPTY;
                            $lesson->material_id = null;
                            $lesson->setParams([]);
                            $this->getService('Lesson')->update($lesson->getValues());
                            break;
                        default:
                            // вкладки 2 и 3
                            $subjectMaterialIdType = $this->_getParam('subject_material_id_type');
                            $kbMaterialIdType = $this->_getParam('kb_material_id_type');
                            $materialIdType = $subjectMaterialIdType ?: $kbMaterialIdType;

                            $materialId = strtok($materialIdType, '-');
                            $materialEventType = strtok('-');

                            // @todo: рефакторить
                            if ($materialEventType == 'scorm') $materialEventType = 'course';

                            //$materialEventType = HM_Kbase_KbaseModel::getKbaseAndEventTypesMap($materialKbaseType);

                            $material = $materialService->findMaterial($materialId, $materialEventType);
                            break;
                    }


                    if ($material) {
                        if ($materialType = $this->_getParam('material_type')) {
                            $lesson->typeID = $materialType;
                        } else {
                            switch (get_class($material)) {
                                case 'HM_Resource_ResourceModel':
                                    $lesson->typeID = HM_Event_EventModel::TYPE_RESOURCE;
                                    break;
                                case 'HM_Quest_Type_TestModel':
                                    $lesson->typeID = HM_Event_EventModel::TYPE_TEST;
                                    break;
                                case 'HM_Quest_Type_PollModel':
                                    $lesson->typeID = HM_Event_EventModel::TYPE_POLL;
                                    break;
                                case 'HM_Quest_Task_TaskModel':
                                    $lesson->typeID = HM_Event_EventModel::TYPE_TASK;
                                    break;
                                case 'HM_Course_CourseModel':
                                    $lesson->typeID = HM_Event_EventModel::TYPE_COURSE;
                                    break;
                            }
                        }
                        $lesson->material_id = $material->getPrimaryKey();
                        $lesson->setParams(['module_id' => $material->getPrimaryKey()]);
                        $this->getService('Lesson')->update($lesson->getValues());

                        if (HM_Event_EventModel::TYPE_ECLASS == $lesson->typeID) {
                            $students = $lesson->getService()->getAvailableStudents($this->_subjectId);
                            $material->webinarPush(['lesson' => $lesson, 'students' => $students]);
                            $lesson = $this->getService('Lesson')->fetchRow(['SHEID = ?' => $lesson->SHEID]);
                        }
                    }

                    $this->_flashMessenger->addMessage(_('Материал успешно заменён.'));

                    if ($redirectUrl = $this->_getParam('redirectUrl')) {
                        if (!strpos($redirectUrl, 'lesson_id')) $redirectUrl = sprintf('%s/lesson_id/%d', trim($redirectUrl, "/"), $lesson->SHEID);
                        $this->_redirector->gotoUrl(urldecode($redirectUrl));
                    } elseif (($createType == HM_Form_ChangeMaterial::CREATE_TYPE_MATERIAL)) {
                        // к конструктору материалов
                        if ($lesson &&
                            HM_Event_EventModel::TYPE_ECLASS != $lesson->typeID
                        ) {
                            $this->_redirector->gotoUrl($this->view->url([
                                'controller' => 'material',
                                'action' => 'edit',
                                'lesson_id' => $lesson->SHEID,
                            ]));
                        } else {
                            // сразу в чёрную комнату
                            $this->_redirector->gotoUrl($this->view->url([
                                'controller' => 'lesson',
                                'action' => 'index',
                                'subject_id' => $this->_subjectId,
                                'lesson_id' => $lesson->SHEID,
                            ]));
                        }
                    } else {
                        $this->_redirectToIndex();
                    }
                }
            } else {
                $this->_flashMessenger->addMessage([
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Произошла ошибка при замене материала.')
                ]);
                $this->_redirectToIndex();
            }
        }

        $this->view->form = $form;
    }

    public function editAction()
    {
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');

        $this->view->setSubSubHeader(_('Редактирование занятия'));
        $form = new HM_Form_Lesson();
        $request = $this->getRequest();

        $newType = $this->getParam('typeID', $this->_lesson->typeID);
        if($newType !== $this->_lesson->typeID) {
            $data = $this->_lesson->getData();

            $data['tool'] = $newType < 0 ? HM_Event_EventModel::TYPE_TEST : '';
            $data['typeID'] = $newType;

            $this->_lesson = HM_Lesson_LessonModel::factory($data);
        }

        if ($request->isPost()) {
            $form->setDefaults($request->getPost());

            if ($form->isValid($request->getPost())) {
                $checkResult = $this->checkLessonDates($form);
                $lessonData = $lessonService->getLessonFormValuesToSave($form);
                if ($lessonData['has_proctoring'] && !$this->_lesson->teacher) {
                    $lessonData['teacher'] = $this->getService('User')->getCurrentUserId();
                }

                if ($lessonData['has_proctoring']) {
                    // на всякий случай, если не было прописно при создании
                    $config = Zend_Registry::get('config');
                    $proctoringServers = array_keys($config->proctoring->servers->toArray());
                    $lessonData['proctoring_server'] = $proctoringServers[$lessonData['SHEID']%count($proctoringServers)];
                }

                if ($this->_lesson = $lessonService->update($lessonData)) {
                    // todo: Для Таволги (#39517) делаем якобы галочки нет и всегда такое поведение
                    // todo: Для 5.0 делаем такое поведение пока не сделаем страницу настройки персональных дат
                    if (true || $form->getValue('reassignDates')) {
                        foreach ($lessonService->getStudents($this->_lesson->SHEID) as $student) {
                            $lessonService->assignStudentDates($this->_lesson, $student, $this->_subject);
                        }
                    }

                    $params = $lessonService->getLessonParamsToSave($this->_lesson, $form);
                    $this->_lesson->setParams($params);
                    $this->getService('Lesson')->update($this->_lesson->getValues());
                }

                if (!$this->_lesson && $this->getParam('lesson_id')) {
                    $this->_lesson = $this->getService('Lesson')->find($this->getParam('lesson_id'))->current();
                }

                $lessonService->_postProcessLessonSave($this->_lesson, $form);

                if ($redirectUrl = $this->_getParam('redirectUrl')) {

                    if (!strpos($redirectUrl, 'material')) {
                        $this->_flashMessenger->addMessage(_('Занятие успешно изменено'));
                    }

                    if (!strpos($redirectUrl, 'lesson_id')) {
                        $redirectUrl = sprintf('%s/lesson_id/%d', trim($redirectUrl, "/"), $this->_lesson->SHEID);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                } else {
                    if ($checkResult) {
                        $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована, так как она выходила за рамки курса'));
                    }
                    $this->_redirectToIndex();
                }
            }
        } else {
            $questId = $this->_getParam('quest_id', null);
            $values = $lessonService->getLessonFormDefaults($this->_lesson, $questId);
            $form->setDefaults($values);
        }

        $this->view->form = $form;
    }

    public function editAssignAction()
    {
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');
        $this->view->setSubSubHeader(_('Назначение участников'));
        $form = new HM_Form_LessonAssign();

        $lessonIsManualTask = false;
        $currentBaseType = $lessonService->getLessonTool($this->_lesson->typeID);
        if (HM_Event_EventModel::TYPE_TASK == $currentBaseType) {
            if (HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL == $this->_lesson->getAssignType()) {
                $lessonIsManualTask = true;
            }
        }

        $request = $this->getRequest();
        $formValues = $request->getPost();

        if ($request->isPost() && $form->isValid($formValues)) {
            $updateFields = ['all' => (int) $form->getValue('all')];
            if (!($this->_lesson->has_proctoring && empty($form->getValue('teacher')))) {
                $updateFields['teacher'] = $form->getValue('teacher');
            }
            $this->getService('Lesson')->updateWhere(
                $updateFields,
                ['SHEID = ?' => $this->_lesson->SHEID]
            );

            $students = $form->getValue('students');
            $groupId = $form->getValue('subgroups');
            $group = explode('_', $groupId);

            if ($group[0] == 'sg' || $group[0] == 's') {
                $lessonService->unassignStudent($this->_lesson->SHEID, $students);
                $this->getService('StudyGroupCourse')->removeLessonFromGroups($this->_subjectId, $this->_lesson->SHEID);
            }

            $variants = (array) $form->getValue('variants');
            if ($lessonIsManualTask) {

                $newVariants = $variants['new'];
                unset($variants['new']);

                /** @var HM_Task_Conversation_ConversationService $taskConversationService */
                $taskConversationService = $this->getService('TaskConversation');
                $taskConversations = $taskConversationService->fetchAll([
                    'lesson_id = ?' => $this->_lesson->SHEID,
                    'type = ?' => HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK,
                ]);

                $newVariantsMap = array_column($variants, 'variant', 'student');

                foreach ($taskConversations as $conversation) {
                    $hasStudent = in_array($conversation->user_id, array_column($variants, 'student'));

                    if (!$hasStudent) {
                        $taskConversationService->deleteBy([
                            'lesson_id = ?' => $this->_lesson->SHEID,
                            'type = ?' => HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK,
                            'user_id = ?' => $conversation->user_id,
                        ]);
                    } elseif ($hasStudent && $newVariantsMap[$conversation->user_id] != $conversation->variant_id) {
                        $taskConversationService->delete($conversation->conversation_id);
                        $taskConversationService->addConversationWithVariant($this->_lesson->SHEID, $conversation->user_id, $newVariantsMap[$conversation->user_id]);
                    }
                }

                if ($newVariants) {
                    foreach ($newVariants['student'] as $key => $studentId) {
                        $variantId = $newVariants['variant'][$key];
                        $conversationVariant = $taskConversationService->fetchRow(['lesson_id = ?' => $this->_lesson->SHEID, 'user_id = ?' => $studentId, 'variant_id = ?' => $variantId]);
                        if (!$conversationVariant) {
                            $taskConversationService->addConversationWithVariant($this->_lesson->SHEID, $studentId, $variantId);
                        }
                    }
                }
            }

            /* Параметр Учебная группа */
            if ($group[0] == 'sg') {
                $groupId = (int) $group[1];
                $students = $this->getService('StudyGroup')->getUsers($groupId);
                /* Добавляем запись что группа подписана на урок */
                $this->getService('StudyGroupCourse')->addLessonOnGroup($this->_subjectId, $this->_lesson->SHEID, $groupId);
            }

            /* Параметр Подгруппа */
            if ($group[0] == 's') {
                $groupId = (int) $group[1];
                if ($groupId > 0) {
                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId))->getList('mid');
                }
            }

            if (!$form->getValue('switch')) {
                $students = $lessonService->getAvailableStudents($this->_subjectId);
            }

            $subject = $this->getService('Subject')->findOne($this->_subjectId);
            if ($subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL ||
                $subject->state == HM_Subject_SubjectModel::STATE_ACTUAL
            ) {

                if (!empty($students)) {
////$this->getService('Lesson')->beginProctoringTransaction();
                    $lessonService->assignStudents($this->_lesson->SHEID, $students);
                } else {
                    $lessonService->unassignAllStudents($this->_lesson->SHEID);
                }
            }
////$this->getService('Lesson')->commitProctoringTransaction();

            switch ($this->_lesson->typeID) {
                case HM_Event_EventModel::TYPE_ECLASS:
                    $this->getService('Eclass')->lessonWebinarReassign($this->_lesson->SHEID);
                    break;
            }

            if ($redirectUrl = $this->_getParam('redirectUrl')) {
                //add lesson_id param, if not exist
                if (!strpos($redirectUrl, 'lesson_id')) $redirectUrl = sprintf('%s/lesson_id/%d', trim($redirectUrl, "/"), $this->_lesson->SHEID);

                $this->_redirector->gotoUrl(urldecode($redirectUrl));
            } else {
                $this->_redirectToIndex();
            }

        } elseif ($this->_lesson) {
            $values = [
                'teacher' => $this->_lesson->teacher,
                'all' => $this->_lesson->all,
            ];

            if ($this->_lesson->gid != 0 && $this->_lesson->gid != -1) {
                $values['switch'] = 2;
            } else {
                $values['switch'] = 1;
            }


            if ($lessonIsManualTask) {
                $conversationService = $this->getService('TaskConversation');
                $conversations = $conversationService->getSelect()
                    ->distinct()
                    ->from(
                        ['tc' => 'task_conversations'],
                        [
                            'user_id',
                            'variant_id'
                        ]
                    )
                    ->joinInner(['sid' => 'scheduleID'], 'tc.lesson_id=sid.SHEID and tc.user_id=sid.MID', [])
                    ->where('lesson_id=?', $this->_lesson->SHEID)
                    ->where('type=?', HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK)
                    ->query()->fetchAll();

                foreach ($conversations as $conversation) {
                    $values['variants'][] = [
                        'student' => $conversation['user_id'],
                        'variant' => $conversation['variant_id'],
                    ];
                }
            }

            $form->setDefaults($values);
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $this->getService('Lesson')->delete($this->_lesson->SHEID);

        $this->_flashMessenger->addMessage(_('Занятие успешно удалено'));
        $this->_redirectToIndex();
    }

    /**
     * #7590
     * Проверка дат при создании-обновлении занятия.
     * Если курс с регламентированными датами и правит-создает занятие не автор курса,
     * то не даем выскочить за рамки курса
     * @param $form
     * @param $subjectId
     * @return bool - были или нет внесены изменения в даты занятия (TRUE-были)
     */
    private function checkLessonDates($form)
    {
        $result = false;
        if ($this->_subject) {
            if (HM_Subject_SubjectModel::PERIOD_DATES == $this->_subject->period) {
                $beginSubject = strtotime($this->_subject->begin);
                $endSubject = strtotime($this->_subject->end);

                if ($beginSubject || $endSubject) {
                    if ($form->getValue('beginDate') && $form->getValue('endDate')) {
                        $beginLesson = strtotime($form->getValue('beginDate'));
                        $endLesson = strtotime($form->getValue('endDate'));

                        if ($this->_subject->begin && ($beginLesson - $beginSubject) < 0 || ($endSubject - $beginLesson) < 0 ) {
                            $date = new HM_Date($beginSubject);
                            $form->getElement('beginDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }

                        if ($this->_subject->end && ($endSubject - $endLesson) < 0 || ($endLesson - $beginSubject) < 0 ) {
                            $date = new HM_Date($endSubject);
                            $form->getElement('endDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }
                    }

                    if ($form->getValue('currentDate')) {
                        $curLesson  = strtotime($form->getValue('currentDate'));
                        if ($beginSubject && ($curLesson - $beginSubject) < 0 || ($endSubject - $curLesson) < 0 ) {
                            $date = new HM_Date($beginSubject);
                            $form->getElement('currentDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function deleteByAction()
    {
        $lessonIds = $this->_request->getParam('postMassIds_grid');
        $lessonIds = explode(',', $lessonIds);

        $eclassService = $this->getService('Eclass');
        if (is_array($lessonIds) && count($lessonIds)) {
            foreach ($lessonIds as $id) {
                $lesson = $this->getService('Lesson')->findOne($id);

                if ($lesson) {
                    $eclassService->webinarDelete($lesson->webinar_event_id);
                }

                $this->getService('Lesson')->delete($id);
            }
        }

        $this->_flashMessenger->addMessage(_('Занятия успешно удалены'));
        $this->_redirectToIndex();
    }
}
