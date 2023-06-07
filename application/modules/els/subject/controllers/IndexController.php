<?php
class Subject_IndexController extends HM_Controller_Action_Subject
{
    private $_testsCache = array();
    private $fromProgramArray = array();

    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id');
        $courseId = (int) $this->_getParam('course_id', 0);

        if ($lessonId = $this->view->lessonId = (int) $this->_getParam('lesson_id', 0)) {
            /** @var HM_Lesson_Course_CourseModel|HM_Lesson_Lecture_LectureModel $lesson */
            $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));

            if (($lesson->getType() == HM_Event_EventModel::TYPE_LECTURE) && empty($courseId)) {
                if ($moduleId = $lesson->getModuleId()) {
                    $courseItem = $this->getService('CourseItem')->getOne(
                        $this->getService('CourseItem')->find($moduleId)
                    );
                    if ($courseItem) {
                        $courseId = $courseItem->cid;
                    }
                }
            }
        }

        // $this->initLessonTabs();

        if ($subjectId && $courseId == 0) {
            $courses = $this->getService('Subject')->getCourses($subjectId, HM_Course_CourseModel::STATUS_ACTIVE);
            $this->view->courses = $courses;
        } elseif($courseId != 0){

            if ($lesson->CID && $subjectId && ($lesson->CID != $subjectId)) {
                $this->_redirector->gotoSimple('card','index','subject', array(
                    'subject_id' => $subjectId,
                ));
            }

            $courseObject = $this->getOne($this->getService('Course')->find($courseId));
            $this->view->courseObject = $courseObject;

            /** @var HM_User_UserService $userService */
            $userService = $this->getService('User');
            $userId                   = $userService->getCurrentUserId();

            $lessonAssign = $this->getService('LessonAssign')
                           ->getOne(
                                    $this->getService('LessonAssign')
                                         ->fetchAll(array('SHEID = ?'=> $lessonId,
                                                          'MID = ?'  => $userId
                                                    ))
                    );

            // @tocheck
            // обновляем scheduleID: для курса из конструктора - успешное прохождение,
            // для импортируемых - статус "в просессе" и нулевой процент прохождения если он еще не был начат
            if ( $lessonAssign ) {
                if ( $lessonAssign->isfree == HM_Lesson_LessonModel::MODE_FREE ) { //|| $lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
                    if ( $courseObject->format == HM_Course_CourseModel::FORMAT_FREE) {
                        $lessonAssign->V_STATUS = 100;
                        $lessonAssign->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE;
                    } elseif( $lessonAssign->V_DONE == HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART ) {
                        $lessonAssign->V_STATUS = 0;
                        $lessonAssign->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS;
                    }

                    $this->getService('LessonAssign')
                        ->update($lessonAssign->getData());
                }
            }

            $aclService = $this->getService('Acl');
            $currentUserRole = $userService->getCurrentUserRole();
            $isEnduser = $aclService->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
            $isManager = $aclService->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_MANAGER);

            if(
                !$aclService->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
                //$this->getService('User')->getCurrentUserRole() != HM_Role_Abstract_RoleModel::ROLE_STUDENT
            ){
                $tmpSubjectId = 0;
            }else{
                $tmpSubjectId = $subjectId;
            }
            $current = $this->getService('CourseItemCurrent')->getCurrent($userId, $tmpSubjectId, $courseId, $lessonId);
            $this->view->current=$current;
            $isDegeneratedTree=$this->getService('CourseItem')->isDegeneratedTree($courseId);

            if ($courseObject->new_window && $isDegeneratedTree) {
                $this->_redirector->gotoSimple('view','item','course', array(
                    'subject_id' => $tmpSubjectId,
                    'course_id' => $courseId,
                    'item_id' => $current,
                    'lesson_id' => $lessonId
                ));
            }

            if ($this->view->current) {
                $this->view->itemCurrent = $this->getService('CourseItem')->getOne(
                    $this->getService('CourseItem')->find($this->view->current)
                );
            }
            $this->view->tree  = $course;
            $this->view->isDegeneratedTree = $isDegeneratedTree;

            // @tocheck
            if (!isset($this->_subject)) {
                $this->_subject = $this->getService('Subject')->getOne(
                    $this->getService('Subject')->find($subjectId)
                );
            }
            $this->view->setHeader($this->_subject->getName());

            if ($this->view->courseObject) {
                $this->view->setSubHeader($this->view->courseObject->getName());
            }

            //подключаем jQuery UI
            $this->view->jQuery()->enable()->uiEnable();

            /** @var HM_View_Helper_HM $HM */
            $HM = $this->view->HM();

            /** @var HM_Course_Item_ItemService $courseItemService */
            $courseItemService = $this->getService('CourseItem');
            if($lesson->typeID == HM_Event_EventModel::TYPE_LECTURE || $lesson->tool == HM_Event_EventModel::TYPE_LECTURE){
                $treeData = $courseItemService->getHmTreeData($courseId, $lesson->getModuleId());
            } else {
                $treeData = $courseItemService->getHmTreeData($courseId);
            }

            $bookmarking = false;
            $bookmarksList = array();
            $checkViewedUrl = null;
            if ($isEnduser) {
                $bookmarking = true;

                /** @var HM_Course_Bookmark_BookmarkService $courseBookmarkService */
                $courseBookmarkService = $this->getService('CourseBookmark');
                $bookmarks = $courseBookmarkService->getBookmarks($lessonId, $userId);
                $bookmarksList = array_keys($bookmarks->getList('item_id'));

                $checkViewedUrl = $this->view->url(array(
                    'module' => 'subject',
                    'controller' => 'ajax',
                    'action' => 'check-viewed-oids'
                ));
            }
            /*защита от прямого копирования html для учебных модулей из SCORM-пакетов (начало)*/
            $path = APPLICATION_PATH."/../public/unmanaged/COURSES/course".$courseId."/js/main.js";
            $pathSecond = APPLICATION_PATH."/../public/unmanaged/COURSES/course".$courseId."/scripts/user.js";
            $contents = file_get_contents($path);
            $contentsSecond = file_get_contents($pathSecond);
            $endContents = substr($contents,-40);
            $endContentsSecond = substr($contentsSecond,-40);
            $checkOncopy = preg_match('/oncopy/i',$endContents);
            $checkOncopySecond = preg_match('/oncopy/i',$endContentsSecond);
            if(!$checkOncopy){
                $contents .= "document.addEventListener('DOMContentLoaded', function(){if((e = document.querySelector('body')) !== null)e.setAttribute('oncopy', 'return false');});";
                file_put_contents($path,$contents);
            }
            if(!$checkOncopySecond){
                $contentsSecond .= "document.addEventListener('DOMContentLoaded', function(){if((e = document.querySelector('body')) !== null)e.setAttribute('oncopy', 'return false');});";
                file_put_contents($pathSecond,$contentsSecond);
            }
            /*(конец)*/

            $HM->create('hm.core.ui.trainingModulesViewer.Viewer', array(
                'renderTo' => '#hm-training-modules-viewer',
                'courseId' => $courseId,
                'lessonId' => $lessonId,
                'itemId' => $this->_getParam('item_id', null),
                'treeData' => $treeData,
                'openInWindow' => (int)$courseObject->new_window ? true : false,
                'extraNavigation' => (int)$courseObject->extra_navigation ? true : false,
                'bookmarking' => $bookmarking,
                'bookmarks' => $bookmarksList,
                //'fileButtonUrl' => '/upload/files/F30.pdf',
                'checkViewedUrl' => $checkViewedUrl,
                'showEditButton' => $isManager
            ));
        }
        $this->view->courseContent = true;
    }


    public function cardAction()
    {
        $subjectId = $this->_subject->subid;

        // Redirect #40476
        $this->_redirector->gotoUrl( $this
            ->view->url( array(
                    'module'     => 'subject',
                    'controller' => 'index',
                    'action'     => 'description',
                    'subject_id' => $subjectId
                ),
                null,
                true
            )
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

            /* Логирование захода пользователя в курс */
            $this->getService('Session')->toLog(array('course_id' => $subjectId));

            $graduated = $this->getService('Graduated')->fetchAll(array('CID = ?' => $subjectId, 'MID = ?' => $this->getService('User')->getCurrentUserId()));
            $this->view->graduated = count($graduated);

            $student = $this->getService('Student')->fetchAll(
                $this->getService('Student')->quoteInto(
                    array('MID = ?', ' AND CID = ?'),
                    array($this->getService('User')->getCurrentUserId(), $subjectId)
                )
            );

            if (!count($graduated) && !count($student)) {
                $this->view->withoutContextMenu = true;
            }
        }

        ///////////////

        $form = new HM_Form_DetailedView();
        $this->setDefaults($form);

        $cards    = $form->getFieldsArray();

        if (isset($cards['classifiers'])) {
            $classifiers = $this->getService('Classifier')
                ->fetchAllDependenceJoinInner('ClassifierLink', $this->quoteInto(
                    array('ClassifierLink.item_id = ?', ' AND ClassifierLink.type = ?', ' AND self.type <> ?'),
                    array($this->_subjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES)));
            $cardClassifiers = array();
            foreach ($classifiers as $classifier) {
                $cardClassifiers['classifier_' . $classifier->type] .=
                    ($cardClassifiers['classifier_' . $classifier->type] ? '<br>' : '') . $classifier->name;
            }
            foreach ($cards['classifiers']['fields'] as $legend => $value) {
                $cards['classifiers']['fields'][$legend] = isset($cardClassifiers[$value]) ? $cardClassifiers[$value] : "";
            }
        }


        $subjectTeachers = $this->getService('TcProviderTeacher')->fetchAllJoinInner('TeacherSubjects', 'TeacherSubjects.subject_id='.$subjectId);
        $teachers = array(array(_('ФИО'), _('Информация'), _('Контакты')));
        foreach($subjectTeachers as $teacher) {
            $teachers[] = $teacher->getValues(array('name', 'description', 'contacts'));
        }

        $cards['Fulltime_teachers'] = array(
            'title'  => _('Тьюторы'),
            'fields' => $teachers,
            'type'   => 'table'
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            foreach ($cards as $cardId => $card) {
                $cards[$cardId]['edit'] = $this->view->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'edit', 'subject_id' => $this->_subjectId), null, true);
            }
            $cards['Fulltime_teachers']['edit'] = $this->view->url(array('module' => 'teacher', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->_subjectId), null, true);
        }

        $this->view->cards = $cards;
        $this->view->icon = $this->_subject->getIcon();

        ///////////////

        $currentUserId = $this->getService('User')->getCurrentUserId();
        $userPrograms = $this->getService('Programm')->getUserProgramms($currentUserId);
        $userProgramIds = array();
        foreach ($userPrograms as $userProgram) {
            $userProgramIds[] = $userProgram['programm_id'];
        }
        if (empty($this->fromProgramArray)) {
            $programEventUsers = $this->getService('ProgrammEventUser')->fetchAllDependence(
                'ProgrammEvent',
                $this->getService('ProgrammEventUser')->quoteInto(
                    array(
                        ' user_id = ? ',
                        ' AND programm_id IN (?) '
                    ),
                    array(
                        $currentUserId,
                        empty($userProgramIds) ? array(0) : $userProgramIds
                    )
                )
            );

            foreach ($programEventUsers as $programEventUser) {
                if ($programEventUser->programmEvent && $programEventUser->programmEvent->current()->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                    $programEventId = $programEventUser->programmEvent->current()->programm_event_id;
                    $programEvent = $this->getService('ProgrammEvent')->find($programEventId)->current();
                    $this->fromProgramArray[] = $programEvent->item_id;
                }
            }
        }

        $this->view->fromProgramArray = $this->fromProgramArray;
        ///////////////

        $this->view->teachers = $this->getService('Subject')->getAssignedTeachers($subjectId);
        $this->view->detailed = $this->getRequest()->getParam('detailed');

    }

    /**
     * @param HM_Form $form
     * @throws Zend_Date_Exception
     * @throws Zend_Form_Exception
     */
    public function update(HM_Form $form)
    {
        $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $data = $form->getValues(false, $subject);
        if (HM_Subject_SubjectModel::PERIOD_FREE == $data['period']) {
            $data['begin'] = null;
            $data['end'] = null;
        } /*elseif(HM_Subject_SubjectModel::PERIOD_DATES == $data['period']) {
            $end = new HM_Date($data['end']);
            $data['end'] = $end->get('YYYY-MM-dd') . ' 23:59:59';
        }*/

        $subject = $this->getService('Subject')->update($data);

        $this->getService('Subject')->linkClassifiers($subject->subid, $form->getClassifierValues());
        $this->getService('Subject')->linkRoom($subject->subid, $form->getValue('rooms'));

        // изображения

        $form->saveBannerIcon();
        $icon = $form->getValue('icon');
        if ($icon != null) {
            HM_Subject_SubjectService::updateIcon(
                $subject->subid,
                $icon,
                null,
                false,
                $form->getElement('icon_delete')->getValue()
            );
        }

        //Обрезаем все занятия выходящие за рамки курса
        if( $subject->period == HM_Subject_SubjectModel::PERIOD_DATES ) {
            $this->getService('Lesson')->fitLessonDates($subject);
        }

    }

    public function setDefaults(Zend_Form $form)
    {
        if ($this->_subject){
            $values = $this->_subject->getValues();
            $values['tags'] = $this->getService('Tag')->getTags($this->_subjectId, $this->getService('TagRef')->getSubjectType());

            $cities = $this->getService('Classifier')->fetchAllDependenceJoinInner('ClassifierLink',
                $this->quoteInto(array('ClassifierLink.type = ? ',' AND ClassifierLink.item_id = ?',' AND self.type = ?'),
                    array(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $this->_subjectId, HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES)
                )
            )->getList('classifier_id', 'name');
            $values['city'] =  $cities ? $cities : '';

            $values['files']     = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $this->_subject->subid);
            $values['criterion'] = $this->setCriterionValue($values['criterion_type'], $values['criterion_id']);
            if ($values['criterion']) {
                $values['criterion_text'] = $values['criterion'][$values['criterion_type'] . '_' . $values['criterion_id']];
            }

            $values['begin'] = $this->_subject->date($values['begin']);
            $values['end']   = $this->_subject->date($values['end']);

            $form->getElement('icon')->setOptions(array('subject' => $this->_subject));
            $values['icon'] = $this->_subject->getIcon();

            $form->populate($values);
        }
    }

    private function setCriterionValue($criterionType, $criterionId)
    {
        switch ($criterionType) {
            case HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST:
                $criterion = $this->getService('AtCriterionTest')->getOne($this->getService('AtCriterionTest')->find($criterionId));
                $result = array($criterionType . "_" . $criterionId => $criterion->name);
                break;
            case HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION:
                $criterion = $this->getService('AtCriterion')->getOne($this->getService('AtCriterion')->find($criterionId));
                $result = array($criterionType . "_" . $criterionId => $criterion->name);
                break;
            default:
                $result = '';
                break;
        }

        return $result;

    }

    public function happyEndAction()
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

            $mark = $this->getService('SubjectMark')->setConfirmed($this->_subject->subid, $this->getService('User')->getCurrentUserId());
            if ($mark !== false) {
            
                $scaleId = $this->_subject->getScale();
                $value = (int)$mark->mark;

                $msg1 = _('Вы прошли данный курс.');

                if (
                    (($scaleId == HM_Scale_ScaleModel::TYPE_BINARY) && ($value == HM_Scale_Value_ValueModel::VALUE_BINARY_ON)) ||
                    (($scaleId == HM_Scale_ScaleModel::TYPE_TERNARY) && ($value == HM_Scale_Value_ValueModel::VALUE_TERNARY_ON))
                ) {
                    $msg1 = _('Вы успешно прошли данный курс.');

                } elseif ($value && ($scaleId == HM_Scale_ScaleModel::TYPE_CONTINUOUS)) {
                    $msg1 = _(sprintf('Вы выполнили весь план занятий и Вам автоматически выставлена итоговая оценка %s.',
                        "<span>{$value}</span>"
                    ));
                }

                $msg2 = $this->_subject->auto_graduate ? _('Система автоматически перевела Вас в прошедшие обучение по курсу.') : '';

                $this->view->msg = implode(' ', [_('Уважаемый пользователь!'), $msg1, $msg2]);
                $this->view->redirectUrl = $this->view->url(array('module' => 'subject', 'controller' => 'my', 'action' => 'index'), null, true);

                return true;
            }
        }
        $this->_redirector->gotoSimple('index', 'my', 'subject');
    }

    public function editAction()
    {
        if ($subid = $this->_getParam('subject_id')) {
            $this->_setParam('subid', $subid);
        }
        $subjectId = (int) $this->_getParam('subid', 0);

        $form = new HM_Form_Subjects();
        $request = $this->getRequest();

        $post = $request->getParams();
        // @todo: убрать этот хак после решения #31891
        if (empty($post['scale_id'])) $post['scale_id'] = HM_Scale_ScaleModel::TYPE_CONTINUOUS;

        if ($request->isPost()) {
            if ($form->isValid($post)) {

                $subjectId = $form->getValue('subid');
                $subject = $this->getService('Subject')->update(
                    array(
                        'subid' => $subjectId,
                        'name' => $form->getValue('name'),
                        'shortname' => $form->getValue('shortname'),
                        'supplier_id' => $form->getValue('supplier_id'),
                        'description' => $form->getValue('description'),
                        'external_id' => $form->getValue('external_id'),
                        'code' => $form->getValue('code'),
                        'type' => $form->getValue('type'),
                        'reg_type' => $form->getValue('reg_type'),
                        'begin' => $form->getValue('begin'),
                        'end' => $form->getValue('end'),
                        'price' => $form->getValue('price'),
                        'plan_users' => $form->getValue('plan_users'),
                        'period' => $form->getValue('period')
                    )
                );

                $this->getService('Subject')->linkClassifiers($subjectId, $form->getClassifierValues());
                $this->getService('Subject')->linkRooms($subjectId, $form->getValue('rooms'));
                if ($form->getValue('icon') != null) {
                    HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('icon'));
                }

                $this->_flashMessenger->addMessage(_('Учебный курс успешно изменён'));
                $this->_redirector->gotoSimple('description', 'index', 'subject', array('subject_id' => $subjectId));
            }
        } else {
            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            if ($subject) {
                $form->setDefaults(
                    $subject->getValues()
                );
            }
        }
        $this->view->form = $form;
    }

    public function incrementTestLimitAction()
    {
        $status = $this->_changeTestLimit($this->_getParam('MID',0), $this->_getParam('SHEID',false), $this->_getParam('subject_id',0));
         if ( $status ) {
             $this->_flashMessenger->addMessage(($status == 1)? _('Число попыток для пользователя успешно увеличено'): _('Нельзя превышать количество попыток, установленных в настройках теста'));
         } else {
             $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                     'message' => _('При выполнении операции произошла ошибка')));
         }
         $this->_redirector->gotoUrl($this->view->url(array('module' => 'subject','controller' => 'index','action' => 'result','subject_id'=>$this->_getParam('subject_id',0),'lesson_id'=>$this->_getParam('lesson_id',0)),null,true));
    }


    public function decrementTestLimitAction()
    {
    if ( $this->_changeTestLimit($this->_getParam('MID',0), $this->_getParam('SHEID',0), $this->_getParam('subject_id',0), 'decrement') ) {
             $this->_flashMessenger->addMessage(_('Число попыток для пользователя успешно уменьшено'));
         } else {
             $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                     'message' => _('При выполнении операции произошла ошибка')));
         }

         $this->_redirector->gotoUrl($this->view->url(array('module' => 'subject','controller' => 'index','action' => 'result','subject_id'=>$this->_getParam('subject_id',0),'lesson_id'=>$this->_getParam('lesson_id',0)),null,true));
    }

    /**
     * Изменение количества попыток пользователя пройти тест
     * @param $mid
     * @param $lessonId
     * @param $subjectId
     * @param string $operation
     * @return int 0-ошибка 1-успешно 2-успешно, результат установлен в 0 (поптка сделать меньше 0)
     */
    private function _changeTestLimit($mid, $lessonId, $subjectId, $operation = 'increment')
    {
        $status = 0;
        if (!$mid || !$lessonId || !$subjectId) return $status;
        $testCount = $this->getOne($this->getService('TestAttempt')->fetchAll(
                        $this->getService('TestAttempt')->quoteInto(
                            array('mid = ?', ' AND cid = ?', ' AND lesson_id = ?'),
                            array($mid, $subjectId, $lessonId)
                        )
                    ));
        if ( $testCount ) {
            $status = 1;
            $testCount->qty = ($operation == 'increment')? $testCount->qty - 1 : $testCount->qty + 1;
            if ($testCount->qty < 0) {
                $testCount->qty = 0;
                $status         = 2;
            }
            $this->getService('TestAttempt')->update($testCount->getValues());
            return $status;
        }
        return $status;
    }



    public function resultAction()
    {

        $subjectId = $this->_getParam('subject_id', 0);
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
                array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))
        ) {
            $isTeacher = true;
        } else {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        if($this->_getParam('progressgrid', '') != '' && strpos($this->_getParam('progressgrid', ''), '=') !==0){
            $this->_setParam('progressgrid', '=' . $this->_getParam('progressgrid', ''));
        }


/*
        $lessons = $this->getService('Lesson')->fetchAllJoinInner('Assign', 'Assign.MID = ' . (int) $userId . ' AND self.CID = ' . (int) $subjectId);
        if(count($lessons) == 1){
            $lesson = $this->getOne($lessons);
//            $this->_redirector->gotoUrl($lesson->getResultsUrl()); // не надо этого делать
        }
*/
        $select = $this->getService('Subject')->getSelect();

        $locale = Zend_Locale::findLocale();

        $columnOptions = array(
           'SHEID' => array('hidden' => true),
           'MID' => array('hidden' => true),
           'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink(
                $this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}',
                _('Карточка пользователя')).'{{fio}}'),
           'Title' => array(
               'title' => _('Название'),
            ),
               'typeID' => array(
                'hidden' => true,
                   'title' => _('Тип'),
                   'callback' => array(
                       'function' => array($this, 'updateType'),
                       'params' => array('{{typeID}}')
                   )
            ),
               'V_DONE' => array(
               'title' => _('Состояние'),
                   'callback' => array(
                       'function' => array($this, 'updateDoneStatus'),
                       'params' => array('{{V_DONE}}')
                   )
            ),
               'progress' => array(
               'title' => _('Результат'),
                   'callback' => array(
                       'function' => array($this, 'updateProgress'),
                       'params' => array('{{progress}}')
                   )
            ),
            'tryLast' => array(
                'title' => _('Дата последней попытки'),
                'format' => array(
                    'DateTime',
                    array('date_format' => Zend_Locale_Format::getDateTimeFormat($locale))
            ),
            ),
        );

        $group = array(
                            'schid.MID',
                            'sch.SHEID',
                            'sch.Title',
                            'sch.typeID',
                            'schid.V_STATUS',
                            'schid.V_DONE',
            'schid.launched',
                    );
        if ($isTeacher) {
            $select->joinInner(array('p' => 'People'), 'p.MID = schid.MID', array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")));
            array_push($group,'p.LastName', 'p.FirstName', 'p.Patronymic');
        } else {
            $select->where('schid.MID = ?', $userId);
            unset($columnOptions['fio']);
        }

        $select->from(array('schid' => 'scheduleID'), array('MID'))
            ->joinInner(array('sch' => 'schedule'),
                        'sch.SHEID = schid.SHEID',
                        array(
                                'SHEID',
                                'Title',
                                'typeID',
                                'V_DONE'         => 'schid.V_DONE',
                                'progress'       => 'schid.V_STATUS',
                                'tryLast'           => 'schid.launched',
                        ))
            ->where($this->quoteInto(array('sch.CID = ?'), array($subjectId,0)))
            ->group($group);

        $people = $this->getService('User')->fetchAllJoinInner('Student', 'Student.CID = ' . (int) $subjectId );
        $fios = array();
        foreach($people as $man){
            //Адский хак
            $fios['=' . $man->getName()] = $man->getName();
        }
        asort($fios);
        $lessons = $this->getService('Lesson')->fetchAll(array(
            'CID = ?' => $subjectId,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE
        ), 'title');
        $lessonsName = $lessons->getList('title', 'title');

        $statuses = array('0' => _('Не начат'), '2' => _('Пройден'), '1' => 'В процессе');

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'Title_ASC');
        }


        $filterOptions =  array(
               'fio' => array('values' => $fios),
               'Title' => array('values' => $lessonsName),
               'V_DONE' => array('values' => $statuses),
               'progress' => array(null),
               'tryLast' => array('render' => 'date')
//               'tryLast' => array('render' => 'DateTimeStamp')
        );

        if ( $this->_getParam('lesson_id',0) ) {
            $select->where('sch.SHEID = ?', $this->_getParam('lesson_id',0)); // занятие
            $columnOptions['Title'] = array('hidden' =>true);
            $columnOptions['V_DONE'] = array('hidden' =>true);
        } else {
            $select->where('sch.isfree = ?', HM_Lesson_LessonModel::MODE_FREE);
        }

        $grid = $this->getGrid(
            $select,
            $columnOptions,
            $filterOptions,
           'grid'
       );

        $grid->addAction(
            array('module' => 'subject', 'controller' => 'index', 'action' => 'redirect-result'),
            array('SHEID','MID'),
            _('Подробнее')
        );

