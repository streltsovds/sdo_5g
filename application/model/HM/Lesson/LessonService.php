<?php
class HM_Lesson_LessonService extends HM_Service_Abstract
{
    protected $_cache = array(
        'getLesson' => array()
    );

    protected $proctoringAssigns = array();
    protected $isProctoringTransaction = false;

    public function beginProctoringTransaction(){
        $this->isProctoringTransaction = true;
        $this->proctoringAssigns = array();
    }

    public function commitProctoringTransaction(){
        foreach($this->proctoringAssigns as $lessonId=>$lesson) { //$lesson - список людей, нам пока не нужен, тк в екласс отправляются все по занятию
            $this->getService('Proctoring')->pushEvents($lessonId);
        }        

        $this->isProctoringTransaction = false;
        $this->proctoringAssigns = array();
    }

    public function insert($data, $unsetNull = true)
    {
        $data = $this->_processGroupDate($data);
        $data = $this->_processCondition($data);

        $data['max_mark'] = $data['vedomost'] ? $this->getMaxMark($data['typeID']) : 0;

        if (Zend_Controller_Front::getInstance()->getRequest()->getParam('has_proctoring',0)) {
            $data['has_proctoring'] = 1;
            $data['teacher'] = $this->getService('User')->getCurrentUserId();
        }

        $result = parent::insert($data);

        if ($result) {
            $lesson = $this->getService('LessonAssign')->insert(array('SHEID' => $result->SHEID, 'MID' => 0));
            $this->getService('Subject')->setLastUpdated($lesson->CID);

            if ($result->has_proctoring) {
                $lessonData = $result->getData();
                $config = Zend_Registry::get('config');
                $proctoringServers = array_keys($config->proctoring->servers->toArray());
                $lessonData['proctoring_server'] = $proctoringServers[$result->SHEID%count($proctoringServers)];
                $this->update($lessonData);
            }
        }
        return $result;
    }

    public function update($data, $unsetNull = true)
    {
        $data = $this->_processCondition($data);
        $data = $this->_processGroupDate($data);
        $data['max_mark'] = $data['vedomost'] ? $this->getMaxMark($data['typeID']) : 0;

        $lesson = parent::update($data, $unsetNull);

        if ($lesson && $lesson->CID) {
            $this->getService('Subject')->setLastUpdated($lesson->CID);
        }
        return $lesson;
    }

    public function createEmpty($title, $subjectId)
    {
        $defaults = array(
            'title' => $title,
            'descript' => '',
            'begin' => date('Y-m-d 00:00:00'),
            'end' => date('Y-m-d 23:59:00'),
            'createID' => $this->getService('User')->getCurrentUserId(),
            'createDate' => date('Y-m-d H:i:s'),
            'typeID' => HM_Event_EventModel::TYPE_EMPTY,
            'vedomost' => 1,
            'CID' => $subjectId,
            'startday' => 0,
            'stopday' => 0,
            'timetype' => 2,
            'isgroup' => 0,
            'teacher' => 0,
            'params' => '',
            'all' => 1,
            'cond_sheid' => '',
            'cond_mark' => '',
            'cond_progress' => 0,
            'cond_avgbal' => 0,
            'cond_sumbal' => 0,
            'cond_operation' => 0,
            'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
        );

        $lesson = $this->getService('Lesson')->insert($defaults);

        $students = $lesson->getService()->getAvailableStudents($subjectId);
        if (is_array($students) && count($students)) {
            $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
        }
//[ES!!!] //array('lesson' => $lesson))

        return $lesson;
    }

    public function resetMaterialFields($lessonId)
    {
        $lesson = $this->update([
            'SHEID' => $lessonId,
            'typeID' => HM_Event_EventModel::TYPE_EMPTY,
            'params' => '',
            'cond_sheid' => '',
            'cond_mark' => '',
            'cond_progress' => 0,
            'cond_avgbal' => 0,
            'cond_sumbal' => 0,
            'cond_operation' => 0,
            'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
        ]);

        return $lesson;
    }

    protected function getMaxMark($typeID)
    {
        if (in_array($typeID,array(HM_Event_EventModel::TYPE_RESOURCE, HM_Event_EventModel::TYPE_LECTURE, HM_Event_EventModel::TYPE_COURSE))) {
            return 1;
        } else {
            return 100;
        }
    }

