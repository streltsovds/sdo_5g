<?php

class HM_View_Infoblock_ScheduleDailyBlock extends HM_View_Infoblock_Abstract
{
    CONST LESSON_LIMIT = 20;

    protected $id = 'schedule';

    public function scheduleDailyBlock($param = null)
    {
        $teachers          =
        $subjects          =
        $subjectNames      =
        $sequence          =
        $students          =
        $scheduleIds       =
        $lessonsCollection =
        $lessonAssignPlain =
        $lessons           = [];
        $bTooMany          = false;
        $begin             = strtotime(date('Y-m-d'));
        $end               = $begin + 60*60*24;

        $lessonService     = $this->getService('Lesson' );
        $userService       = $this->getService('User'   );
        $aclService        = $this->getService('Acl'    );
        $subjectService    = $this->getService('Subject');
        $teacherService    = $this->getService('Teacher');

        $currentUserId     = (int) $userService->getCurrentUserId();
        $currentUserRole   = $userService->getCurrentUserRole();
        $isTeacher         = $aclService->inheritsRole($currentUserRole, [HM_Role_Abstract_RoleModel::ROLE_TEACHER]);
        $fioExpression     = "CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)";

        // #12475
        $rows = $isTeacher ?
            $this->getCurrentActiveLessons($begin, $currentUserId) :
            $this->getCurrentActiveLessonsStudent($begin, $end, $currentUserId);

        if (!empty($rows)) {
            $select = $teacherService->getSelect();
            $select->from(['t' => 'Teachers']);
            $select->joinLeft(['p' => 'People'],
                'p.MID =   t.MID', [
                    'id'    => 'p.MID',
                    'fio'   => new Zend_Db_Expr($fioExpression)
                ]
            );

            $res = $select->query()->fetchAll();
            foreach ($res as $row) $teachers += [$row['id'] => $row['fio']];
            // Формируем коллекцию заранее, что бы потом не обращатся к БД в цикле
            foreach ($rows as $row) $scheduleIds[] = $row['SHEID'];

            $where = $isTeacher ? ['Assign'] : ['Assign' => $currentUserId];
            $lessonsColl = $lessonService->fetchAllDependence($where, [
                'SHEID IN(' . implode(',', $scheduleIds) . ')'
            ]);

            // Возможно данное действие и лишнее, но оно гаррантирует соответствие "SHEID"
            foreach ($lessonsColl as $lesson) $lessonsCollection[$lesson->SHEID] = $lesson;

            unset($scheduleIds, $lessonsColl);

            foreach ($rows as $row) {
                /** @var $lesson HM_Lesson_LessonModel */
                $lesson = $lessonsCollection[$row['SHEID']];
                if (!$lesson) continue;

                $students[$lesson->SHEID] = [];

                if (!$isTeacher) {
                    $lesson->teacher = $row['teacher'] ? ['id' => $row['teacher'], 'fio' => $teachers[$row['teacher']]] : false;
                }

                if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_FREE) {
                    $lesson->begin = date('Y-m-d H:i', $begin);
                    $lesson->end   = date('Y-m-d H:i', $end);
                }

                if ( $lesson->isConditionalLesson() &&
                    !$lessonService->isLaunchConditionSatisfied($lesson->SHEID, $lesson, false) &&
                    !$isTeacher) continue;

                if (!$isTeacher && ($row['mark'] == -1) &&
                     $lesson->vedomost &&
                    !$lesson->recommend &&
                    !$lesson->isTimeFree() &&
                     strtotime($lesson->end) &&
                    (strtotime($lesson->end) < time())
                ) {
                    $prefix = '0';
                    $lesson->overdue = true;
                } else {
                    $prefix = $lesson->recommend ? '9' : '';
                    $lesson->overdue = false;
                }

                if (isset($sequence[$row['CID']])) {
                    $lessons = $subjects[$sequence[$row['CID']]]['lessons'];
                    unset($subjects[$sequence[$row['CID']]]);
                }
                $key = $row['subject'] . $row['begin'] . $row['CID'];
                $sequence[$row['CID']]        = $key;
                $subjectNames[$row['CID']]    = $row['subject'];
                $subjects[$key]['title']      = $row['subject'];
                $subjects[$key]['subject_id'] = $row['CID'];

                $lessonData = $lesson->getDataLessonForPreview();
                $lessonData['url'] = $this->view->url([
                    'action'     => 'index',
                    'controller' => 'execute',
                    'module'     => 'lesson',
                    'lesson_id'  => $lesson->SHEID,
                    'subject_id' => $lesson->CID
                ], false, true);
                $lessons[str_pad($row['order'], 3, '0', STR_PAD_LEFT) . $prefix . $row['begin'] . $row['SHEID']] = $lessonData;

                $subjects[$key]['lessons'] = $lessons;
                $subjects[$key]['lessonsCount'] = count($lessons);
                $subjects[$key]['url'] = $subjectService->getDefaultUri($subjects[$key]['subject_id']);;

                //#17314
                if (count($lessons) >= self::LESSON_LIMIT) {
                    $bTooMany = true;
                    break;
                }
            }

            if (is_array($subjects)) ksort($subjects);
        }