//        $grid->updateColumn('tryLast', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
            $grid->updateColumn('Title',
                                 array(
                                       'callback' =>
                                        array(
                                            'function' => array($this, 'getTitleString'),
                                            'params' => array('{{Title}}','{{typeID}}')
                                        )
                                    )
                                );

        $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                        'params'   => array('{{progress}}')
                )
        );

        $this->view->grid = $grid;
    }


    public function updateActions($progress, $actions)
    {
        if ($progress) return $actions;
        }

    public function getTitleString($title,$typeID)
    {
        return '<span class="' . HM_Lesson_LessonModel::getIconClass($typeID) . '">' . $title . '</span>';
    }

    public function getTryCountString($count)
    {
        return (intval($count) > 0)? (int) $count : '';
    }

    public function getLeftCountString($count, $SHEID, $lessons) {
        $lesson = $lessons->exists('SHEID',$SHEID);
        $count = (int) $count;
        if ($lesson && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
            if (isset($this->_testsCache[$lesson->getModuleId()])) {
                $test = $this->_testsCache[$lesson->getModuleId()];
            } else {
            $test = $this->getService('Test')->getOne($this->getService('Test')->find($lesson->getModuleId()));
                $this->_testsCache[$lesson->getModuleId()] = $test;
            }
            $startLimit = (int) $test->startlimit;

            if ($startLimit == 0) {
                 return _('Без ограничения');
            } else {
                return ( ($startLimit - $count) > 0)? $startLimit - $count : 0;
            }
        }

        return '';
    }

    public function redirectResultAction()
    {
        $lessonId = $this->_getParam('SHEID', 0);
        $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
        if($lesson){
            $this->_redirector->gotoUrl($lesson->getResultsUrl(array(
                                                                       'user_id'    => $this->_getParam('MID', 0),
                   'subject_id' => $this->_getParam('subject_id', 0),
                   'userdetail' => 'yes',
            )));
        }
    }

    public function assignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $gridId = ($this->id) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        $section = $this->getService('Section')->getDefaultSection($subjectId);
        $currentOrder = $this->getService('Section')->getCurrentOrder($section);

        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $assign = $this->getOne(
                    $this->getService('SubjectCourse')->fetchAll(
                        $this->getService('SubjectCourse')->quoteInto(
                            array('subject_id = ?', ' AND course_id = ?'),
                            array($subjectId, $id)
                        )
                    )
                );
                if (!$assign) {
                    $this->getService('Subject')->linkCourse($subjectId, $id);
                    $this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                }

                $this->getService('Course')->createLesson($this->_subject->subid, $id, HM_Lesson_LessonModel::MODE_FREE, $section, ++$currentOrder);
                }
            }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));
    }

    public function unassignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $gridId = ($this->id) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {

                    $this->getService('Course')->clearLesson($this->_subject, $id);
                $this->getService('Subject')->unlinkCourse($subjectId, $id);
            }
        }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));

    }

    public function updateTypeColumn($gridSubjectId, $subjectId)
    {
        if ($gridSubjectId == $subjectId) {
            $return = _('Учебный курс');
        } else {
            $return = _('База знаний');
        }
        return "<span class='nowrap'>{$return}</span>";
    }

    public function subjectsListAction()
    {
        $ot     = $this->getRequest()->getParam('ot');
        $dean   = $this->getRequest()->getParam('dean');
        $userId = $this->getRequest()->getParam('user_id');
        $responsibility = $this->getService('Responsibility')->get($userId, HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT);

        $where = array(
            'type <> ?' => HM_Tc_Subject_SubjectModel::TYPE_FULLTIME
        );
        if ($ot) {
            $where['is_labor_safety =  ?'] = 1;
        } elseif ($dean) {
            $where['is_labor_safety != ?'] = 1;
        }

        $this->_disableLayout();

        /** @var HM_Subject_SubjectService $service */
        $service = $this->getService('Subject');

        $collections = $service->fetchAll($where, 'name');
        $subjects = $collections->getList('subid', 'name');

        $result = [];
        if (is_array($subjects) && count($subjects)) {
            $position = 0;
            foreach($subjects as $subjectId => $name) {
                $result[] = [
                    'id' => $subjectId,
                    'name' => $name,
                    'selected' => in_array($subjectId, $responsibility),
                    'level' => 1,
                    'lft' => ++$position,
                    'rgt' => ++$position,
                ];
            }
        }

        echo HM_Json::encodeErrorSkip($result);
        exit;
    }

    public function groupsListAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $responsibility = $this->getService('Responsibility')->get($userId, HM_Responsibility_ResponsibilityModel::TYPE_GROUP);
        $this->_disableLayout();

        /** @var HM_StudyGroup_StudyGroupService $service */
        $service = $this->getService('StudyGroup');

        $collections = $service->fetchAll([], 'name');
        $groups = $collections->getList('group_id', 'name');

        $result = [];
        if (is_array($groups) && count($groups)) {
            $position = 0;
            foreach($groups as $groupId => $name) {
                $result[] = [
                    'id' => $groupId,
                    'name' => $name,
                    'selected' => in_array($groupId, $responsibility),
                    'level' => 1,
                    'lft' => ++$position,
                    'rgt' => ++$position,
                ];
            }
        }

        echo HM_Json::encodeErrorSkip($result);
        exit;
    }

    public function programmsListAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $responsibility = $this->getService('Responsibility')->get($userId, HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM);
        $this->_disableLayout();

        $select = $this->getService('Programm')->getSelectElearningProgramms();
        $programs = $select->query()->fetchAll();

        $result = [];
        if (is_array($programs) && count($programs)) {
            $position = 0;
            foreach($programs as $index => $data) {
                $result[] = [
                    'id' => $data['programm_id'],
                    'name' => $data['name'],
                    'selected' => in_array($data['programm_id'], $responsibility),
                    'level' => 1,
                    'lft' => ++$position,
                    'rgt' => ++$position,
                ];
            }
        }

        echo HM_Json::encodeErrorSkip($result);
        exit;
    }

    public function coursesListAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->findDependence('CourseAssign', $subjectId));

        $q = urldecode($this->_getParam('q', ''));

        $this->_disableLayout();

        $where = 'Status = 1';
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where .= ' AND '.$this->getService('Course')->quoteInto('LOWER(Title) LIKE LOWER(?)', $q);
        }

        $collections = $this->getService('Course')->fetchAll($where, 'Title');
        $courses = $collections->getList('CID', 'Title');
        if (is_array($courses) && count($courses)) {
            $count = 0;
            foreach($courses as $courseId => $title) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($subject && $subject->isCourseExists($courseId)) {
                    $courseId .= '+';
                }
                echo sprintf("%s=%s", $courseId, $title);
                $count++;
            }
        }
    }

    /**
     * Смена режима прохождения курса
     **/
    public function changemodeAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $subject_id  = $this->_getParam('subject_id',0);
        $access_mode = (int) $this->_getParam('access_mode',0);

        $subject = $this->getService('Subject')
                        ->getOne( $this->getService('Subject')
                                       ->findDependence(array('Lesson',
                                                              'CourseAssign',
                                                              'ResourceAssign',
                                                              'TestAssign',
                                                              'TaskAssign'),
                                                        $subject_id));
        if ($subject) {
            $subject->access_mode = $access_mode;

            $this->getService('Subject')
                 ->update($subject->getValues());

            // Удаляем плановые занятия и информацию о их прохождении и создаем новые записи
            if ( $subject->access_mode == HM_Subject_SubjectModel::MODE_FREE ) {

                // Удаление: записи в SheduleID удаляются автоматом (onDelete' => self::CASCADE)
                if ( count($subject->lessons) ) {
                    foreach ( $subject->lessons as $lesson ) {
                        $this->getService('Lesson')->delete($lesson->SHEID);
                    }
                }

                // Создание занятий для уч. модулей
                if ( count($subject->courses) ) {
                    foreach ($subject->courses as $course) {
                        $this->getService('Course')->createLesson($subject->subid, $course->course_id);
                    }
                }

                // Создание занятий для ресурсов
                if ( count($subject->resources) ) {
                    foreach ($subject->resources as $resource) {
                        $this->getService('Resource')->createLesson($subject->subid, $resource->resource_id);
                    }
                }

                // Создание занятий для тестов
                if ( count($subject->tests) ) {
                    foreach ($subject->tests as $test) {
                        $this->getService('TestAbstract')->createLesson($subject->subid, $test->test_id);
                    }
                }
            }
        }

        $this->_redirector
             ->gotoUrl( $this->view
                             ->url( array('module'     => 'subject',
                                          'controller' => 'index',
                                          'action'     => 'card',
                                          'subject_id' => $subject_id),
                                    null,
                                    true));
    }

    public function changeStateAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $subjectId  = $this->_getParam('subject_id',0);
        $state = (int) $this->_getParam('state', 0);

        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->findDependence(array('Student'), $subjectId));
        if ($subject && $subject->isStateAllowed($state)) {

            switch ($state) {
                case HM_Subject_SubjectModel::STATE_ACTUAL:

                    $this->getService('Subject')->updateWhere(array(
                        'begin' => date('Y-m-d'),
                        'state' => $state,
                    ), array('subid = ?' => $subjectId));

                    foreach ($subject->students as $student) {
                        $this->getService('Subject')->startSubjectForStudent($subjectId, $student->MID);
                    }

                    break;
                case HM_Subject_SubjectModel::STATE_CLOSED:

                    $this->getService('Subject')->updateWhere(array(
                        'end' => date('Y-m-d H:i:s'),
                        'state' => $state,
                    ), array('subid = ?' => $subjectId));

                    foreach ($subject->students as $student) {
                        $this->getService('Subject')->assignGraduated($subjectId, $student->MID);
                    }

                    break;

                default:
                    // something wrong..
                    return false;
                    break;
            }
        }

        $this->_redirector
             ->gotoUrl( $this->view
                             ->url( array('module'     => 'subject',
                                          'controller' => 'index',
                                          'action'     => 'card',
                                          'subject_id' => $subjectId),
                                    null,
                                    true));

    }

    public function pinAction()
    {
        $this->pin($this->_getParam('subject_id'), $this->_getParam('uri'), true);
    }

    public function unpinAction()
    {
        $this->pin($this->_getParam('subject_id'), $this->_getParam('uri'), false);
    }

    protected function pin($subjectId, $uri, $pin = true)
    {
        if (!is_numeric($subjectId)) {
            $uriParts = explode('/', $uri);
            $subjectId = $uriParts[count($uriParts) - 1];
        }

        $this->getService('Subject')->setDefaultUri($pin ? $uri : null, $subjectId);

        exit('1');
    }

    public function statementAction()
    {
        $subjectID = $this->_getParam('subid', $this->_getParam('subject_id', 0));
        $subject   = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectID));

        if (!$subject) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не найден')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        if ($subject->state != HM_Subject_SubjectModel::STATE_CLOSED) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не закрыт')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        $score = $this->getService('Lesson')->getUsersScore($subject->subid ,'' ,'', null, true);
        $this->view->persons    = $score[0];
        $this->view->schedules  = $score[1];
        $this->view->scores     = $score[2];
    }

    public function descriptionAction()
    {
        if ($this->_isAccessible) {
            $this->view->replaceSidebar('subject', 'subject-updates', [
                'model' => $this->_subject,
                'order' => 100, // после Subject
            ]);
        }

        $feedbackResults = $this->getService('Feedback')->getFeedbackResultsForSubject($this->_subjectId);

        foreach ($feedbackResults as &$feedbackResult) {
            $user = $this->getService('User')->findOne($feedbackResult['user_id']);
            $user->photo = $user->getPhoto() ?: $user->getDefaultPhoto();
            $feedbackResult['date'] = (new HM_Date($feedbackResult['date']))->toString('YYYY-MM-dd HH:mm');

            $feedbackResult['user'] = $user->getData();
        }

        $aclService = $this->getService('Acl');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        $currentUserRole = $userService->getCurrentUserRole();
        $isDean = $aclService->inheritsRole($currentUserRole, [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        ]);

        $assignData = [
            'isDean' => $isDean,
            'feedbackData' => HM_Json::encodeErrorSkip($feedbackResults)
        ];

        $regBtnUrl = false;
        if (!$this->_isAccessible) {

            $this->view->setSubHeader(_('О курсе'));

            $descriptionUrl = urlencode($this->view->url(['module' => 'subject', 'controller' => 'index', 'action' => 'description', 'subid' => $this->_subject->subid], null, true));

            $isStudent  = $this->is('student', $userService->getCurrentUserId());
            $isClaimant = $this->is('claimant', $userService->getCurrentUserId());

            $o = new stdClass();
            if ($isStudent) {
                $o->text = _('Курс назначен');
                $o->isButton = false;
            } elseif ($isClaimant) {
                $o->text = _('Заявка на рассмотрении');
                $o->isButton = false;
            } elseif (!$isStudent && !$isClaimant && $this->_subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN) {
                if ($this->_subject->claimant_process_id) {
                    $o->text = _('Подать заявку');
                } else {
                    $o->text = _('Записаться');
                }
                $o->isButton = true;
            }

            $o->href = $this->view->url(array(
                'module'=> 'user',
                'controller' => 'reg',
                'action' => 'subject',
                'subid' => $this->_subject->subid,
                'redirect' => $descriptionUrl
            ), null, true);

            $assignData['regStatus'] = $o;
        }

        $this->view->assign($assignData);
    }

    protected function is($role, $userId)
    {
        $userId = (int) $userId;
        if(!$userId) return false;

        $sum = 'SUM(claim.status)';

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $sum = 'SUM(CAST(claim.status AS INT))';
        }


        $subjectsSelect = $this->getService('Subject')->getSelect()
            ->from(
                ['s' => 'subjects'],
                ['s.subid']
            )
            ->joinLeft(
                ['st' => 'Students'],
                'st.CID = s.subid and st.MID = '. $userId,
                ['isStudent' => new Zend_Db_Expr('CASE WHEN GROUP_CONCAT(st.SID) <> \'\' THEN 1 ELSE 0 END')]
            )
            ->joinLeft(
                ['claim' => 'claimants'],
                'claim.CID = s.subid and claim.MID = '. $userId,
                ['isClaimant' => new Zend_Db_Expr("CASE WHEN ((GROUP_CONCAT(claim.SID) <> '') AND (" . $sum . " = 0)) THEN 1 ELSE 0 END")]
            )
            ->group(['s.subid'])
            ->where('s.subid = ?', $this->_subject->subid)
            ->where('s.reg_type <> ?', 2);

        $subjects = $subjectsSelect->query()->fetchAll();

        if ($role == 'student') return $subjects[0]['isStudent'];
        if ($role == 'claimant') return $subjects[0]['isClaimant'];
        return false;
    }

    protected function _disableLayout()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
    }
}