    protected function _processCondition($data)
    {
        if (isset($data['Condition'])) {
            switch($data['Condition']) {
            case HM_Lesson_LessonModel::CONDITION_NONE:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_progress'] = '0';
                $data['cond_avgbal'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_PROGRESS:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_avgbal'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_AVGBAL:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_progress'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_SUMBAL:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_progress'] = '0';
                $data['cond_avgbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_LESSON:
                $data['cond_progress'] = '0';
                $data['cond_avgbal'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            }
            unset($data['Condition']);
        }
        unset($data['Condition']);
        return $data;
    }

    protected function _processGroupDate($data)
    {
        if (isset($data['GroupDate'])) {

            $data['startday'] = '';
            $data['stopday'] = '';
            switch($data['GroupDate']) {

                case HM_Lesson_LessonModel::TIMETYPE_FREE:
                    $data['begin'] = $this->getDateTime();
                    $data['end'] = $data['begin'];
                    $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_FREE;
                    break;

                case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                    try {
                        $begin = new HM_Date($data['currentDate'].' '.$data['beginTime']);
                    } catch(Zend_Date_Exception $e) {
                        $begin = new HM_Date();
                    }
                    try {
                        $end = new HM_Date($data['currentDate'].' '.$data['endTime']);
                    } catch (Zend_Date_Exception $e) {
                        $end = new HM_Date();
                    }
                    $data['begin'] = $begin->toString('YYYY-MM-dd HH:mm');
                    $data['end'] = $end->toString('YYYY-MM-dd HH:mm');
                    $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_DATES;

                    break;

                case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                    $data['begin'] = $this->getDateTime();
                    $data['end'] = $this->getDateTime();
                    $data['startday'] = $data['beginRelative']*24*60*60;
                    $data['stopday'] = $data['endRelative']*24*60*60;
                    $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_RELATIVE;

                    break;

                default:
                    //if (!strlen($data['beginDate']))
                    try {
                        $begin = new HM_Date($data['beginDate']);
                    } catch (Zend_Date_Exception $e) {
                        $begin = new HM_Date();
                    }
                    $begin->set('00:00', Zend_Date::TIMES);

                    try {
                        $end = new HM_Date($data['endDate']);
                    } catch (Zend_Date_Exception $e) {
                        $end = new HM_Date();
                    }
                    $end->set('23:59', Zend_Date::TIMES);
                    $data['begin'] = $begin->toString('YYYY-MM-dd HH:mm');
                    $data['end'] = $end->toString('YYYY-MM-dd HH:mm');
                    $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_DATES;

            }
        }

        unset($data['GroupDate']);
        unset($data['beginDate']);
        unset($data['endDate']);
        unset($data['currentDate']);
        unset($data['beginTime']);
        unset($data['endTime']);
        unset($data['beginRelative']);
        unset($data['endRelative']);

        return $data;
    }

    public function deleteFromConditions($lessonId)
    {
        $this->updateWhere(array('cond_sheid' => ''), $this->quoteInto('cond_sheid = ?', $lessonId));

        $collection = $this->fetchAll(
            $this->quoteInto(
                array('cond_sheid LIKE ?', ' OR cond_sheid LIKE ?', ' OR cond_sheid LIKE ?'),
                array("$lessonId#%", "%#$lessonId#%", "%#$lessonId")
            )
        );

        if (count($collection)) {
            foreach($collection as $lesson) {
                $necessary = $lesson->getNecessaryLessonsId();
                if (is_array($necessary) && count($necessary)) {
                    for($i=0; $i < count($necessary); $i++) {
                        if ($necessary[$i] == $lessonId) {
                            unset($necessary[$i]);
                        }
                    }
                    $this->update(array('SHEID' => $lesson->SHEID, 'cond_sheid' => join('#', $necessary)));
                }
            }
        }

    }

    public function delete($lessonId)
    {
        $userIds = $this->getService('LessonAssign')->getRelatedUserList($lessonId);

        $lesson = $this->find($lessonId)->current();
        $params = $lesson->getParams();
        $typeId = $lesson->getType();
        $moduleId = $params['module_id'];

        if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
            $typeId = HM_Event_EventModel::TYPE_COURSE; // открываем весь модуль
            $moduleId = $params['course_id'];
            $siblings = $this->fetchAll($this->quoteInto(
                array('params LIKE ?'),
                array('%course_id='.$moduleId.';%')
            ));
            if (count($siblings) > 1) $moduleId = false;
        }
        $this->setLessonFreeMode($moduleId, $typeId, $lesson->CID, HM_Lesson_LessonModel::MODE_FREE);

        // Удаление назначений
        $this->getService('LessonAssign')->deleteBy($this->quoteInto('SHEID = ?', $lessonId));

        // Удаление тестов
        //$this->getService('Test')->deleteBy($this->quoteInto('lesson_id = ?', $lessonId));

        // Удаление из условий
        $this->deleteFromConditions($lessonId);
        $return = parent::delete($lessonId);
        $this->cleanUpCache('HM_View_Infoblock_ScheduleDailyBlock', $userIds);

        return $return;
    }

    public function assignStudents($lessonId, $userIds, $unassign = true, $taskUserVars = [], $reassignDates = true)
    {
        $lesson = $this->getOne($this->findDependence('Subject', $lessonId));
        $subject = $this->getOne($lesson->subject);

        $students = $studentForUpdates = [];
        if (is_array($userIds) && count($userIds)) {

            $collection = $this->getService('Student')->fetchAll(array(
                'CID = ?' => $subject->subid,
                'MID IN (?)' => $userIds,
            ));
            foreach ($collection as $student) {
                $students[$student->MID] = $student;
            }

            $assigns = $this->getService('LessonAssign')->fetchAll($this->quoteInto('SHEID = ? AND MID > 0', $lessonId));

            if (count($assigns)) {
            foreach($assigns as $assign) {
                    if (in_array($assign->MID, $userIds)) {
                        $key = array_search($assign->MID, $userIds);
                        if (false !== $key && !in_array($userIds[$key], $studentForUpdates)) {
                            $studentForUpdates[] = $userIds[$key];
/*
                            if($lesson->getType() == HM_Event_EventModel::TYPE_TASK) {
                                $this->getService('Question')->updateTask($lessonId, $userIds[$key], $taskUserVars[$userIds[$key]]);
                            }
*/
                            // если НЕ отжали галочку "Заново назначить даты..." - пересчитываем для всех участников занятия
                            // todo: Для Таволги (#39517) делаем якобы галочки нет и всегда такое поведение
                            // todo: Для 5.0 делаем такое поведение пока не сделаем страницу настройки персональных дат
                            if (true || $reassignDates) {
                                $this->assignStudentDates($lesson, $students[$assign->MID], $subject);
                            }

                            unset($userIds[$key]);
                        }
                    } else {
                        $graduatedUsers = $this->getService('Graduated')->fetchAll(array(
                            'MID = ?' => $assign->MID,
                            'CID = ?' => $subject->subid,
                        ));

                        if ($unassign and !count($graduatedUsers)) {
                            $this->unassignStudent($lessonId, $assign->MID);
                        }
                    }
                }
            }

            /** @var HM_Messenger $messenger */
            // Автоматическая отправка сообщений о назначении на занятие отключена #41543
            //$messenger = $this->getService('Messenger');
            //$messenger->setOptions(HM_Messenger::TEMPLATE_ASSIGN_LESSON, ['lesson_id' => $lesson->SHEID, 'subject_id' => $subject->subid]);
            foreach($userIds as $userId) {
                $taskUserVar = (isset($taskUserVars[$userId]))? $taskUserVars[$userId] : null;

                $this->assignStudent($lessonId, $userId, $taskUserVar);
                //$messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
            }

//            $triggerService = $this->getService('LessonAssignESTrigger');
            if ((int) $lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
//[ES!!!] //array('lesson' => $lesson,'students' => array_keys($students))
            }
/*
            if($lesson->getType() == HM_Event_EventModel::TYPE_TASK) {
                $this->getService('Question')->updateTasks($lesson, $studentForUpdates, $taskUserVars);
            }
*/
        }

        if($lesson->has_proctoring) {

            if($this->isProctoringTransaction) {
                if(!isset($this->proctoringAssigns[$lessonId])) {
                    $this->proctoringAssigns[$lessonId] = array();
                }
                $this->proctoringAssigns[$lessonId] = array_unique(array_merge($students, $this->proctoringAssigns[$lessonId]));
            } else {
                $this->getService('Proctoring')->pushEvents($lessonId);
            }
        }
    }

    // пишем в scheduleID фактические даты для каждого студента
    //
    public function updateDates($studentIds, $lessonId)
    {
        $lesson = $this->getOne($this->findDependence('Subject', $lessonId));
        $subject = $lesson->subject->current();

        $lessonData = [];

        if (!count($studentIds)) return;

        $students = $this->getService('Student')->fetchAll(
            $this->quoteInto(array('CID = ?', ' AND MID IN (?)'), array($lesson->CID, $studentIds))
        );


        foreach ($students as $student) {

            switch ($lesson->timetype) {

                case HM_Lesson_LessonModel::TIMETYPE_FREE:
                    $lessonData['begin_personal'] = ($subject->period == HM_Subject_SubjectModel::PERIOD_FREE) ? null : $student->time_registered;
                    $lessonData['end_personal'] = ($subject->period == HM_Subject_SubjectModel::PERIOD_FREE) ? null : $student->end_personal;
                    break;

                case HM_Lesson_LessonModel::TIMETYPE_DATES:
                case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                    $lessonData['begin_personal'] = $lesson->begin;
                    $lessonData['end_personal'] = $lesson->end;
                    break;

                case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:

                    if (in_array($lesson->getType(), array(
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER
                    ))) {
                        $assign = $this->getOne(
                            $this->getService('LessonAssign')->fetchAll(
                                $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson->SHEID, $student->MID))
                            )
                        );
                        if ($assign) {
                            $base = $assign->created;
                        }
                    } else {
                        $base = (max($lesson->startday, $lesson->stopday) > 0) ? $student->time_registered : $student->end_personal; // если кол-во дней отрицательное, то отсчитывать от конца курса
                    }

                    if ($lesson->startday) {
                        $begin = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->startday/86400);
                        $lessonData['begin_personal'] = $begin->get('Y-M-d');
                    } else {
                        $lessonData['begin_personal'] = null;
                    }
                    if ($lesson->stopday) {
                        $end = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->stopday/86400);
                        $lessonData['end_personal'] = $end->get('Y-M-d') . ' 23:59:59';
                    } else {
                        $lessonData['end_personal'] = null;
                    }
                    break;
            }

