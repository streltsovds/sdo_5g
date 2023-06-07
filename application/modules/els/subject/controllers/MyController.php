<?php
/*
 * 5G
 *
 */
class Subject_MyController extends HM_Controller_Action
{
    const RANGE_NEW = 3; // days
    const MY_SUBJECTS_NAMESPACE = 'my-subjects';
    const SHOW_ALL_FILTER_NAME = 'show-all';

    public function indexAction()
    {
        $switcher = $this->_getParam('switcher', '');

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN)) && $switcher == 'programm') {
            $switcher = '';
        }

        $disabledSwitcherMods = array();
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            // Если пользователь не записан на программы, то сбросить switcher
            if (!count($programs = $this->getService('Programm')->getUserElsProgramms($this->getService('User')->getCurrentUserId()))) {
                $disabledSwitcherMods[] = 'programm';
                $switcher = 'list';
            } else {
                $currentUserId = $this->getService('User')->getCurrentUserId();
                $hasPassed = false;
                $hasElective = false;
                foreach ($programs as $program) {
                    $userEvents = $this->getService('ProgrammEventUser')->fetchAllDependence('ProgrammEvent', array(
                        'programm_id = ?' => $program['programm_id'],
                        'user_id = ?' => $currentUserId,
//                        'status != ?' => HM_Programm_Event_User_UserModel::STATUS_PASSED,
                    ));
                    foreach ($userEvents as $userEvent) {
                        if ($userEvent->status == HM_Programm_Event_User_UserModel::STATUS_PASSED) {
                            $hasPassed = true;
                        }
                        if (count($userEvent->programmEvent)) {
                            $programEvent = $userEvent->programmEvent->current();
                            if ($programEvent->isElective) {
                                $hasElective = true;
                            }
                        }
                    }
                }
                $switcher = (empty($switcher) && (!$hasPassed || !$hasElective)) ? 'program' : $switcher;
            }
        }

        if (empty($switcher)) $switcher = 'list';

        if ($switcher && $switcher == 'program') {
            $this->getHelper('viewRenderer')->setNoRender();
            $action = 'programAction';
            $this->_setParam('switcher', 'program');
            $this->$action();
            echo $this->view->render('my/program.tpl');
            return true;
        }

        $userService   = $this->getService('User');
        $currentUserId = $userService->getCurrentUserId();
        $isEndUser     = $this->getService('Acl')->inheritsRole(
            $userService->getCurrentUserRole(),
            [HM_Role_Abstract_RoleModel::ROLE_ENDUSER]
        );

        if (!$isEndUser) $this->_redirector->gotoSimple(
            'index',
            'list',
            'subject',
            ['base' => HM_Subject_SubjectModel::BASETYPE_PRACTICE]
        );

        $savedShowAll = HM_Search_FilterState::getValue(self::MY_SUBJECTS_NAMESPACE, self::SHOW_ALL_FILTER_NAME);
        if ($this->isAjaxRequest()) {
            $showAll = (!$this->getParam('showAll') || $this->getParam('showAll') == 'false') ? false : true;
        } else {
            $showAll = (!empty($savedShowAll) && $savedShowAll == 'true') ? true : false;
        }

        $subjectUserStatuses = [HM_Subject_User_UserModel::SUBJECT_USER_STUDENT];
        if ($showAll) {
            $subjectUserStatuses[] = HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED;
            $subjectUserStatuses[] = HM_Subject_User_UserModel::SUBJECT_USER_CLAIMANT;
        }

        $subjectUsers = self::getSubjectUsers($currentUserId, $subjectUserStatuses);
        $result = [];
        foreach ($subjectUsers as $key => $item) {
            if (!isset($result[$item->subject_id]) || (isset($result[$item->subject_id]) && ($item->status == 0))) {
                if (!$showAll) {
                    $student = $item->student->current();
                    $subject = $item->subject->current();
                    if ($subject->period != HM_Project_ProjectModel::PERIOD_FREE && ((strtotime($student->begin_personal) > strtotime(date('Y-m-d H:i:s'))) || (!is_null($student->end_personal) && (strtotime($student->end_personal) < strtotime(date('Y-m-d H:i:s'))))))
                    {
                        continue;
                    }
                }
                $result[$item->subject_id] = $item;
            }
        }
        $this->view->assign([
            'switcher' => $switcher,
            'subjectUsers' => $result,
            'disabledSwitcherMods' => $disabledSwitcherMods,
        ]);

        $this->view->addSidebar('subjectsCatalog');
    }

    public function programAction()
    {
        $switcher = $this->_getParam('switcher', 'program');
        $user = $this->getService('User')->getCurrentUser();
        $programs = $this->getService('Programm')->getUserProgramms($user->MID, HM_Programm_ProgrammModel::TYPE_ELEARNING);

        $result = [];
        foreach ($programs as $item) {
            $program = $this->getService('Programm')->findDependence('ProgrammEvents', $item['programm_id'])->current();
            if (!isset($result[$item['programm_id']]) && isset($program->programm_events)) { // программы без курсов не выводим
                $result[$item['programm_id']] = $program;
            }
        }

        $this->view->assign([
            'switcher' => $switcher,
            'programs' => $result
        ]);

        $this->view->addSidebar('subjectsCatalog');
    }

    static protected function getSubjectUsers($currentUserId, $subjectUserStatuses, $subjectIds = null)
    {
        $where = [
            'user_id = ?' => $currentUserId,
            'status IN (?)' => $subjectUserStatuses,
            'subject_id > ?' => 0
        ];

        if ($subjectIds && is_array($subjectIds) && count($subjectIds)) {
            $where['subject_id IN (?)'] = $subjectIds;
        }

        $subjectUsers = Zend_Registry::get('serviceContainer')->getService('SubjectUser')->fetchAllDependence(
            [
                'User',
                'Subject',
                'Claimant',
                'Student',
                'Graduated',
            ],
            $where
        );

        // кэш для отображения принадлежности курса к программе
        $collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->fetchAllDependence(
            'Programm',
            ['user_id = ?' => $currentUserId]);
        $programmEventUserIds = $collection->getList('programm_event_user_id', 'programm');

        $subjectIds = $subjectUsers->getList('subject_id');
        if (count($subjectIds)) {
            // кэш для отображения преподов
            $collection = Zend_Registry::get('serviceContainer')->getService('Subject')->fetchAllManyToMany(
                'User',
                'Teacher',
                ['subid IN (?)' => $subjectIds]);
            $teacherUsers = $collection->getList('subid', 'teachers');

            // кэш для отображения итоговых оценок
            $collection = Zend_Registry::get('serviceContainer')->getService('SubjectMark')->fetchAll([
                'cid IN (?)' => $subjectIds,
                'mid = ?' => $currentUserId
            ]);
            $subjectMarks = $collection->getList('cid', 'mark');

            // кэш для прогресса
            $lessonService = Zend_Registry::get('serviceContainer')->getService('Lesson');
            /** @var HM_Lesson_LessonService $lessonService */
            $subjectProgress = $lessonService->countPercentsAllSubjects($currentUserId, $subjectIds);

            /** @var HM_User_UserService */
            $userService = Zend_Registry::get('serviceContainer')->getService('User');
            /** @var HM_Lesson_Assign_AssignService */
            $lessonAssignService = Zend_Registry::get('serviceContainer')->getService('LessonAssign');

            $subjectAvailableLessons = [];
            $subjectAvailableLessonsCollection = $lessonService->fetchAllDependenceJoinInner(
                'Assign',
                $lessonService->quoteInto([
                    'Assign.MID = ?',
                    ' AND self.CID IN (?)',
                    ' AND self.isfree = ?'
                ], [
                        (int) $userService->getCurrentUserId(),
                        $subjectIds,
                        HM_Lesson_LessonModel::MODE_PLAN
                    ]
                )
            , ['order ASC']);

            foreach ($subjectAvailableLessonsCollection as $availableLesson) {
                $subjectAvailableLessons[$availableLesson->CID][] = $availableLesson;
            }
        }

        // init cache & assignments, filter empty
        $subjectUsers
            ->addcache('programmEventUserId2Programm', $programmEventUserIds)
            ->addCache('subjectId2TeacherUser', $teacherUsers)
            ->addCache('subjectId2Mark', $subjectMarks)
            ->addCache('subjectId2Progress', $subjectProgress)
            ->addCache('subjectId2AvailableLessons', $subjectAvailableLessons)
            ->init(); // important!
        // ->sort($sortFunction);

        return $subjectUsers;
    }

    static public function programPlainify($data = array(), $view = null)
    {
        $plainData = [];

        $user = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUser();

        /** @var HM_Programm_ProgrammModel $program */
        foreach ($data['programs'] as $program) {
            $isNew = false;
            $programUser = Zend_Registry::get('serviceContainer')->getService('ProgrammUser')->fetchAll([
                'programm_id = ?' => $program->programm_id,
                'user_id = ?' => $user->MID
            ]);

            if (count($programUser)) {
                $assignedAt = new HM_Date($programUser->current()->assign_date);
                $now = new HM_Date();
                $diff = $now->sub($assignedAt)->toValue();
                if (self::RANGE_NEW >= ceil($diff/60/60/24)) $isNew = true;
            }

            $graduatedCount = 0;
            $subjectIds = [];
            $subjects = Zend_Registry::get('serviceContainer')->getService('Programm')->getSubjects($program->programm_id);
            if (count($subjects)) {
                foreach ($subjects as $subject) {
                    $subjectIds[] = $subject->item_id;
                    $graduated = Zend_Registry::get('serviceContainer')->getService('Graduated')->fetchAll([
                        'CID = ?' => $subject->item_id,
                        'MID = ?' => $user->MID,
                    ]);
                    if (count($graduated)) $graduatedCount++;
                }
            }

            $subjectUserStatuses = [
                HM_Subject_User_UserModel::SUBJECT_USER_STUDENT,
                HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED,
                HM_Subject_User_UserModel::SUBJECT_USER_CLAIMANT
            ];

            $subjectUsers = self::getSubjectUsers($user->MID, $subjectUserStatuses, $subjectIds);
            $subjectUsersIds = $subjectUsers->getList('subject_id');
            $diff = array_diff($subjectIds, $subjectUsersIds);

            $result = [];
            foreach ($subjectUsers as $key => $item) {
                if (!isset($result[$item->subject_id]) || (isset($result[$item->subject_id]) && ($item->status == 0))) {
                    $result[$item->subject_id] = $item;
                }
            }

            $showRegButton = true;
            $subjectsPlainify = self::subjectsPlainify(['subjectUsers' => $subjectUsers], $view, $showRegButton, $program->programm_id);
            if (count($diff)) {
                foreach ($diff as $subjectId) {
                    $subject = Zend_Registry::get('serviceContainer')->getService('Subject')->find($subjectId)->current();
                    $subjectsPlainify['subjectUsers'][] = self::subjectPlainify($subject, null, $view, $showRegButton, $program->programm_id);
                }
            }

            $programData['name'] = $program->name;
            $programData['description'] = $program->description;
            $programData['icon'] = $program->getIcon();
            $programData['isNew'] = $isNew;
            $programData['graduatedCount'] = $graduatedCount;
//            if (count($subjectUsers)) {
                $programData['subjects'] = $subjectsPlainify;
//            }

            $plainData['programs'][] = $programData;
        }
        return $plainData;
    }

    static public function indexPlainify($data = array(), $view = null)
    {
        if (key_exists('programs', $data)) {
            $programPlainify = self::programPlainify($data, $view);
            usort($programPlainify['programs'], function($a, $b)
            {
                $isAfinished = $a['graduatedCount'] == count($a['subjects']['subjectUsers']);
                $isBfinished = $b['graduatedCount'] == count($b['subjects']['subjectUsers']);
                if ($isAfinished == $isBfinished) {
                    return $b['isNew'] <=> $a['isNew'];
                }
                return $isAfinished <=> $isBfinished;
            });
            return $programPlainify;
        }

        return self::subjectsPlainify($data, $view);
    }

    static public function subjectPlainify($subject, $subjectUser = null, $view = null, $showRegButton = false, $programId = false)
    {
        $isNew = false;
        if ($subjectUser) {
            $assignment = $subjectUser->getAssignment();
            if (is_a($assignment, 'HM_Role_StudentModel') && $assignment->time_registered) {
                $assignedAt = new HM_Date($assignment->time_registered);
                $now = new HM_Date();
                $diff = $now->sub($assignedAt)->toValue();
                if (self::RANGE_NEW >= ceil($diff/60/60/24)) $isNew = true;
            }
        } else {
            $isNew = true;
        }

        $teachers = [];
        // тьюторы
        foreach($subject->getCachedSubjectTeachers() as $teacherUser) {
            $teachers[] = [
                // фото (URL)
                'photo' => $teacherUser->getPhoto(),
                // ФИО
                'name' => $teacherUser->getName(),
                // ссылка на карточку
                'url' => $view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $teacherUser->MID]),
            ];
        }

        // доступные сейчас занятия
        $lessons = array();
        /** @var HM_Lesson_LessonModel $lesson */
        foreach($subject->getCachedSubjectAvailableLessons() as $lesson) {
            $lessons[] = [
                // название занятия
                'title' => $lesson->title,
                // тип занятия (определяет иконку)
                'type' => $lesson->getType(),
                'iconUrl' => $lesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM),
                // URL для запуска занятия
                'url' => $view->url(['module' => 'subject',
                    'controller' => 'lesson',
                    'action' => 'index',
                    'lesson_id' => $lesson->SHEID,
                    'subject_id' => $lesson->CID,
                ]),
            ];
        }

        $o = new stdClass();
        if ($showRegButton) {
            $isElective = false;
            $programEvent = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->fetchAll([
                'programm_id = ?' => $programId,
                'item_id = ?' => $subject->subid,
                'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT
            ]);
            if (count($programEvent)) {
                $isElective = $programEvent->current()->getValue('isElective');
            }

            $userService = Zend_Registry::get('serviceContainer')->getService('User');

            if ($isElective) {
                $isStudent  = self::is('student', $userService->getCurrentUserId(), $subject);
                $isClaimant = self::is('claimant', $userService->getCurrentUserId(), $subject);
                $descriptionUrl = urlencode($view->url([
                    'module' => 'subject',
                    'controller' => 'index',
                    'action' => 'description',
                    'subid' => $subject->subid
                ], null, true));

                if ($isStudent) {
                    $o->text = _('Курс назначен');
                    $o->isButton = false;
                } elseif ($isClaimant) {
                    $o->text = _('Заявка на рассмотрении');
                    $o->isButton = false;
                } elseif (!$isStudent && !$isClaimant && $subjectUser->status != HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED) {
                    if ($subject->claimant_process_id) {
                        $o->text = _('Подать заявку');
                    } else {
                        $o->text = _('Записаться');
                    }
                    $o->isButton = true;
                }

                $o->href = $view->url(array(
                    'module'=> 'user',
                    'controller' => 'reg',
                    'action' => 'subject',
                    'subid' => $subject->subid,
                    'redirect' => $descriptionUrl
                ), null, true);
            }
        }

        $plainData = [
            'regStatus' => $o,

            // статус [0 = заявка | 1 = слушатель | 2 = прошедший]
            'status' => $subjectUser ? $subjectUser->status : -1,

            'showAll' => $_GET['showAll'] ?: 'false',

            // кликуема ли ссылка + для фильтра "только активные"
            'isUnaccessible' => $subjectUser ? $subjectUser->isSubjectUnaccessible() : false,

            // для бейджа "New"
            'isNew' => $isNew,

            // итоговая оценка за курс
            'mark' => [
                // значение оценки
                'score' => $subjectUser && ($subjectUser->getCachedSubjectMark() !== false) ? $subjectUser->getCachedSubjectMark() : HM_Scale_Value_ValueModel::VALUE_NA,
                // шкала
                'scale_id' => $subject->getScale()
            ],

            // прогресс прохождения курса в процентах или false если заявка
            'progress' => $subjectUser ? $subjectUser->getCachedSubjectProgress() : false,

            // ----

            // название курса
            'subjectTitle' => $subject->name,

            // иконка курса (URL)
            'subjectIcon' => $subject->getIcon(),

            // ссылка с названия курса
            'subjectUrl' => $subject->getDefaultUri(),

            // ссылка с названия курса
            'subjectDescription' => $subject->short_description ? $view->escape($subject->short_description) : '',

            // программа (если курс назначен через программу); НЕТ В ЭСКИЗЕ!
            'subjectProgramm' => ($subjectUser && !empty($subjectUser->getCachedSubjectProgramm())) ? _('Программа') . ': ' . $subjectUser->getCachedSubjectProgramm() : null,

            // даты курса для конкретного пользователя (в различных вариантах - относительные, абсолютные и т.п.)
            'subjectDates' => $subjectUser ? $subjectUser->getSubjectDates() : null,

            // -----------

            // тьюторы курса
            'teachers' => $teachers,

            // занятия
            'lessons' => $lessons,
        ];

        return $plainData;
    }

    static public function subjectsPlainify($data = array(), $view = null, $showRegButton = false, $programId = false)
    {
        $plainData = [];

        /** @var HM_Subject_User_UserModel $subjectUser */
        foreach($data['subjectUsers'] as $i => $subjectUser) {
            /** @var HM_Subject_SubjectModel $subject */
            $subject = $subjectUser->getSubject();
            if (empty($subject)) continue;

            $plainData['subjectUsers'][] = self::subjectPlainify($subject, $subjectUser, $view, $showRegButton, $programId);
        }

        // разные ссылки
        $plainData['urls'] = [
            'catalog' => $view->url(['module' => 'subject', 'controller' => 'catalog', 'sort' => null, 'switcher' => null]),
        ];

        $savedShowAll = HM_Search_FilterState::getValue(self::MY_SUBJECTS_NAMESPACE, self::SHOW_ALL_FILTER_NAME);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $showAll = $request->getParam('showAll', $savedShowAll);

        $plainData['showAll'] = $showAll;

        HM_Search_FilterState::setValue(self::MY_SUBJECTS_NAMESPACE, self::SHOW_ALL_FILTER_NAME, $showAll);

        $plainData['scales'] = [
            'continuous' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
            'binary' => HM_Scale_ScaleModel::TYPE_BINARY,
            'ternary' => HM_Scale_ScaleModel::TYPE_TERNARY,
        ];

        return $plainData;
    }

    static protected function is($role, $userId, $subject)
    {
        $sum = 'SUM(claim.status)';

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $sum = 'SUM(CAST(claim.status AS INT))';
        }


        $subjectsSelect = Zend_Registry::get('serviceContainer')->getService('Subject')->getSelect()
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
            ->where('s.subid = ?', $subject->subid)
            ->where('s.reg_type <> ?', 2);

        $subjects = $subjectsSelect->query()->fetchAll();

        if ($role == 'student') return $subjects[0]['isStudent'];
        if ($role == 'claimant') return $subjects[0]['isClaimant'];
        return false;
    }
}
?>