        // #7882
        if ($isTeacher && count($students)) {
            $select = $userService->getSelect();
            $select->from(['s' => 'schedule'], [
                's.SHEID',
                'p.MID',
                'fio' => new Zend_Db_Expr($fioExpression),
                'st.time_registered',
                'sid.begin_personal',
                'sid.end_personal']
            )->joinInner(['sid' => 'scheduleID'], 'sid.SHEID = s.SHEID', [])
             ->joinInner(['st'  => 'Students'], 'st.CID = s.CID AND st.MID = sid.MID', [])
             ->joinInner(['p'   => 'People'], 'p.MID = sid.MID', [])
             ->where('s.SHEID IN (?)', array_keys($students));

            $rows = $select->query()->fetchAll();
            if (!empty($rows)) {
                $students = [];
                foreach ($rows as $row) {
                    $students[$row['SHEID']][$row['MID']] = [
                        'fio'            => $row['fio'],
                        'regtime'        => $row['time_registered'],
                        'begin_personal' => $row['begin_personal'],
                        'end_personal'   => $row['end_personal']
                    ];
                }
            }
        }

        foreach ($lessons as $lessonKey => $lessonArray) {
            /** @var HM_Lesson_LessonModel $lesson */
            $lesson      = $lessonArray['lesson'];

            /** @var HM_Lesson_Assign_AssignModel $lessonAssign */
            $lessonAssign = $isTeacher
                ? $lesson->getTeacherAssign()
                : $lesson->getStudentAssign($currentUserId);

            //
            if (!$lessonAssign) {
                continue;
            }

            $subjectName = $subjectNames[$lesson->CID];
            $lessonAssignPlain[$subjectName][] = [
                'lessonId'           => $lesson->SHEID,
                'lessonTitle'        => $lesson->getName(),
                'lessonDescription'  => nl2br($lesson->getDescription()),
                'lessonDate'         => $lessonAssign->getBeginEnd(),
                'beginTime'          => $isTeacher ? $lesson->getBeginTime() : $lessonAssign->getBeginTime(),
                'endTime'            => $isTeacher ? $lesson->getEndTime() : $lessonAssign->getEndTime(),
                'lessonCondition'    => $lessonAssign->getCachedLaunchCondition(),
                'lessonComment'      => $lessonAssign->getComment(),
                'lessonScoreHistory' => $lessonAssign->getCachedMarkHistory(),
                'isNewWindow'        => $lesson->isNewWindow(),
                'isNotStrict'        => $lesson->recommend,
                'isPenalty'          => (bool) $lesson->getFormulaPenaltyId(),
                'isScoreable'        => (bool) $lesson->vedomost,
                'isPassed'           => $lessonAssign->getScore() != HM_Scale_Value_ValueModel::VALUE_NA,
                'resultUrl'          => $lesson->getResultsUrl(),
                'executeUrl'         => $lesson->getExecuteUrl(),
                    'lessonScore'    => [
                        'score'          => $lessonAssign->getScore(),
                        'scale_id'       => $lesson->getScale()
                ],
                'isResultURLEnabled' => $lessonAssign->getCachedLogEnabled() &&
                                    $lesson->getResultsUrl() &&
                                    $lesson->isResultInTable(),
                'iconUrl'            => $lesson->getUserIcon() ?
                                        $lesson->getUserIcon() :
                                        $lesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM),
            ];
        }

        $data['lessonAssigns'] = $lessonAssignPlain;

        $data['lessonLimit']   = self::LESSON_LIMIT;
        $data['lessonCount']   = count($lessons) + ($bTooMany ? 1 : 0);
        $data['subjectsCount'] = count($subjects);
        $data['subjects']      = $subjects;
        $data['begin']         = date('d.m.Y', $begin);
        $data['end']           = date('d.m.Y', $end);
        $data['students']      = $students;

        $this->view->data      = $data;

        return $this->view->render('scheduleDailyBlock.tpl');
    }

    public function getNotCachedContent()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/schedule-daily/style.css');

        if (!$this->view->ajax) {

            $this->view->inlineScript()->captureStart();
            echo <<<JS
            function reloadscheduledaily() {
                $('#schedule-daily #schedule-daily-wrapper-1').load('/infoblock/schedule/index/begin/'+$('#infoblock-schedule-daily-begin').val().replace('.','-').replace('.','-').replace('.','-'));
            }
JS;
            $this->view->inlineScript()->captureEnd();
        }
    }

    /**
     * Функция выбирает занятия с учетом новых параметров.
     * примерно так:
     * Курсы с ручным стартом должны иметь статус "Идет"
     * Курсы со строгим соответствием должны совпадать по датам.
     * Курсы с фиксированной длинной должны иметь дату начала не раньше сегодня + longtime
     * остальные или PERIOD_FREE,PERIOD_DATE AND PERIOD_RESTRICTION_DECENT
     * Занятия в курсах timetype (2)
     * Занятия в курсах recommend = 1
     * Занятия в курсах timetype (0,3) и сейчас между началом и концом
     * Занятия в курсах timetype (1) и сейчас между день начала+день начала курса и день начала+день конца курса.
     *
     * @auhtor Artem Smirnov <tonakai.personal@gmail.com>
     * @date 10.01.2013
     *
     * @param $nowTime
     * @param $currentUserId
     *
     * @return array
     */
    public function getCurrentActiveLessons($nowTime, $currentUserId)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $day = 60*60*24;
        $select = $userService->getSelect();
        $select->from(array('s' => 'schedule'), array(
            's.*',
            'subject' => 'subjects.name',
            'subject_begin' => 'UNIX_TIMESTAMP(subjects.begin)',
            'subject_end' => 'UNIX_TIMESTAMP(subjects.end)',
        ))
            ->joinInner('subjects', 's.CID = subjects.subid', array())
            ->joinInner('Teachers', 's.CID = Teachers.CID', array())
            ->where('Teachers.MID = ?', $currentUserId)
            ->where($userService->quoteInto('s.teacher = ?', $currentUserId))
            ->where($userService->quoteInto(array(
                    '((subjects.period_restriction_type = ? ', 'AND subjects.state = ?) OR',
                    '(subjects.period_restriction_type = ? ', 'AND UNIX_TIMESTAMP(subjects.begin) <= ? ', 'AND UNIX_TIMESTAMP(subjects.end) > ?) OR',
                    '(subjects.period = ? ','AND UNIX_TIMESTAMP(subjects.begin) <= ? ', 'AND UNIX_TIMESTAMP(subjects.begin) + subjects.longtime*60*60*24 > ?) OR',
                    '(subjects.period = ?) OR',
                    '(subjects.period_restriction_type = ?))'
                ), array(
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL, HM_Subject_SubjectModel::STATE_ACTUAL,
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT, $nowTime, $nowTime,
                    HM_Subject_SubjectModel::PERIOD_FIXED, $nowTime, $nowTime,
                    HM_Subject_SubjectModel::PERIOD_FREE,
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT
                )))
            ->where($userService->quoteInto(
                    array(
                        '(s.timetype IN (?)',
                        ' OR (s.timetype IN (?)',
                        ' AND GREATEST(UNIX_TIMESTAMP(s.begin), ?)',
                        ' < LEAST(UNIX_TIMESTAMP(s.end), ?) ))'
                    ),
                    array(
                        array(HM_Lesson_LessonModel::TIMETYPE_FREE, HM_Lesson_LessonModel::TIMETYPE_RELATIVE),
                        array(HM_Lesson_LessonModel::TIMETYPE_TIMES, HM_Lesson_LessonModel::TIMETYPE_DATES),
                        $nowTime,
                        $nowTime + $day
                    )));
        $lessons = $select->query()->fetchAll();
        $rows = array();

        //решил сделать так, а не условием в sql потому что толком не знаю, можно ли использовать конструкции case end
        //убирает лишние уроки с относительными диапазонами, которые закончились или не начинались
        if (!empty($lessons))
            foreach ($lessons as $id => $lesson)
            {
                $passed = true;
                if ($lesson['timetype'] == HM_Lesson_LessonModel::TIMETYPE_RELATIVE)
                {
                    if ($lesson['startday'] > 0)
                    {
                        if ($nowTime < $lesson['subject_start'] + $lesson['startday']*$day)
                        {
                            $passed = false;
                        }
                    }
                    else
                    {
                        if ($nowTime < $lesson['subject_end'] + $lesson['startday']*$day)
                        {
                            $passed = false;
                        }
                    }
                    if ($lesson['stopday'] > 0)
                    {
                        if ($nowTime > $lesson['subject_start'] + $lesson['stopday']*$day)
                        {
                            $passed = false;
                        }
                    }
                    else
                    {
                        if ($nowTime > $lesson['subject_end'] + $lesson['stopday']*$day)
                        {
                            $passed = false;
                        }
                    }
                }
                if ($passed) {
                    $rows[$id] = $lesson;
                }
            }

        return $rows;
    }

    /**
     * Функция выбирает занятия для слушателей.
     * алгоритм не изменен, просто вынесен в отдельный блок
     *
     * @auhtor Artem Smirnov <tonakai.personal@gmail.com>
     * @date 29.12.2012
     *
     * @param $begin
     * @param $end
     * @param $currentUserId
     *
     * @return array
     */
    public function getCurrentActiveLessonsStudent_($begin, $end, $currentUserId)
    {
        $select = $this->getService('User')->getSelect();
        $select->from(array('s' => 'schedule'), array(
            's.*',
            'subject' => 'subjects.name',
            'regtime' => 'Students.time_registered',
            'mark' => 'scheduleID.V_STATUS',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(People.LastName, ' ') , People.FirstName), ' '), People.Patronymic)"),
        ))
            ->joinInner('subjects', 's.CID = subjects.subid', array())
            ->joinInner('scheduleID', 's.SHEID = scheduleID.SHEID')
            ->joinInner('Students', 'subjects.subid = Students.CID', array())
            ->joinLeft('Teachers', 's.CID = Teachers.CID', array())
            ->joinLeft('People','Teachers.MID = People.MID')
            ->where('Students.MID = ?', $currentUserId)
            ->where('scheduleID.MID = ?', $currentUserId)
            ->where('scheduleID.V_STATUS = -1')
            ->where('isfree = ? OR isfree IS NULL', HM_Lesson_LessonModel::MODE_PLAN);
        $subjectPeriodWhere = $this->getService('Subject')->quoteInto(
            array(
                '(subjects.period IN (?',
                ', ?))',
                ' OR ( subjects.period = ?',
                ' AND (UNIX_TIMESTAMP(subjects.begin) <= ?)',
                ' AND (UNIX_TIMESTAMP(subjects.end) > ? or subjects.end is null))',
//                        ' OR ( subjects.period = ?',
//                        ' AND (UNIX_TIMESTAMP(subjects.begin) <= ?)',
//                        ' AND (UNIX_TIMESTAMP(subjects.end) > ?))',
            ),
            array(
                HM_Subject_SubjectModel::PERIOD_FIXED,
                HM_Subject_SubjectModel::PERIOD_FREE,
                HM_Subject_SubjectModel::PERIOD_DATES,
                $begin,
                $begin,
//                        HM_Subject_SubjectModel::PERIOD_DATES,
//                        $begin,
//                        $begin,
            )
        );
        $select->where($subjectPeriodWhere);
        $select->where(
            $this->getService('User')->quoteInto(
                array(
                    '(s.timetype IN (?)',
                    ' OR (s.timetype IN (?)',
                    ' AND GREATEST(UNIX_TIMESTAMP(s.begin), ?)',
                    ' < LEAST(UNIX_TIMESTAMP(s.end), ?) ))'
                ),
                array(
                    array(HM_Lesson_LessonModel::TIMETYPE_FREE, HM_Lesson_LessonModel::TIMETYPE_RELATIVE),
                    array(HM_Lesson_LessonModel::TIMETYPE_TIMES, HM_Lesson_LessonModel::TIMETYPE_DATES),
                    $begin,
                    $end
                )
            )
        );
        return $select->query()->fetchAll();
    }

    // радикально упростил запрос исходя из следующих предположений:
    // 1. адекватные данные в Students; если время обучения по курсу закончилось, а чел еще в Students - это вопрос к HM_Crontask_Task_Graduate
    // 2. правильные даты в scheduleID.begin_personal и scheduleID.end_personal
    public function getCurrentActiveLessonsStudent($begin, $end, $currentUserId)
    {
        $select = $this->getService('Student')->getSelect();
        $select->from(array('s' => 'Students'), array(
            'schedule.*',
            'subject' => 'subjects.name',
            'regtime' => 's.time_registered',
            'mark' => 'scheduleID.V_STATUS',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(People.LastName, ' ') , People.FirstName), ' '), People.Patronymic)"),
        ))
            ->joinInner('subjects', 's.CID = subjects.subid', array())
            ->joinInner('schedule', 'subjects.subid = schedule.CID', array())
            ->joinInner('scheduleID', 'schedule.SHEID = scheduleID.SHEID', array())
            ->joinLeft('Teachers', 's.CID = Teachers.CID', array())
            ->joinLeft('People','Teachers.MID = People.MID', array())
            ->where('s.MID = ?', $currentUserId)
            ->where('scheduleID.MID = ?', $currentUserId)
            ->where('scheduleID.V_STATUS = ?', -1)
            ->where('(schedule.isfree = ? OR schedule.isfree IS NULL)', HM_Lesson_LessonModel::MODE_PLAN)
        ;

        // UNIX_TIMESTAMP < 0 - это когда занятие не ограничено по времени (1901 год)
        $select->where(
            $this->getService('Student')->quoteInto(
                array('
                    (
                        (UNIX_TIMESTAMP(scheduleID.begin_personal) <= 0 AND UNIX_TIMESTAMP(scheduleID.end_personal) <= 0) OR 
                        (GREATEST(UNIX_TIMESTAMP(scheduleID.begin_personal), ?)', ' < LEAST(UNIX_TIMESTAMP(scheduleID.end_personal), ?))
                    )
                '),
                array(
                    $begin,
                    $end
                )
            )
        );

        return $select->query()->fetchAll();
    }

}