            $this->getService('LessonAssign')->updateWhere(array(
                'begin_personal' => $lessonData['begin_personal'],
                'end_personal' => $lessonData['end_personal']
            ), array(
                'SHEID = ?' => $lessonId,
                'MID = ?' => $student->MID
            ));
        }
    }


    /**
     * @param $lessonId
     * @param HM_Form_Element_Html5File|HM_Form_Element_ServerFile $photo Элемент формы
     * @param $destination Путь к папке с иконками
     * @return bool
     */
    public static function updateIcon($lessonId, $photo, $destination = null)
    {
        $destination = Zend_Registry::get('config')->path->upload->lesson;
        return HM_Subject_SubjectService::updateIcon($lessonId, $photo, $destination);
    }

    public function assignStudent($lessonId, $studentId, $taskVariant = null, $fromAssigns = false)
    {
        $lesson = $this->getOne($this->findDependence('Subject', $lessonId));
        $params       = $lesson->getParams();
        $subject = $lesson->subject->current();
        $student = $this->getService('Student')->fetchOne(array(
            'MID = ?' => $studentId,
            'CID = ?' => $lesson->CID,
        ));

        if (!$lesson || !$student || !$subject) return false;

        //if ( $lesson->teacher != $studentId ) {
        if ($lesson->getType() == HM_Event_EventModel::TYPE_TASK) {
            $this->getService('TaskConversation')->assignUser($lessonId, $params['module_id'], $studentId, $taskVariant);
        }
        //}

        // если занятие с типом форум, то пользователя еще и подписываем на уведомления
        if ($lesson->getType() == HM_Activity_ActivityModel::ACTIVITY_FORUM) {
            $this->getService('Subscription')->subscribeUserToChannelByLessonId($studentId, $lessonId);
        }

        // всегда, когда назначаем нового студента на занятие - рассчитываем для него даты
        $this->assignStudentDates($lesson, $student, $subject);

            if($lesson->has_proctoring) {

                if($this->isProctoringTransaction) {
                    if(!isset($this->proctoringAssigns[$lessonId])) {
                        $this->proctoringAssigns[$lessonId] = array();
                    }
                    $this->proctoringAssigns[$lessonId] = array_unique(array_merge(array($studentId), $this->proctoringAssigns[$lessonId]));
                } else {
                    $this->getService('Proctoring')->pushEvents($lessonId);
                }
            }
    }

    public function assignStudentDates ($lesson, $student, $subject)
    {
        $collection = $this->getService('LessonAssign')->fetchAll(array(
            'SHEID = ?'      => (int) $lesson->SHEID,
            'MID = ?'        => (int) $student->MID,
            'isgroup = ?'    => 0,
        ));

        if (count($collection)) {
            $lessonData = $collection->current()->getData();
        } else {
            $lessonData = array(
                'SHEID'      => (int) $lesson->SHEID,
                'MID'        => (int) $student->MID,
                'isgroup'    => 0,
            );

        }

        switch ($lesson->timetype) {

            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                // 0, а не null точно срабатывает при update в mysql
                // В mssql ещё стоит посмотреть
                $lessonData['begin_personal'] = 0;
                $lessonData['end_personal'] = 0;

                break;
            case HM_Lesson_LessonModel::TIMETYPE_DATES:
            case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                $lessonData['begin_personal'] = $lesson->begin;
                $lessonData['end_personal'] = $lesson->end;
                break;

            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:

                if (in_array($lesson->getType(), array(
                    HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER,
                    HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT,
                    HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER
                ))) {
                    $assign = $this->getOne(
                        $this->getService('LessonAssign')->fetchAll(
                            $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson->SHEID, $student->MID))
                        )
                    );
                    if ($assign) {
                        $base = $assign->created;
                    }
                } else {
                    // если кол-во дней отрицательное, то отсчитывать от конца курса
                    $base = (max($lesson->startday, $lesson->stopday) > 0) ? $student->time_registered : $student->end_personal;
                }

                if ($lesson->startday) {
                    $begin = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->startday/86400);
                    $lessonData['begin_personal'] = $begin->get('Y-M-d');
                } else {
                    $lessonData['begin_personal'] = null;
                }
                if ($lesson->stopday) {
                    $end = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->stopday/86400);
                    $lessonData['end_personal'] = $end->get('Y-M-d') . ' 23:59:59';
                } else {
                    $lessonData['end_personal'] = null;
                }
                break;
        }

        if (isset($lessonData['SSID']) && $lessonData['SSID']) {
            $lessonAssign = $this->getService('LessonAssign')->update($lessonData);
        } else {
            $lessonAssign = $this->getService('LessonAssign')->insert($lessonData);
        }

        $this->cleanUpCache('HM_View_Infoblock_ScheduleDailyBlock', $student->MID);

        return $lessonAssign;
    }

    public function unassignStudent($lessonId, $studentId)
    {
        $this->getService('Proctoring')->deleteEvents($lessonId, $studentId);

//        $this->getService('Interview')->deleteBy(array($this->getService('Interview')->quoteInto(array('to_whom = ? ',' AND lesson_id = ? '),array($studentId,$lessonId))));
        if (is_array($studentId)) {
            $return = $this->getService('LessonAssign')->deleteBy($this->quoteInto(array("SHEID = ?", " AND MID IN (?)"), array($lessonId, $studentId)));
        } else {
            $return = $this->getService('LessonAssign')->deleteBy(sprintf("SHEID = '%d' AND MID = '%d'", $lessonId, $studentId));
        }

        $config = Zend_Registry::get('config');
        if ($config->cache->enabled) {

            if(!$lesson = $this->getCachedValue('lessonId2Cid', $lessonId)) {
                $lesson = $this->getLesson($lessonId);
                $this->add2Cache('lessonId2Cid', $lesson, $lessonId);
            }

            // Инфоблоки
            $this->cleanUpCache('HM_View_Infoblock_ScheduleDailyBlock', $studentId);
        }

        return $return;
    }

    public function unassignAllStudents($lessonId)
    {
        $this->getService('Proctoring')->deleteEvents($lessonId);
//        $this->getService('Interview')->deleteBy($this->getService('Interview')->quoteInto('lesson_id = ? ',$lessonId));

        $return = $this->getService('LessonAssign')->deleteBy($this->getService('LessonAssign')->quoteInto('SHEID = ? AND MID > 0', $lessonId));

        $config = Zend_Registry::get('config');
        if ($config->cache->enabled) {

            if (!$lesson = $this->getCachedValue('lessonId2Cid', $lessonId)) {
                $lesson = $this->getLesson($lessonId);
                $this->add2Cache('lessonId2Cid', $lesson, $lessonId);
            }

            $this->cleanUpCache('HM_View_Infoblock_ScheduleDailyBlock', Zend_Cache::CLEANING_MODE_ALL);
        }
        return $return;
    }

    public function isUserAssigned($lessonId, $userId)
    {
        $collection = $this->getService('LessonAssign')->fetchAll($this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lessonId, $userId)));
        return count($collection);
    }

    public function isLaunchConditionSatisfied($lessonId, $lesson = null, $checkRole = true)
    {
        if ($checkRole
            && !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //&& !in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_STUDENT))
        ) return true;

        if (null == $lesson) {
            $lesson = $this->getOne($this->find($lessonId));
        }

        $conditionLesson = $conditionProgress = $conditionAvg = $conditionSum = null;

        if ($lesson) {
            if ($lesson->cond_sheid && $lesson->cond_mark) {
                $sheids = explode('#', $lesson->cond_sheid);
                $marks  = explode('#', $lesson->cond_mark);
                if (is_array($sheids) && count($sheids) && is_array($marks) && count($marks) && (count($sheids) == count($marks))) {
                    $conditions = [];
                    foreach($sheids as $index => $sheid) {
                        $conditions[] = sprintf('(%s)', $this->quoteInto(array('SHEID = ?', ' AND V_STATUS >= ?'), array($sheid, (float) $marks[$index])));
                    }
                    if (count($conditions)) {
                        $collection = $this->getService('LessonAssign')->fetchAll(
                            $this->quoteInto('MID = ?', $this->getService('User')->getCurrentUserId())
                            .' AND ('.join(' OR ', $conditions).')'
                        );

                        $conditionLesson = (count($collection) == count($sheids));
                    }
                }
            }

            if ($lesson->cond_progress || $lesson->cond_avgbal || $lesson->cond_sumbal) {
                $collection = $this->getService('LessonAssign')->fetchAllDependenceJoinInner(
                    'Lesson',
                    $this->quoteInto(
                        array(
                            'self.MID = ?',
                            ' AND Lesson.CID = ? AND Lesson.vedomost = 1',
                            ' AND typeID NOT IN (?)',
                            ' AND isfree = ?'
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $lesson->CID,
                            array_keys(HM_Event_EventModel::getExcludedTypes()),
                            HM_Lesson_LessonModel::MODE_PLAN
                        )
                    )
                );
                if (count($collection)) {
                    $lessons = [];
                    $lessonsCompleted = $lessonsTotal = $lessonsSumBal = $lessonsProgress = $lessonsAvgBal = 0;
                    foreach($collection as $item) {
                        if ($item->V_STATUS > 0) {
                            $lessonsCompleted++;
                            $lessonsSumBal += $item->V_STATUS;
                        }
                        $lessons[$item->SHEID] = 1;
                        //$lessonsTotal++;
                    }

                    $lessonsTotal = count($lessons);

                    if ($lessonsTotal)
                        $lessonsProgress = floor(doubleval(($lessonsCompleted/$lessonsTotal)*100));
                    if ($lessonsCompleted)
                        $lessonsAvgBal = $lessonsSumBal/$lessonsCompleted;
                }

                if ($lesson->cond_progress) {
                    $conditionProgress = $lesson->checkInterval($lessonsProgress, $lesson->cond_progress);
                }

                if ($lesson->cond_avgbal) {
                    $conditionAvg = $lesson->checkInterval($lessonsAvgBal, $lesson->cond_avgbal);
                }

                if ($lesson->cond_sumbal) {
                    $conditionSum = $lesson->checkInterval($lessonsSumBal, $lesson->cond_sumbal);
                }
            }
        }

        $return = !(integer)$lesson->cond_operation;
        foreach (array($conditionLesson, $conditionProgress, $conditionAvg, $conditionSum) as $argument) {
            if (null !== $argument) {
                $return = $lesson->cond_operation ? $return || $argument : $return && $argument;
            }
        }

        return $return;

    }

    protected function _isExecutableForDean($lesson)
    {
        return true;
    }

    protected function _isExecutableForTeacher($lesson)
    {
        if (!$this->getService('Subject')->isTeacher($lesson->CID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не являетесь инструктором на курсе'));
        }

        return true;
    }

    protected function _isExecutableForStudent($lesson)
    {
        $registered = null;
        $isGraduated = $this->getService('Subject')->isGraduated($lesson->CID, $this->getService('User')->getCurrentUserId());
        $isStudent = $this->getService('Subject')->isStudent($lesson->CID, $this->getService('User')->getCurrentUserId());
        $isSessionUser = $lesson->session_id ? $this->getService('AtSessionUser')->isActiveSessionUser($lesson->session_id, $this->getService('User')->getCurrentUserId()) : false;

        $msg = _('У Вас нет прав на запуск данного занятия');
        if (in_array($lesson->typeID, array_keys(HM_Event_EventModel::getDeanPollTypes()))) {
            if (!$isGraduated) {
                throw new HM_Exception(_('Вы не являетесь прошедшим обучения на курсе'));
            }
        } else {
            if (!$isStudent && !$isGraduated) {
                throw new HM_Exception(_('Вы не являетесь слушателем на курсе'));
            } elseif (!$isStudent && $lesson->vedomost && ($lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN)) {
                throw new HM_Exception(_('Вы переведены в прошедшие обучение на этом курсе; запуск занятий на оценку не разрешен.'));
            }
        }

        if (!$this->isUserAssigned($lesson->SHEID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не назначены на занятие'));
        }

        // Проверка дат (только для студентов)
        if (!$lesson->isExecutable()) {

            throw new HM_Exception(_('Занятие назначено на другое время'));
        }

        if ($lesson->typeID == HM_Event_EventModel::TYPE_WEBINAR && Zend_Registry::get('config')->offline) {
            throw new HM_Exception(_('Вебинар невозможно запустить в режиме offline'));
        }

        // Проверка условий запуска
        if (!$this->isLaunchConditionSatisfied($lesson->SHEID, $lesson)) {
            throw new HM_Exception(_('Условия запуска занятия не выполнены'));
        }

        return true;

    }

    protected function _isExecutableForDefault($lesson)
    {
        throw new HM_Exception(_('Нет прав для запуска данного занятия'));
    }

    protected function _isExecutableForRole($lesson)
    {
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        if ($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $this->_isExecutableForStudent($lesson);
        } elseif ($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
            $this->_isExecutableForTeacher($lesson);
        } elseif ($acl->checkRoles(array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $this->_isExecutableForDean($lesson);
        } else {
            $this->_isExecutableForDefault($lesson);
        }

/*        switch($this->getService('User')->getCurrentUserRole()) {
            case HM_Role_Abstract_RoleModel::ROLE_TEACHER:
                $this->_isExecutableForTeacher($lesson);
                break;
            case HM_Role_Abstract_RoleModel::ROLE_STUDENT:
                $this->_isExecutableForStudent($lesson);
                break;
            case HM_Role_Abstract_RoleModel::ROLE_DEAN:
                $this->_isExecutableForDean($lesson);
                break;
            default:
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_STUDENT)) {
                    $this->_isExecutableForStudent($lesson);
                } else {
                    $this->_isExecutableForDefault($lesson);
                }
                break;
}*/
    }

    public function isExecutable($lesson)
    {
        if ($lesson) {
            $this->_isExecutableForRole($lesson);
            return true;
        } else {
            throw new HM_Exception(_('Занятие не найдено'));
        }
    }

    /**
     * Возвращает subject_id
     *
     * @author Artem Smirnov
     * @date 19.02.2013
     * @param $lessonID
     * @return string
     */
    public function getSubjectByLesson($lessonID)
    {
        /** @var $lessonService HM_Lesson_LessonService */
        $subjectRequest = $this->getSelect();
        $subjectRequest->from(
            array('l' => 'lessons'),
            array(
                'l.CID'
            )
        );
        $subjectRequest->where('l.SHEID = ?', $lessonID);
        return $subjectRequest->getAdapter()->fetchOne($subjectRequest);
    }

    private function getMarkSheetLessons($subjectId)
    {
        return $this->getService('Lesson')->fetchAllDependenceJoinInner(
            'Assign',
            [
                'self.CID  = ?' => $subjectId,
                'self.vedomost = ?' => 1,
                'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
            ],
            'self.order'
        );
    }

    private function getMarkSheetEvents($eventIds)
    {
        $result = [];
        $eventIds = array_map('abs', array_filter($eventIds, function ($typeId) {
            return $typeId < 0;
        }));

        if (count($eventIds)) {
            $eventsCollection = $this->getService('Event')->fetchAll(array('event_id IN (?)' => $eventIds));
            foreach ($eventsCollection as $event) {
                $result[$event->event_id] = $event;
            }
        }

        return $result;
    }

    private function getMarkSheetSchedules($subjectId, $fromDate, $toDate)
    {
        $result = [];
        $lessonsCollection = $this->getMarkSheetLessons($subjectId);
        $events = $this->getMarkSheetEvents($lessonsCollection->getList('typeID'));

        /** @var HM_Lesson_LessonModel $lesson custom? */
       foreach($lessonsCollection as $lesson) {
           $assigns = $lesson->getAssigns();
           if ($assigns) {
               $inPeriod = false;

               foreach($assigns as $assign) {
                   if ($assign->MID > 0) {
                       $hasStudent = !empty($students[$assign->MID]);

                       if ($fromDate && $toDate && !$inPeriod) {

                           switch($lesson->timetype){
                               case HM_Lesson_LessonModel::TIMETYPE_FREE:
                                   $inPeriod = true;
                                   break;
                               case HM_Lesson_LessonModel::TIMETYPE_DATES:
                               case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                                   $inPeriod = $this->isDatesInPeriod($fromDate, $toDate, $lesson->begin, $lesson->end);
                                   break;
                               case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                                   if($hasStudent){
                                       $inPeriod = $this->isDatesInPeriod($fromDate, $toDate, $assign->begin_personal, $assign->end_personal);
                                   }
                                   break;
                               default:
                                   break;
                           }
                       }
                   }
               }
               if($inPeriod || !$fromDate || !$toDate){
                  if (strlen($lesson->typeID) && isset($events[-$lesson->typeID])) {
                      $lesson->setEvent($events[-$lesson->typeID]);
                  }
                   $result[$lesson->SHEID] = [
                       'id' => $lesson->SHEID,
                       'title' => $lesson->title,
                       'subjectId' => $lesson->CID,
                       'resultUrl' => $lesson->getResultsUrl(),
                       'icon' => $lesson->getUserIcon() ?: $lesson->getIcon(),
                       'scale' => $lesson->getScale(),
                       'order' => $lesson->getOrder(),
                   ];
               }
           }
       }

       return $result;
    }

    private function isDatesInPeriod($fromDate, $toDate, $periodFrom, $lessonTo)
    {
        $result = false;

        $fromDate = (new Zend_Date($fromDate))->getTimestamp();
        $toDate = (new Zend_Date($toDate))->getTimestamp();

        $begin = (new Zend_Date($periodFrom))->getTimestamp();
        $end = (new Zend_Date($lessonTo))->getTimestamp();

       if (($begin >= $fromDate && $begin <= $toDate) ||
           ($end >= $fromDate && $end <= $toDate) ||
           ($end >= $toDate && $begin <= $fromDate) ||
           ($end <= $toDate && $begin >= $fromDate)
       ) {
           $result = true;
       }

       return $result;
    }

    private function getMarkSheetLessonsTotal($subjectId)
    {
        $result = [];
        $lessonsCollection = $this->getMarkSheetLessons($subjectId);
        /** @var HM_Lesson_LessonModel $lesson custom? */
       foreach($lessonsCollection as $lesson) {
           $assigns = $lesson->getAssigns();
           if ($assigns) {
               foreach($assigns as $assign) {
                   if ($assign->MID > 0) {
                       if (!isset($result[$assign->MID][$assign->SHEID])) {
                           $result[$assign->MID][$assign->SHEID] = [
                               'mark' => $assign->V_STATUS,
                               'comments' => $assign->comments,
                           ];
                       }
                   }
               }
           }
       }

       return $result;
    }

    /**
     * Для сортировки пользователей по ФИО
     */
    public function userCompare ($a,$b)
    {
        return strcmp($a['name'], $b['name']);
    }

    public function isTeacher($lessonId, $userId)
    {
        $lesson = $this->getOne($this->find($lessonId));
        if ($lesson) {
            return ($lesson->teacher == $userId);
        }
        return false;
    }

    public function getUsersStats($from, $to, $subjectId)
    {

        $from = date('Y-m-d', strtotime($from));
        $to = date('Y-m-d', strtotime($to));

        $select = $this->getSelect();

        $select->from(
            array('sc' => 'scorm_tracklog'),
            array('mid', 'start', 'stop')
        )
        ->joinInner(array('subjects_courses'), 'subjects_courses.course_id = sc.CID', [])
        ->where('subjects_courses.subject_id  = ?', $subjectId)
        ->where('sc.start >= ?',  $from . ' 00:00')
        ->where('sc.stop <= ?',  $to . ' 23:59:59');

        $query = $select->query();

        $fetch = $query->fetchAll();

        $users = [];
        $time = 0;
        $count = 0;
        foreach($fetch as $val){
            if(!isset($users[$val['mid']]))
            {
                $count++;
                $users[$val['mid']] = true;
            }

            $time = $time + (strtotime($val['stop']) - strtotime($val['start']));
        }
        return array('time' => $time, 'count' => $count);

    }

    public function getAssignedForLeader($lessonId)
    {
        $leaders = $this->getService('LessonAssign')->fetchAll(array('SHEID = ?' => $lessonId));
        $leaders = $leaders->getList('MID', 'SSID');
        $student = $this->getService('LessonDeanPollAssign')->fetchAll(array('lesson_id = ?' => $lessonId, 'head_mid IN (?)' => array_keys($leaders)));

        $studentList = $student->getList('student_mid', 'lesson_id');

        $students = $this->getService('User')->fetchAll(array('MID IN (?)' => array_keys($studentList)));

        return $students;
    }

    public function getAvailableStudents($subjectId)
    {
        return $this->getService('Subject')->getAssignedUsers($subjectId)->getList('MID', 'MID');
    }

    public function getStudents($lessonId) {
        $result = [];
        $lesson = $this->findOne($lessonId);
        $usersIds = $this->getService('LessonAssign')->fetchAll(['SHEID = ?' => $lessonId])->getList('MID');

        if(!empty($usersIds)) {
            $result = $this->getService('Student')->fetchAll([
                'CID = ?' => $lesson->CID,
                'MID in (?)' => $usersIds
            ]);
        }

        return $result;
    }


    public function getTotalCoursePercent($lesson, $userId, $courseId)
    {
        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = $this->getService('CourseItem');
        $items = $courseItemService->getTreeItems($courseId, $lesson->getModuleId());

        $count = 0;
        $total = 0;

        /** @var HM_Scorm_Track_TrackService $scormService */
        $scormService = $this->getService('ScormTrack');

        $tracks = $scormService->getLastUserTracks(
            $userId,
            $courseId,
            $lesson->SHEID
        );

        $modules = array_column($tracks, 'module');

        foreach($items as $item){
            if ((int)$item->module) {
                $count++;
            }

             // todo: Вот тут очень много циклов неадекватных, надо разобраться
             // Хотя бы исключим запросы с пустым ответом, иногда их бывает под 90% от общего количества
             if(in_array($item->module, $modules)) {
                 $track = $scormService->getLastUserTrack(
                $userId,
                $courseId,
                $item->oid,
                $item->module,
                $lesson->SHEID
            );

            if($track){
                if (empty($track->scoremax)) {
                    $track->scoremax = 100;
                }
                if(
                    $track->status == HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED
                    || $track->status == HM_Scorm_Track_Data_DataModel::STATUS_PASSED
                ) {
                    $total += 100;
                }elseif($track->score != 0 && $track->scoremax != 0){
                    $total += ($track->score / $track->scoremax) * 100;
                }
            }
        }
        }
        if ($count) {
            if (100 >= $percent = ceil($total/$count)) {
                return $percent;
            }
        }
        return 0;
    }

    /**
     *  Получаем массив результатов занятия пользователей типа 'userId' => V_STATUS для занятия по его ID
     *  выбираются данные с V_STATUS > 0
     *  @param int $lessonId ID занятия
     *  @return array
     */
    public function getMarkedUsersId($lessonId)
    {
        $results             = [];
        $lessonAssignService = $this->getService('LessonAssign');
        $collection          = $lessonAssignService->fetchAll($lessonAssignService->quoteInto(array('V_STATUS > ?', ' AND SHEID = ?'),array(0,intval($lessonId))));

        if ( count($collection) ) {
            $results = $collection->getList('MID','V_STATUS');
        }

        return $results;
    }

    public function getLesson($lessonId)
    {
        $cacheName = 'getLesson';

        if ($this->_cache[$cacheName][$lessonId]) {
            $output = $this->_cache[$cacheName][$lessonId];
        } else {

            $output = $this->fetchRow(array('SHEID = ?' => (int)$lessonId));
            $this->_cache[$cacheName][$lessonId] = $output;
        }

        return $output;
    }

    public function setLessonFreeMode($moduleId, $typeId, $subjectId, $newMode = HM_Lesson_LessonModel::MODE_FREE)
    {
        if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
            $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
        }

        // для кастомных типов занятий надо вычислить реальный тип
        $typeId = $this->getLessonTool($typeId);

        if ($freeLesson = $this->getOne(
            $this->fetchAll(array(
                "params LIKE '%module_id=" . $moduleId . ";'",
                'CID = ?' => $subjectId,
                'typeID = ?' => $typeId ? : 0,
                'isfree = ?' => $newMode == HM_Lesson_LessonModel::MODE_FREE ?
                    HM_Lesson_LessonModel::MODE_FREE_BLOCKED :
                    HM_Lesson_LessonModel::MODE_FREE,
            )))) {

                $data = $freeLesson->getValues();
                $data['isfree'] = $newMode;
                $this->update($data);
            }
    }

    public function getLessonTool($typeOrEventId)
    {
        if ($typeOrEventId < 0) {
            $event = $this->getOne(
                $this->getService('Event')->find(-$typeOrEventId)
            );
            if ($event) {
                return $event->tool;
            }
        }
        return $typeOrEventId;
    }

    /*
     *  Пока считает более грубо, нежели countPercents,
     *  т.к. не учитывает внутренний прогресс занятий с типом УМ
     */
    public function countPercentsAllSubjects($userId, $subjectIds)
    {
        $select = $this->getSelect();
        $select
            ->from(array('s' => 'schedule'), array(
                'subject_id' => 's.CID',
                'percent' => new Zend_Db_Expr('ROUND(CASE WHEN COUNT(s.SHEID) != 0 THEN (100 * SUM(CASE WHEN sID.V_STATUS !=-1 THEN 1 ELSE 0 END))/COUNT(s.SHEID) ELSE 0 END, 0)'),
            ))
            ->joinLeft(array('sID' => 'scheduleID'), 's.SHEID = sID.SHEID', [])
            ->where('s.CID IN (?)', $subjectIds)
            ->where('s.vedomost = 1')
            ->where('s.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN)
            ->where('sID.MID = ?', $userId)
            ->group('s.CID')
        ;

        $return = [];
        $rows = $select->query()->fetchAll();
        foreach ($rows as $row) {
            $return[$row['subject_id']] = $row['percent'];
        }

        return $return;
    }

    /**
     * Function returns count of finished lessons which in statment(vedomost)
     * in percents
     * @param HM_Collection $lessonAssigns
     * @return int
     */

    public function countPercents(HM_Collection $lessons, $userId = 0)
    {
        list($totalCount, $lessonsPercent, $lessonsWithMarks) = $this->_getCounts($lessons, $userId);

        return $totalCount ? intval($lessonsPercent/$totalCount) : 0;
    }

    public function countPercentsWithLessons(HM_Collection $lessons, $userId = 0)
    {
        list($totalCount, $lessonsPercent, $lessonsWithMarks) = $this->_getCounts($lessons, $userId);

        return $totalCount
            ? array('percents' => intval($lessonsPercent/$totalCount), 'count' => array('ready' => count($lessonsWithMarks), 'total' => $totalCount))
            : array(0, array(0, 0));
    }

    protected function _getCounts(HM_Collection $lessons, $userId = 0)
    {
        if (!count($lessons)) {
            return 0;
        }
        $totalCount = 0;
        $lessonsPercent = 0;

        $typesToCountProgress = array(
            HM_Event_EventModel::TYPE_COURSE,
            HM_Event_EventModel::TYPE_LECTURE
        );

        if (!$userId) {
            /** @var HM_User_UserService $userService */
            $userService = $this->getService('User');
            $userId = $userService->getCurrentUserId();
        }

        $lessonsWithMarks = $this->getService('LessonAssign')->fetchAll(array(
            'MID = ?' => $userId,
            'SHEID IN (?)' => $lessons->getList('SHEID'),
            'V_STATUS != ?' => HM_Scale_Value_ValueModel::VALUE_NA,
        ))->getList('SHEID');

        /** @var HM_Course_CourseService $courseService */
        $courseService = $this->getService('Course');

        /** @var HM_Lesson_LessonModel $lesson */
        foreach ($lessons as $lesson) {
            if ($lesson->vedomost) {
                $totalCount++;
            }

            $lessonType = $lesson->getType();

            //если занятие по модулю или по разделу модуля
            if (!in_array($lesson->SHEID, $lessonsWithMarks) && in_array($lessonType, $typesToCountProgress)) {
                $params = $lesson->getParams();

                $courseId = $params['module_id'];
                if ($lessonType == HM_Event_EventModel::TYPE_LECTURE) {
                    $courseId = $params['course_id'];
                }

                /** @var HM_Course_CourseModel $courseModel */
                $courseModel = $courseService->getCourse($courseId);

                if(!$courseId)
                    $courseId = $lesson->CID;

                //есть скорм
                if ($courseModel) {
                    if ($courseModel->isImportFormat() || $courseModel->isScormEmulationAllowed()) {
                        $lessonsPercent += (int) $this->getTotalCoursePercent(              // здесь считаются по скорму
                            $lesson,
                            $userId,
                            $courseId
                        );
                        continue;
                    }
                }
            }

            if (in_array($lesson->SHEID, $lessonsWithMarks)) {
                $lessonsPercent += 100;
            }
        }

        return [$totalCount, $lessonsPercent, $lessonsWithMarks];
    }


    // @todo: проверить
    public function fitLessonDates(HM_Subject_SubjectModel $subject)
    {
        $this->updateWhere(array('end' => $subject->end),
            $this->quoteInto(array('CID = ?',' AND (end > ?',' OR end < ?)'),
                array($subject->subid,
                    $this->getService('Lesson')
                        ->getDateTime(strtotime($subject->end)),
                    $this->getService('Lesson')
                        ->getDateTime(strtotime($subject->begin)))));

        $this->updateWhere(array('begin' => $subject->begin),
            $this->quoteInto(array('CID = ?',' AND (begin > ?',' OR begin < ?)'),
                array($subject->subid,
                    $this->getService('Lesson')
                        ->getDateTime(strtotime($subject->end)),
                    $this->getService('Lesson')
                        ->getDateTime(strtotime($subject->begin)))));
    }

    static public function autodetectType($material)
    {
        // @todo: продолжить
        switch (get_class($material)) {
            case 'HM_Resource_ResourceModel':
                return HM_Event_EventModel::TYPE_RESOURCE;
        }

        return false;
    }
    public function getLessonFormValuesToSave(HM_Form_Lesson $form)
    {
        $typeId = $form->getValue('typeID');
        if (is_numeric($typeId) and (int) $typeId < 0) {
            $event = $this->getService('Event')->findOne(- (int) $typeId);
        }

        $sectionId = $form->getValue('section_id') ?: 0;

        return [
            'SHEID' => $form->getValue('lesson_id'),
            'title' => $form->getValue('title'),
            'CID' => $form->getValue('subject_id'),
            'typeID' => $form->getValue('typeID'),
            'has_proctoring' => $form->getValue('has_proctoring'),
//            'material_id' => $form->getValue('material_id'),
            'vedomost' => $form->getValue('vedomost'),
            'recommend' => $form->getValue('recommend'),
            'GroupDate' => $form->getValue('GroupDate'),
            'beginDate' => $form->getValue('beginDate'),
            'endDate' => $form->getValue('endDate'),
            'currentDate' => $form->getValue('currentDate'),
            'beginTime' => $form->getValue('beginTime'),
            'endTime' => $form->getValue('endTime'),
            'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
            'endRelative' => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
            'Condition' => $form->getValue('Condition'),
            'cond_sheid' => $form->getValue('cond_sheid'),
            'cond_mark' => ((null !== $form->getValue('cond_mark')) ? $form->getValue('cond_mark') : ''),
            'cond_progress' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_progress') : 0),
            'cond_avgbal' => ((null !== $form->getValue('cond_avgbal')) ? $form->getValue('cond_avgbal') : 0),
            'cond_sumbal' => ((null !== $form->getValue('cond_sumbal')) ? $form->getValue('cond_sumbal') : 0),
            'notice_days' => (int) $form->getValue('notice_days'),
            'descript' => $form->getValue('descript'),
            'threshold' => (string) ((null !== $form->getValue('threshold')) ? $form->getValue('threshold') : 0),
            'createID' => $this->getService('User')->getCurrentUserId(),
            'tool' => $event ? $event->tool : '',
            'section_id' => $sectionId,
            'chat_enabled' => (int) $form->getValue('chat_enabled'),
        ];
    }

    public function getLessonParamsToSave(HM_Model_Abstract $lesson, HM_Form_Lesson $form): array
    {
        $result = $lesson->getParams();

//        if ($form->getValue('material_id')) {
//            $result['module_id'] = $form->getValue('material_id');
//        }

        if ($form->getValue('assign_type')) {
            $result['assign_type'] = $form->getValue('assign_type');
        } elseif (!empty($result['assign_type'])) {
            unset($result['assign_type']);
        }

        if ($form->getValue('formula')) {
            $result['formula_id'] = $form->getValue('formula');
        } elseif (isset($result['formula_id'])) {
            unset($result['formula_id']);
        }

        if ($form->getValue('formula_group')) {
            $result['formula_group_id'] = $form->getValue('formula_group');
        }

        if ($form->getValue('formula_penalty')) {
            $result['formula_penalty_id'] = $form->getValue('formula_penalty');
        }

        // кэшируем id уч.модуля, чтоб потом легко найти и удалить
//        if ($lesson->getType() == HM_Event_EventModel::TYPE_LECTURE) {
//            $result['course_id'] = $form->getValue('material_id');
//        }

        if ($form->getValue('is_hidden', 0)) {
            $result['is_hidden'] = $form->getValue('is_hidden');
        } elseif (!empty($result['is_hidden'])) {
            unset($result['is_hidden']);
        }

        return $result;
    }

    /**
     * @param HM_Lesson_LessonModel $lesson
     * @param $questId
     * @return array
     */
    public function getLessonFormDefaults($lesson, $questId)
    {
        $params = $lesson->getParams();
        $result = array(
            'lesson_id' => $lesson->SHEID,
            'title' => $lesson->title,
            'subject_id' => $lesson->CID,
            'typeID' => $lesson->typeID,
            'vedomost' => $lesson->vedomost,
            'recommend' => $lesson->recommend,
            'material_id' => $lesson->material_id,
            'formula' => $lesson->getFormulaId(),
            'formula_group' => $lesson->getFormulaGroupId(),
            'formula_penalty' => $lesson->getFormulaPenaltyId(),
            'cond_sheid' => $lesson->cond_sheid,
            'cond_mark' => $lesson->cond_mark,
            'cond_progress' => $lesson->cond_progress,
            'cond_avgbal' => $lesson->cond_avgbal,
            'cond_sumbal' => $lesson->cond_sumbal,
            'gid' => $lesson->gid,
            'notice' => $lesson->notice,
            'notice_days' => $lesson->notice_days,
            'descript' => $lesson->descript,
            'assign_type' => (isset($params['assign_type'])) ? (int) $params['assign_type'] : HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM,
            'section_id' => $lesson->section_id,
            'threshold' => $lesson->threshold,
            'has_proctoring' => $lesson->has_proctoring,
            'chat_enabled' => $lesson->chat_enabled
        );

        if ($lesson->cond_sheid) {
            $result['Condition'] = HM_Lesson_LessonModel::CONDITION_LESSON;
        }

        if ($lesson->cond_progress) {
            $result['Condition'] = HM_Lesson_LessonModel::CONDITION_PROGRESS;
        }

        if ($lesson->cond_avgbal) {
            $result['Condition'] = HM_Lesson_LessonModel::CONDITION_AVGBAL;
        }

        if ($lesson->cond_sumbal) {
            $result['Condition'] = HM_Lesson_LessonModel::CONDITION_SUMBAL;
        }

        switch ($lesson->timetype) {
            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                $result['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_RELATIVE;
                $result['beginRelative'] = floor($lesson->startday / 24 / 60 / 60);
                $result['endRelative'] = floor($lesson->stopday / 24 / 60 / 60);
                break;
            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                $result['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_FREE;
                break;
            default:
                $result['beginDate'] = $lesson->getBeginDate();
                $result['endDate'] = $lesson->getEndDate();
                $result['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_DATES;
                if ($result['beginDate'] == $result['endDate']) {
                    $result['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_TIMES;
                    $result['currentDate'] = $result['beginDate'];
                    $result['beginTime'] = $lesson->getBeginTime();
                    $result['endTime'] = $lesson->getEndTime();
                    unset($result['beginDate']);
                    unset($result['endDate']);
                }
                break;
        }

        switch ($lesson->getType()) {
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
            case HM_Event_EventModel::TYPE_TASK:
            case HM_Event_EventModel::TYPE_POLL:
                $test = $this->getOne($this->getService('Test')->fetchAll(
                    $this->getService('Test')->quoteInto('lesson_id = ?', $lesson->SHEID)
                ));
                if ($test) {
                    // Набить форму данными теста
                    $result['mode'] = $test->mode;
                    $result['lim'] = $test->lim;
                    $result['qty'] = $test->qty;
                    $result['startlimit'] = $test->startlimit;
                    $result['limitclean'] = $test->limitclean;
                    $result['timelimit'] = $test->timelimit;
                    $result['random'] = $test->random;
                    //$values['adaptive'] = $test->adaptive;
                    $result['questres'] = $test->questres;
                    $result['showurl'] = $test->showurl;
                    $result['endres'] = $test->endres;
                    $result['skip'] = $test->skip;
                    $result['allow_view_url'] = $test->allow_view_url;
                    $result['allow_view_log'] = $test->allow_view_log;
                    $result['comments'] = $test->comments;
                    $result['material_id'] = $test->test_id;

                    if ($test->adaptive) {
                        $result['questions'] = HM_Test_TestModel::QUESTIONS_ADAPTIVE;
                    }
                }
                break;
            case HM_Event_EventModel::TYPE_TEST:
                $quest = $this->getOne($this->getService('Quest')->find($lesson->getModuleId()));
                if ($quest) {
                    $questSettings = $this->getService('QuestSettings')->fetchAll(array(
                        'quest_id = ?' => $quest->getValue('quest_id'),
                        'scope_id = ?' => $lesson->getValue('SHEID'),
                        'scope_type = ?' => HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON
                    ));
                    if (count($questSettings)) {
                        $questSettings = $questSettings->current();
                    } else {
                        continue;
                    }
                    // Набить форму данными теста
                    $result['mode_display'] = $questSettings->getValue('mode_display');
                    $result['mode_display_questions'] = $questSettings->getValue('mode_display_questions');
                    $result['mode_display_clusters'] = $questSettings->getValue('mode_display_clusters');
                    $result['mode_selection'] = $questSettings->getValue('mode_selection');
                    $result['mode_selection_questions'] = $questSettings->getValue('mode_selection_questions');
                    $result['mode_selection_questions_cluster'] = $questSettings->getValue('mode_selection_questions_cluster');
                    $result['mode_selection_all_shuffle'] = $questSettings->getValue('mode_selection_all_shuffle');
                    $result['limit_attempts'] = $questSettings->getValue('limit_attempts');
                    $result['limit_clean'] = $questSettings->getValue('limit_clean');
                    $result['limit_time'] = $questSettings->getValue('limit_time');
                    $result['mode_test_page'] = $questSettings->getValue('mode_test_page');
                    $result['show_log'] = $questSettings->getValue('show_log');
                    $result['show_result'] = $questSettings->getValue('show_result');
                    $result['mode_self_test'] = $questSettings->getValue('mode_self_test');
                }
                break;
        }

        $formTypeId = $lesson->typeID;
        $formCustomEvent = $this->getService('Event')->fetchRow(array('event_id = ?' => abs($formTypeId)));

        if ($formCustomEvent) {
            if (($lesson->getType() == HM_Event_EventModel::TYPE_TEST ||
                    $formTypeId == HM_Event_EventModel::TYPE_TEST ||
                    $formCustomEvent->tool == HM_Event_EventModel::TYPE_TEST) &&
                $questId !== null
            ) {
                $result['material_id'] = $questId;
            }
        }

        return $result;
    }

    public function _postProcessLessonSave(HM_Lesson_LessonModel $lesson, HM_Form_Lesson $form)
    {
        if($lesson->material_id) {
            switch ($lesson->getType()) {
                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_POLL:
                    /** @var HM_Quest_QuestModel $quest */
                    if ($quest = $this->getService('Quest')->findOne($lesson->material_id)) {
                        $this->getService('Quest')->_postProcessQuest($quest, $lesson->CID, $lesson->SHEID, $form);
                    }
                    break;
                case HM_Event_EventModel::TYPE_ECLASS:
                    $students = $lesson->getService()->getAvailableStudents($lesson->CID);
                    $this->getService('Eclass')->webinarPush(['lesson' => $lesson, 'students' => $students]);
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
    }

    // @todo: check availability
    public function getContextSwitcherData($currentLesson)
    {
        return []; // не имеет смысла для занятий, т.к. могут быть ограничения

        $switcherData = [];
        $siblingLessons = $this->getService('Lesson')->fetchAll([
            'CID = ?' => $currentLesson->CID,
            'SHEID != ?' => $currentLesson->SHEID,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN, // не используется более, но для обратной совместимости
        ], 'order');

        foreach ($siblingLessons as $lesson) {

            $name = $lesson->title ? : '';
            $url = Zend_Registry::get('view')->url(array('lesson_id' => $lesson->SHEID));

            $switcherData[$lesson->SHEID] = compact('name', 'url');
        }

        return $switcherData;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('занятие plural', '%s занятие', $count), $count);
    }

    public function pluralFormCountPrepositionalCase($count)
    {
        return !$count ? _('Нет') : sprintf(_n('занятиях plural', '%s занятии', $count), $count);
    }

    public function getNextLesson($currentLessonId)
    {
        $currentLesson = $this->findOne($currentLessonId);

        $lessonsList = $this->fetchAll(
            [
                'CID = ?' => $currentLesson->CID,
                'isfree NOT IN (?)' => [HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED] // на всякий случай
            ],
            $this->getLessonOrderFields()
        );

        $returnLesson = false;

        foreach ($lessonsList as $lesson) {

            if($returnLesson) {
                return $lesson;
            }

            if($lesson->SHEID == $currentLessonId) {
                $returnLesson = true;
            }
        }

        return false;
    }

    public function getLessonOrderFields()
    {
        return ['order', 'SHEID'];
    }

    public function getExecuteUrl($lessonId, $subjectId)
    {
        return [
            'module' => 'subject',
            'controller' => 'lesson',
            'action' => 'index',
            'lesson_id' => $lessonId,
            'subject_id' => $subjectId,
        ];
    }

    public function getLessonsOrder(array $lessons)
    {
        $sortedLesons = [];
        foreach ($lessons as $lesson){
            $sortedLesons[] = [
                'id' => $lesson['id'],
                'order' => $lesson['order']
            ];
        }

        usort($sortedLesons, function($a, $b) {
            return strnatcmp($a['order'], $b['order']);
        });

        return $sortedLesons;
    }

    public function getStudentLessons($student, $subjectId)
    {
        $lessonService = $this->getService('Lesson');

        $subSelect = $lessonService->getSelect();
        $subSelect->from(array('ul' => 'scheduleID'), 'SHEID')
            ->where('MID = ?', $student);

        $lessons = $lessonService->fetchAll(
                array(
            'CID = ?' => $subjectId,
            'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
            'isfree IN (?)' => array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_PLAN),
             'SHEID IN ?' => $subSelect), array('order')
        );

        $sheids = $lessons->getList('SHEID');
        $assigns = array();
        if(count($sheids)) {
            $select = $lessonService->getSelect();
            $select->from(array('ul' => 'scheduleID'), array('SHEID', 'V_STATUS'))
                ->where('MID = ?', $student);
            $select->where('SHEID IN (?)', $sheids);
            $assigns = $select->query()->fetchAll();
        }
        $marks = array();
        foreach($assigns as $assign) {
            $marks[$assign['SHEID']] = $assign['V_STATUS'];
        }
        foreach($lessons as $lesson) {
            $lesson->V_STATUS = $marks[$lesson->SHEID];
        }

        return $lessons;
    }

    // TODO: наверное, не очень хорошее решение с $unlinkSection
    // В связи с добавлением разделов в План занятий нужно как-то отвязывать занятие от раздела.
    // Увы, фронт отправляет всегда обновлённый порядок занятий и разделов целиком и нет никакого экшена для удаления из раздела отдельно.
    // Чтобы не делать ополнительные запросы в БД для отвязки от раздела, решил использовать $unlinkSection
    // Возможно, это нужно будет исправить. А, может, и не нужно исправлять...
    public function setOrder($lessons, $unlinkSection = false)
    {
        $result = false;

        if (count($lessons)) {
            foreach ($lessons as $key => $lessonId) {
                $lesson = $this->findOne($lessonId);
                if ($lesson) {
                    $fields = array('order' => $key);

                    if ($unlinkSection) {
                        $fields['section_id'] = 0;
                    }

                    $this->updateWhere(
                        $fields,
                        array('SHEID = ?' => $lessonId)
                    );
                }
            }
            $result = true;
        }

        return $result;
    }
}
