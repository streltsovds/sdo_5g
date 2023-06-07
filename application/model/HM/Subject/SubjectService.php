<?php

class HM_Subject_SubjectService extends HM_Service_Abstract implements HM_Service_Rest_Interface
{
    protected $_cache = [
        'getById' => []
    ];

    const EVENT_GROUP_NAME_PREFIX = 'COURSE_ACTIVITY';

    /**
     * кеш занятий пользователя по курсам
     * используется при подсчете статуса и процента прохождения
     * @var array
     */
    private $_userLessonsCache = array();

    private $_subjectsColorsCache = null;

    private $_subjectCache = null;

    /**
     * Кеш соответствий ID оригинальных сущностей в курсе и их копий
     * @var array
     */
    private $_subjectCopyCache = array();

    public function insert($data, $unsetNull = true)
    {
        $data = $this->_prepareData($data);
        $subject = null;
        if ($subject = parent::insert($data)) {

            // создаем дефолтную секцию для материалов в своб.доступе
            // $this->getService('Section')->createSection($subject->subid);
        }
        return $subject;
    }

    public function update($data, $unsetNull = true)
    {
        $data = $this->_prepareData($data);
        $subject = parent::update($data, $unsetNull);

        $this->setLastUpdated($subject->subid);

        if ($subject) {
            $this->getService('Eclass')->subjectWebinarsReassign($subject->subid);
            $this->getService('PollFeedback')->updateWhere(array('subject_name' => $subject->name), $this->quoteInto('subject_id = ?', $subject->subid));
            $timeEndedPlanned = false;
            $now = date('Y-m-d H:i:s');

            if ($subject->longtime) {
                $timeEndedPlanned = HM_Date::getRelativeDate(new Zend_Date($now), (int) $subject->longtime);
            } elseif ($subject->end) {
                $timeEndedPlanned = new Zend_Date($subject->end);
            } elseif ($subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
                $timeEndedPlanned = new Zend_Date($subject->end);
            }

            if ($timeEndedPlanned) {
                $this->getService('Student')->updateWhere(
                    array('end_personal' => ($subject->period == HM_Project_ProjectModel::PERIOD_FREE) ? null : $timeEndedPlanned->get('Y-MM-dd') . ' 23:59:59'),
                    $this->quoteInto('CID = ?', $subject->subid)
                );
            }
        }

        return $subject;
    }

    /**
     * @param $subjectId
     * @param HM_Form_Element_Html5File|HM_Form_Element_ServerFile $photo Элемент формы
     * @param $destination Путь к папке с иконками
     * @param bool $skipResize
     * @param int $removeIcon Удалить icon?
     * @return bool
     * @throws Exception
     * @todo Реализовать возможность выбирать размер иконок, решить как их сохранять (менять название файла/создавать папку)
     */
    public static function updateIcon($subjectId, $photo, $destination = null, $skipResize = false, $removeIcon = 0)
    {
        if (empty($destination)) {
            $destination = HM_Subject_SubjectModel::getIconFolder($subjectId);
            $isSubject = true;
        } else {
            $isSubject = false;
        }
        $w = HM_Subject_SubjectModel::THUMB_WIDTH;
        $h = HM_Subject_SubjectModel::THUMB_HEIGHT;

        $path = rtrim($destination, '/') . '/' . $subjectId . '.jpg';

        if ($removeIcon) {
            unlink($path);
            return true;
        }

        if (is_null($photo)) return false;

        if ($photo instanceof HM_Form_Element_ServerFile) {
            $photoVal = $photo->getValue();
            //если инпут пустой - удаляем текущее изображение
            if (empty($photoVal)) {
                unlink($path);
                return true;
            }
            $original = APPLICATION_PATH . '/../public' . $photoVal;
            //если новая картинка = старой, то ничего не меняем
            if (md5_file($original) == md5_file($path)) {
                return true;
            }
            if ($skipResize) {
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            // костыль для виджета subjectSlider
            if ($isSubject) {
                $img->adaptiveResize($w, $h);
            }
            $img->save($path);
        } elseif ($photo->isUploaded()) {
            $original = rtrim($photo->getDestination(), '/') . '/' . $photo->getValue();
            if ($skipResize) {
                $path = rtrim($destination, '/') . '/' . $subjectId . '-full.jpg';
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);

            // костыль для виджета subjectSlider    
            if ($isSubject) {
                $img->adaptiveResize($w, $h);
            }
            $img->save($path);
            unlink($original);
        }
        return true;
    }

    private function _prepareData($data)
    {
        if (isset($data['period']) && ($data['period'] !== '')) {
            switch ($data['period']) {
                case HM_Subject_SubjectModel::PERIOD_FREE:
                    $today = new HM_Date();
                    $data['begin'] = (string) $today->getDate();
                    $data['end'] = (string) $today->getDate();
                    break;

                case HM_Subject_SubjectModel::PERIOD_FIXED:
                    $today = new HM_Date();
                    $data['begin'] = (string)$today->getDate();

                    $end = HM_Date::getRelativeDate($today, (int)$data['longtime']);
                    $data['end'] = (string)$end->getDate();
                    break;

                case HM_Subject_SubjectModel::PERIOD_DATES:
                    // если дата начала и дата окончания совпадают - это значит курс идет один день!!!
                    //                    if (!empty($data['begin']) && ($data['begin'] == $data['end'])) {
                    //                        $data['begin'] = $data['end'] = '';
                    //                    }
                    //                    $today = new HM_Date();
                    //                    if (!$data['begin']) {
                    //                        $data['begin'] = (string) $today->getDate();
                    //                    }
                    //                    $today->add(1, HM_Date::MONTH);
                    //                    if (!$data['end']) {
                    //                        $data['end'] = (string) $today->getDate();
                    //                    }
                    if (!empty($data['end']) && strtotime($data['end'])) {
                        $date = new Zend_Date(strtotime($data['end']));
                        $date->add(1, HM_Date::DAY);
                        $date->sub(1, HM_Date::SECOND);
                        $data['end'] = $date->toString('dd.MM.y H:m:s');
                    }
                    break;
                default:
                    unset($data['period']);
                    break;
            }
        } else {
            unset($data['period']);
        }
        return $data;
    }

    public function delete($subjectId)
    {
        if ($subject = $this->find($subjectId)->current()) {
            if ($subject->base_id && ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION)) {
                if (count($this->getSessions($subject->base_id)) == 1) { // последний из могикан
                    $this->updateWhere(array('base' => HM_Subject_SubjectModel::BASETYPE_PRACTICE), array('subid = ?' => $subject->base_id));
                }
            }
        }

        $lessons = $this->getService('Lesson')->fetchAll($this->quoteInto('CID = ?', $subjectId));
        if (count($lessons)) {

            $lessonIds = $lessons->getList('SHEID');
            $lessonAssigns = $this->getService('LessonAssign')->fetchAll($this->quoteInto('SHEID IN (?)', $lessonIds));
            if (count($lessonAssigns)) {
                $lessonAssignsIds = $lessonAssigns->getList('SSID');
                $this->getService('LessonAssignMarkHistory')->deleteBy($this->quoteInto('SSID IN (?)', $lessonAssignsIds));
                $this->getService('LessonAssign')->deleteBy($this->quoteInto('SHEID IN (?)', $lessonIds));
            }

            $questAtttempts = $this->getService('QuestAttempt')->fetchAll($this->quoteInto('context_event_id IN (?)', $lessonIds));
            if (count($questAtttempts)) {
                $questAtttemptIds = $questAtttempts->getList('attempt_id');
                $this->getService('QuestAttemptCluster')->deleteBy($this->quoteInto('quest_attempt_id IN (?)', $questAtttemptIds));
                $this->getService('QuestAttempt')->deleteBy($this->quoteInto('context_event_id IN (?)', $lessonIds));
            }

            // todo: task_conservations ?

            $this->getService('Lesson')->deleteBy($this->quoteInto('SHEID IN (?)', $lessonIds));
        }

        $this->getService('CourseItemHistory')->deleteBy($this->quoteInto('cid = ?', $subjectId));
        $this->getService('ScormTrack')->deleteBy($this->quoteInto('cid = ?', $subjectId));

        // Удаляем связки из subjects_courses
        $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto('subject_id = ?', (int) $subjectId)
        );

        $this->getService('StudyGroupCourse')->deleteBy($this->quoteInto('course_id = ?', $subjectId));
        $this->getService('Section')->deleteBy($this->quoteInto('subject_id = ?', $subjectId));
        $this->getService('Student')->deleteBy($this->quoteInto('CID = ?', $subjectId));
        $this->getService('Teacher')->deleteBy($this->quoteInto('CID = ?', $subjectId));
        $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(array('type = ?', ' AND item_id = ?'), array(HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $subjectId))
        );
        $countGraduated = $this->getService('Graduated')->fetchAll(array('CID = ?' => $subjectId));
        if (count($countGraduated) > 0)
            $this->getService('Graduated')->deleteBy($this->quoteInto('CID = ?', $subjectId));

        return parent::delete($subjectId);
    }

    public function linkRooms($subjectId, $rooms)
    {
        $this->unlinkRooms($subjectId);
        if (is_array($rooms) && count($rooms)) {
            foreach ($rooms as $roomId) {
                if ($roomId > 0) {
                    $this->linkRoom($subjectId, $roomId);
                }
            }
        }
        return true;
    }

    public function linkRoom($subjectId, $roomId)
    {
        $this->getService('SubjectRoom')->deleteBy(array('cid = ?' => $subjectId));
        return $this->getService('SubjectRoom')->insert(
            array(
                'cid' => $subjectId,
                'rid'  => $roomId
            )
        );
    }

    public function unlinkRooms($subjectId)
    {
        return $this->getService('SubjectRoom')->deleteBy(
            $this->quoteInto('cid = ?', $subjectId)
        );
    }

    public function linkClassifiers($subjectId, $classifiers)
    {
        $this->getService('Classifier')->unlinkItem($subjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
        if (is_array($classifiers) && count($classifiers)) {
            foreach ($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($subjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifierId);
                }
            }
        }
        return true;
    }

    public function linkClassifier($subjectId, $classifierId)
    {
        return $this->getService('SubjectClassifier')->insert(
            array(
                'subject_id' => $subjectId,
                'classifier_id'  => $classifierId
            )
        );
    }

    public function unlinkClassifiers($subjectId)
    {
        return $this->getService('SubjectClassifier')->deleteBy(
            $this->quoteInto('subject_id = ?', $subjectId)
        );
    }

    public function unlinkCourse($subjectId, $courseId)
    {
        return $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto(array('subject_id = ?', ' AND course_id = ?'), array($subjectId, $courseId))
        );
    }

    public function linkCourse($subjectId, $courseId)
    {
        $result = $this->getService('SubjectCourse')->fetchRow([
            'subject_id = ?' => $subjectId,
            'course_id = ?'  => $courseId
        ]);

        if (!$result) {
            $result = $this->getService('SubjectCourse')->insert(
                array(
                    'subject_id' => $subjectId,
                    'course_id'  => $courseId
                )
            );
        }

        return $result;
    }

    public function unlinkCourses($subjectId)
    {
        return $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto('subject_id = ?', $subjectId)
        );
    }

    public function getCourses($subjectId, $status = null)
    {
        if (null == $status) {
            return $this->getService('Course')->fetchAllDependenceJoinInner(
                'SubjectAssign',
                $this->quoteInto('SubjectAssign.subject_id = ?', $subjectId),
                'self.Title'
            );
        } else {
            return $this->getService('Course')->fetchAllDependenceJoinInner(
                'SubjectAssign',
                $this->quoteInto(array('SubjectAssign.subject_id = ?', ' AND self.Status = ?'), array($subjectId, $status)),
                'self.Title'
            );
        }
    }

    public function getFreeSubjects($resultsCount = 20, $userId = null, $excludedSubjectsIds = [])
    {
        /*
        $subjects = $this->fetchAllDependenceJoinInner(
            'Student',
            $this->quoteInto(array(
                '(reg_type = ?',
                ' OR reg_type = ?)',
                ' AND end > ?',
                ' AND registered IS NULL',
            ),
            array(
                HM_Subject_SubjectModel::REGTYPE_FREE,
                HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN,
                $this->getDateTime(),
            ))
        );
        */

        $select = $this->getSelect();
        if (!$resultsCount) $select->distinct();
        $select->from(
            array('s' => 'subjects'),
            array(
                'subject_id' => 's.subid'
            )
        );

        if ($userId) {
            $select->joinLeft(
                array('st' => 'Students'),
                'st.CID = s.subid AND st.MID = ' . $userId,
                array('registeged' => 'st.registered')
            );
            $select->where('registered IS NULL');
        }

        $subSelect = $this->getSelect();
        $subSelect->from(
            array('s' => 'subjects'),
            array(
                'subid' => 's.subid',
                'limit_reached' => new Zend_Db_Expr("
                    CASE 
                        WHEN s.plan_users=0 THEN 
                            0
                        ELSE
                            CASE 
                                WHEN (s.plan_users - COUNT(sts.MID)- COUNT(cls.MID)) > 0 THEN
                                    0 
                                ELSE 
                                    1 
                            END
                    END
                ")
            )
        )
            ->joinLeft(
                array('sts' => 'Students'),
                'sts.CID=s.subid',
                array()
            )
            ->joinLeft(
                array('cls' => 'claimants'),
                's.subid = cls.CID AND cls.status = ' . HM_Role_ClaimantModel::STATUS_NEW,
                array()
            )
            ->where('s.type = ?', HM_Subject_SubjectModel::TYPE_DISTANCE)
            ->group(array('s.subid', 's.plan_users'));


        $select->joinLeft(
            array('sl' => $subSelect),
            'sl.subid=s.subid',
            array()
        )
            ->where('sl.limit_reached = 0');


        $select->where($this->quoteInto(
            array('s.reg_type = ?', ' OR s.reg_type = ?'),
            array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN)
        ))
            ->where($this->quoteInto(
                array('s.period <> ?', ' OR s.end > ?'),
                array(HM_Subject_SubjectModel::PERIOD_DATES, $this->getDateTime())
            ));

        if (count($excludedSubjectsIds)) {
            $select->where($this->quoteInto(
                ['s.subid not in (?)'],
                [$excludedSubjectsIds]
            ));
        }

        if ($resultsCount) $select->limit($resultsCount);

        $tmp = $select->query()->fetchAll();
        $tmp = (is_array($tmp)) ? $tmp : array();

        $freeSubjects = array();
        foreach ($tmp as $value) {
            $freeSubjects[] = $value['subject_id'];
        }

        return $freeSubjects;
    }

    public function isTeacher($subjectId, $userId)
    {
        return $this->getService('Teacher')->isUserExists($subjectId, $userId);
    }

    public function isStudent($subjectId, $userId)
    {
        return $this->getService('Student')->isUserExists($subjectId, $userId);
    }

    public function isLimitReached($subjectId)
    {
        $select = $this->getSelect();
        $select->from(
            array('s' => 'subjects'),
            array(
                'subid' => 's.subid',
                'student' => 'COUNT(sts.SID)',
                'climant' => 'COUNT(cls.SID)',
                'plan_users' => 's.plan_users'
            )
        )
            ->joinLeft(
                array('sts' => 'Students'),
                'sts.subid=s.subid',
                array()
            )
            ->joinLeft(
                array('cls' => 'claimants'),
                's.subid = cls.subid AND cls.status = ' . HM_Role_ClaimantModel::STATUS_NEW,
                array()
            )
            ->where('s.subid=' . $subjectId)
            ->group(array('s.subid', 's.plan_users'));

        $result = $select->query()->fetch();
        if (!$result['plan_users'] || ($result['plan_users'] > ($result['student'] + $result['climant']))) {
            return false;
        }
        return true;
    }

    public function isGraduated($subjectId, $userId)
    {
        return $this->getService('Graduated')->isUserExists($subjectId, $userId);
    }

    /**
     * Возвращает модели юзеров, присвоенных определенному курсу
     * @param unknown_type $subject_id  Id Курса
     * @return multitype:
     */
    public function getAssignedUsers($subject_id)
    {
        $collection = $this->getService('User')->fetchAllJoinInner('Student', 'Student.CID = ' . (int) $subject_id);
        return $collection;
    }

    public function getAssignedTeachers($subject_id)
    {
        $collection = $this->getService('User')->fetchAllJoinInner('Teacher', 'self.blocked = 0 AND Teacher.CID = ' . (int) $subject_id);
        return $collection;
    }

    public function getAssignedGraduated($subject_id)
    {
        $collection = $this->getService('User')->fetchAllJoinInner('Graduated', 'Graduated.CID = ' . (int) $subject_id);
        return $collection;
    }

    public function assignUser($subjectId, $userId)
    {
        $subject = $this->getOne($this->find($subjectId));
        if ($subject) {
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
//                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
            ))) {
                $this->assignStudent($subjectId, $userId);
            } else {
                if ($subject->claimant_process_id == HM_Subject_SubjectModel::APPROVE_NONE) {
                    $this->assignAcceptedClaimant($subjectId, $userId);
                } else {
                    $this->assignClaimant($subjectId, $userId);
                }
            }
        }
    }

    public function assignGraduated($subjectId, $userId, $status = NULL, $period = -1, $fromDate = null)
    {
        $result = false;
        $student = $this->getOne(
            $this->getService('Student')->fetchAll(
                array(
                    'CID = ?' => $subjectId,
                    'MID = ?' => $userId
                )
            )
        );
        if ($student) {

            $type = $period && ($period != -1) && !empty($fromDate) ? HM_Certificates_CertificatesModel::TYPE_CERTIFICATE : HM_Certificates_CertificatesModel::TYPE_CERTIFICATE_ELS;
            $certificate = $this->getService('Certificates')->addCertificate($userId, $subjectId, $period, $fromDate, $type);
            $certificate_id = ($certificate) ? $certificate->certificate_id : 0;

            if ($status === NULL) {
                $status = HM_Role_GraduatedModel::STATUS_SUCCESS;
            }
            $data = array(
                'MID'            => $userId,
                'CID'            => $subjectId,
                'begin'          => $student->time_registered,
                'status'         => (int) $status,
                'certificate_id' => $certificate_id,
                'application_id' => $student->application_id,
            );

            $result = $this->getService('Graduated')->insert(
                $data,
                false,
                false
            );

            $service = $this->getService('SubjectMark');
            $userSubjectMark = $service->getOne($service->fetchAll('mid = ' . intval($userId) . ' AND cid = ' . intval($subjectId)));
            if ($userSubjectMark) {

                //[ES!!!] //array('mark' => $userSubjectMark)//Это как-бы перевод, зачем здесь было? В итоге сделано в LessonAssign::setUserScore

            } else {

                $data = array(
                    'cid' => intval($subjectId),
                    'mid' => intval($userId),
                    'mark' => -1,
                    'confirmed' => 0,
                    'comments' => '',
                    'certificate_validity_period' => $period
                );

                $userSubjectMark = $service->insert($data);
            }

            $this->getService('Student')->deleteBy(array('CID = ?' => $subjectId, 'MID = ?' => $userId));

            // #16545
            $subject = $this->getOne($this->findDependence('Lesson', $subjectId));
            foreach ($subject->lessons as $lesson) {
                $this->getService('Subscription')->unsubscribeUserFromChannelByLessonId($userId, $lesson->SHEID);
            }

            /**
             * при автоматическом завершении курса,
             * у пользователя удаляется роль student и текущая роль в 
             * $GLOBALS['controller']->user->profile_current должна стать user,
             * но она становаится NULL из-за этого главное меню отображается 
             * как для незалогиненного пользователя.
             * ниже проверяется есть ли у пользователя еще курсы (если есть, то меню и так остается нормальным)
             * и является ли текущий пользователь тем, у кого завершен курс (текущий пользователь может быть и учителем, 
             * который вручную переводит студента в завершивших курс), затем принудительно выставляется роль "USER"
             * 
             * @todo разобраться почему удаляется profile_current из $GLOBALS['controller']->user
             */
            $UserHasSubjects = count($this->getService('Student')->getSubjects($userId));
            $isStudent = ($userId == $this->getService('User')->getCurrentUserId());
            if (!$UserHasSubjects && $isStudent) {
                $this->getService('User')->switchRole(HM_Role_Abstract_RoleModel::ROLE_USER);
            }
            //


            // назначение кураторских опросов "всем новым"
            $this->getService('Feedback')->onStudentGraduate($userId, $subjectId);
        }
        return $result;
    }

    public function unassignGraduated($subjectId, $userId)
    {
        $subject = $this->getOne($this->findDependence(array('Graduated', 'Lesson'), $subjectId));
        if ($subject) {
            if ($subject->isGraduated($userId)) {
                $this->getService('Graduated')->deleteBy(sprintf("MID = '%d' AND CID = '%d'", $userId, $subjectId));
            }

            $lessons = $subject->getLessons();
            if (count($lessons)) {
                foreach ($lessons as $lesson) {
                    $lesson->getService()->unassignStudent($lesson->SHEID, $userId);
                    /*
                    if (!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
                        $this->getService('Lesson')->unassignStudent($lesson->SHEID, $userId);
                    }
                    if (in_array($lesson->typeID, array_keys(HM_Event_EventModel::getDeanPollTypes()))) {
                        $this->getService('LessonDeanPoll')->unassignStudent($lesson->SHEID, $userId);
                    }

                     */
                }
            }
        }
    }

    /**
     * Добавляет заявку со статусом HM_Role_ClaimantModel::STATUS_ACCEPTED
     * @param int $subjectId
     * @param int $userId
     */
    public function assignAcceptedClaimant($subjectId, $userId)
    {
        $subject = $this->getOne($this->findDependence('Claimant', $subjectId));
        if ($subject) {
            if (!$subject->isClaimant($userId)) {
                $user = $this->getOne($this->getService('User')->find($userId));
                $this->getService('Claimant')->insert(
                    array(
                        'MID' => $userId,
                        'CID' => $subjectId,
                        'created' => $this->getDateTime(),
                        'begin' => $this->getDateTime(),
                        'end' => $subject->end,
                        'status' => HM_Role_ClaimantModel::STATUS_ACCEPTED,
                        'lastname' => $user->LastName,
                        'firstname' => $user->FirstName,
                        'patronymic' => $user->Patronymic
                    )
                );
            }
            $this->assignStudent($subjectId, $userId);
        }
    }


    public function assignClaimant($subjectId, $userId)
    {
        $subject = $this->getOne($this->findDependence('Claimant', $subjectId));
        if ($subject) {
            if (!$subject->isClaimant($userId)) {
                $user = $this->getOne($this->getService('User')->find($userId));
                $lastName      =   $user->LastName;
                $firstName     =   $user->FirstName;
                $patronymic    =   $user->Patronymic;
                $mid           =   $user->MID;
                $mid_external   =   $user->mid_external;
                //Делаем запрос в БД(Table)`Claimant` и проверяем существует ли такой пользователь
                //если существует, то кладем в переменную dublicated MID пользователя на которого
                //похож, регистрирующийся пользователь - дубликат
                //$dublicated = $this->getService('Claimant')->checkDublicate($lastName, $firstName, $patronymic, $mid, $mid_external);
                $this->getService('Claimant')->insert(
                    array(
                        'MID' => $userId,
                        'CID' => $subjectId,
                        'created' => $this->getDateTime(),
                        'begin' => $this->getDateTime(),
                        'end' => $subject->end
                        //'lastname' => $user->LastName,
                        //'firstname' => $user->FirstName,
                        //'patronymic' => $user->Patronymic,
                        //'dublicate' => $dublicated,
                        //'mid_external'=> $user->mid_external,
                    )
                );
                $dublicated = $this->getService('Claimant')->updateClaimant();
                // Сообщение администрации
                $messenger = $this->getService('Messenger');
                $messenger->addMessageToChannel(
                    HM_Messenger::SYSTEM_USER_ID,
                    HM_Messenger::SYSTEM_USER_ID,
                    HM_Messenger::TEMPLATE_ORDER,
                    array(
                        'subject_id' => $subjectId,
                        'url_user' => Zend_Registry::get('view')->serverUrl(
                            Zend_Registry::get('view')->url(array(
                                'module' => 'user',
                                'controller' => 'edit',
                                'action' => 'card',
                                'user_id' => $userId
                            ), null, true)
                        ),
                        'user_login'       => $user->Login,
                        'user_name'        => $user->LastName .' '. $user->FirstName .' '. $user->Patronymic,
                        'user_lastname'    => $user->LastName,
                        'user_firstname'   => $user->FirstName,
                        'user_patronymic'  => $user->Patronymic,
                        'user_email'        => $user->EMail,
                        'user_mail'        => $user->EMail,
                        'user_phone'       => $user->Phone,
                        'subject_price'    => $subject->price,
                        'subject_currency' => $subject->price_currency
                    )
                );
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER,
                    array(
                        'subject_id' => $subjectId,
                        'url_user' => Zend_Registry::get('view')->serverUrl(
                                        Zend_Registry::get('view')->url(array(
                                            'module' => 'user',
                                            'controller' => 'edit',
                                            'action' => 'card',
                                            'user_id' => $userId
                                        ), null, true)
                                    ),
                        'user_login'       => $user->Login,
                        'user_name'        => $user->LastName .' '. $user->FirstName .' '. $user->Patronymic,
                        'user_lastname'    => $user->LastName,
                        'user_firstname'   => $user->FirstName,
                        'user_patronymic'  => $user->Patronymic,
                        'user_mail'        => $user->EMail,
                        'user_phone'       => $user->Phone,
                        'subject_price'    => $subject->price,
                        'subject_currency' => $subject->price_currency
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::SYSTEM_USER_ID);

                // Сообщение пользователю
                $messenger->addMessageToChannel(
                    HM_Messenger::SYSTEM_USER_ID,
                    $userId,
                    HM_Messenger::TEMPLATE_ORDER_REGGED,
                    array(
                        'subject_id' => $subjectId
                    )
                );
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER_REGGED,
                    array(
                        'subject_id' => $subjectId
                    )
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
            }
        }
    }

    public function assignStudent($subjectId, $userId, $params = array())
    {
        $event = null;
        $programmEventUserIds = array();
        if (isset($params['event'])) {
            $event = $params['event'];
            if (isset($event->programmEventUser) && count($event->programmEventUser)) {
                $programmEventUserIds = $event->programmEventUser->getList('user_id', 'programm_event_user_id');
            }
        }

        if (isset($params['newcomer_id']) && $params['newcomer_id']) {
            $newcomer_id = $params['newcomer_id'];
        } else {
            $newcomer_id = null;
        }

        if (isset($params['application_id']) && $params['application_id']) {

            $application_id = $params['application_id'];
            // это какая-то нехорошая ситуация - в прошлую сессию планирования не была назначена сессия обучения

            $this->getService('Student')->deleteBy(
                $this->quoteInto(array('CID = ?', ' AND MID = ? AND application_id IS NOT NULL'), array($subjectId, $userId))
            );
        } else {
            $application_id = null;
        }

        if ($this->_subjectCache === null || $this->_subjectCache->subid != $subjectId) {
            $this->_subjectCache = $this->getOne($this->fetchAllHybrid(array('Student', 'Lesson', 'ClassifierLink'), 'Room', 'SubjectRoom', array('subid = ?' => $subjectId)));
        }

        /** @var HM_Subject_SubjectModel $subject */
        $subject = $this->_subjectCache;

        if ($subject) {
            if (!($student = $subject->isStudent($userId))/*&& !$this->isLimitReached($subjectId)*/) {

                $beginPersonal = $timeRegistered = new HM_Date();
                $endPersonal = false;

                $programmEventUserId = isset($programmEventUserIds[$userId]) ? $programmEventUserIds[$userId] : null;

                if ($event) {

                    // если курс назначается через программу нач.обучения - даты берутся из длительности programm_event'а
                    $beginPersonal = HM_Date::getRelativeDate(new HM_Date(), (int)$event->day_begin);
                    $endPersonal = HM_Date::getRelativeDate(new HM_Date(), (int)$event->day_end);
                } else {

                    switch ($subject->period) {
                        case HM_Subject_SubjectModel::PERIOD_DATES:
                            $beginPersonal = new HM_Date($subject->begin);
                            $endPersonal = new HM_Date($subject->end);
                            break;
                        case HM_Subject_SubjectModel::PERIOD_FIXED:
                            $endPersonal = HM_Date::getRelativeDate(new HM_Date($beginPersonal), (int)$subject->longtime);
                            break;
                    }
                }

                $this->getService('SubjectMark')->deleteBy([
                    'cid = ? ' => $subjectId,
                    'mid = ? ' => $userId
                ]);

                $newStudent = $this->getService('Student')->insert(
                    array(
                        'MID' => $userId,
                        'CID' => $subjectId,
                        'Registered' => time(),
                        'newcomer_id' => $newcomer_id,
                        'programm_event_user_id' => $programmEventUserId,
                        'application_id' => $application_id,
                        'time_registered' => $timeRegistered->get('Y-MM-dd'),
                        'begin_personal' => $beginPersonal->get('Y-MM-dd'),
                        'end_personal' => $endPersonal ? $endPersonal->get('Y-MM-dd') . ' 23:59:59' : null,
                    )
                );

                // assign course lessons
                $lessons = $subject->getLessons();
                if (count($lessons)) {
                    foreach ($lessons as $lesson) {
                        if (
                            $lesson->all
                            && $lesson->isfree != HM_Lesson_LessonModel::MODE_FREE_BLOCKED
                            && !in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))
                        ) {
                            $lesson->getService()->assignStudent($lesson->SHEID, $userId);
                        }
                    }
                }

                // Отправка сообщения о назначении на учебный курс
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();

                $end = $endPersonal ? $endPersonal->get('Y-MM-dd') . ' 23:59:59' : null;

                /** @var HM_Messenger $messenger */
                $messenger = $this->getService('Messenger');
                $template = $subject->base_id ? HM_Messenger::TEMPLATE_ASSIGN_SUBJECT_SESSION : HM_Messenger::TEMPLATE_ASSIGN_SUBJECT;
                $template = $subject->is_labor_safety ? HM_Messenger::TEMPLATE_ASSIGN_LABOR_SAFETY_EVENT : $template;

                $options = [
                    'user_id' => $userId,
                    'subject_id' => $subjectId,
                    'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_STUDENT],
                    'begin' => $subject->period == HM_Subject_SubjectModel::PERIOD_FREE ? _('[без ограничений]') : date('d.m.Y', strtotime($newStudent->begin_personal)),
                    'end' => $subject->period == HM_Subject_SubjectModel::PERIOD_FREE ? _('[без ограничений]') : date('d.m.Y', strtotime($newStudent->end_personal)),
                    'room' => count($subject->room) ? $subject->room->current()->name : '[не указано]',
                ];

                $messenger->setOptions(
                    $template,
                    $options,
                    'subject',
                    $subjectId
                );
                $templateModel = $this->getService('Notice')->getOne(
                    $this->getService('Notice')->fetchAll(
                        $this->getService('Notice')->quoteInto(
                            'type = ?',
                            $template
                        )
                    )
                );
                $messenger->setIcal(
                    HM_Messenger::getCalendar(
                        $messenger->replace($templateModel->title),
                        $messenger->replace($templateModel->title),
                        date('d.m.Y', strtotime($timeRegistered)),
                        $end ? date('d.m.Y', strtotime($end)) : $end
                    )
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);

                $this->getService('Feedback')->onStudentAssign($userId, $subjectId);
            } elseif ($newcomer_id) {
                $this->getService('Student')->updateWhere(array(
                    'newcomer_id' => $newcomer_id
                ), $this->quoteInto(array('CID = ?', ' AND MID = ?'), array($subjectId, $userId)));
            }
        }
    }

    public function startSubjectForStudent($subjectId, $userId)
    {
        $subject = $this->getOne($this->findDependence(array('Student', 'Lesson'), $subjectId));

        if ($subject) {
            if ($subject->isStudent($userId)) {

                $timeEndedPlanned = new Zend_Date($subject->end);

                $this->getService('Student')->updateWhere(
                    array(
                        'time_registered' => date('Y-m-d H:i:s'),
                        'end_personal' => $timeEndedPlanned ? $timeEndedPlanned->get('Y-MM-dd') . ' 23:59:59' : null,
                    ),
                    array(
                        'MID = ?' => $userId,
                        'CID = ?' => $subjectId,
                    )
                );


                // assign course lessons
                $lessons = $subject->getLessons();
                if (count($lessons)) {
                    foreach ($lessons as $lesson) {
                        if ($lesson->all && !in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
                            $lesson->getService()->assignStudent($lesson->SHEID, $userId);
                        }
                    }
                }

                // Отправка сообщения о назначении на учебный курс
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();

                /** @var HM_Messenger $messenger */
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    $subject->base_id ? HM_Messenger::TEMPLATE_ASSIGN_SUBJECT_SESSION : HM_Messenger::TEMPLATE_ASSIGN_SUBJECT,
                    [
                        'user_id' => $userId,
                        'subject_id' => $subjectId,
                        'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_STUDENT]
                    ],
                    'subject',
                    $subjectId
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
            }
        }
    }

    public function unassignStudent($subjectId, $userId)
    {
        $subject = $this->getOne($this->findDependence(array('Student', 'Lesson'), $subjectId));
        if ($subject) {
            if ($subject->isStudent($userId)) {
                $this->getService('Student')->deleteBy(sprintf("(MID = '%d' AND CID = '%d') OR (MID = '%d' AND CID = 0)", $userId, $subjectId, $userId));
                //$this->getService('Student')->deleteBy(sprintf("MID = '%d' AND CID = 0", $userId, $subjectId));
            }

            $lessons = $subject->getLessons();
            if (count($lessons)) {
                foreach ($lessons as $lesson) {
                    if (!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
                        $this->getService('Lesson')->unassignStudent($lesson->SHEID, $userId);
                    }
                }
            }

            $groups = $this->getService('Group')->fetchAll(array('cid = ?' => $subject->subid));
            if (count($groups)) {
                foreach ($groups as $group) {
                    $this->getService('GroupAssign')->deleteBy(array('mid = ?' => $userId, 'cid = ?' => $subject->subid, 'gid = ?' => $group->gid));
                }
            }
        }
    }

    public function assignTeacher($subjectId, $teacherId)
    {
        if (!$this->isTeacher($subjectId, $teacherId)) {
            $this->getService('Teacher')->insert(array(
                'MID' => $teacherId,
                'CID' => $subjectId,
            ));

            /** @var HM_Role_StudentService $studentService */
            /*
            $studentService = $this->getService('Student');
            $collection = $studentService->fetchAll(
                $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($teacherId, $subjectId))
            );

            if (!count($collection)) {
                $studentService->insert( // #11928
                    array(
                        'MID' => $teacherId,
                        'CID' => $subjectId,
                        'Registered' => time(),
                        'time_registered' => $studentService->getDateTime(),
                        'end_personal' => $studentService->getDateTime(strtotime('+5 year'))
                    )
                );
            }*/
        }
    }

    public function unassignTeacher($subjectId, $teacherId)
    {
        return $this->getService('Teacher')->deleteBy(
            $this->quoteInto(
                array('MID = ?', ' AND CID = ?'),
                array($teacherId, $subjectId)
            )
        );
    }


    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('курс plural', '%s курс', $count), $count);
    }

    public function pluralFormCountPrepositionalCase($count)
    {
        return !$count ? _('Нет') : sprintf(_n('курсах plural', '%s курсе', $count), $count);
    }
    /**
     *  Дата - время последнего обновления содержимого курса.
     *  Что считаем обновлением содержимого:
     *   - включение эл.курсов
     *   - включение инф.ресурсов
     *   - создание занятий в плане
     *
     *  Если дата в диапазоне [-бесконечность; 1год1месяц назад] - 0 баллов;
     *  [1год1месяц назад - 1месяц назад] - XX балов пропорционально;
     *  [1месяц назад - сейчас] - 100 баллов;
     */
    static public function calcFreshness($timestamp)
    {
        if ($timestamp > ($ceil = time() - 2592000)) { // 1месяц назад
            return 100;
        } elseif ($timestamp > ($floor = time() - 31104000)) { // 1год1месяц назад
            return 0;
        } else {
            return 100 * ($timestamp - $floor) / ($ceil - $floor);
        }
    }

    /**
     * Возвращает массив типов регистрации с наименованиями
     * @return multitype:NULL
     */
    public function getRegTypes()
    {
        return HM_Subject_SubjectModel::getRegTypes();
    }
    /**
     * Возвращает наименование типа регистрации
     * @return string
     */
    public function getRegType($typeId)
    {
        $arrTypes =  HM_Subject_SubjectModel::getRegTypes();
        if (!array_key_exists($typeId, $arrTypes)) {
            return '';
        }
        return $arrTypes[$typeId];
    }

    public function copyClassifiers($fromSubjectId, $toSubjectId)
    {
        $classifiers = $this->getService('ClassifierLink')->fetchAll(
            $this->quoteInto(
                ['item_id = ?', ' AND type = ?'],
                [$fromSubjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT]
            )
        );

        $this->getService('ClassifierLink')->deleteBy(
            $this->quoteInto(
                ['item_id = ?', ' AND type = ?'],
                [$toSubjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT]
            )
        );

        if (count($classifiers)) {
            $this->linkClassifiers($toSubjectId, $classifiers->getList('classifier_id', 'classifier_id'));
        }
    }

    public function copyExercises($fromSubjectId, $toSubjectId)
    {
        $links = $this->getService('SubjectExercise')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($links)) {
            foreach ($links as $link) {
                $link->subject_id = $toSubjectId;
                $this->getService('SubjectExercise')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyQuizzes($fromSubjectId, $toSubjectId)
    {

        $pollsLinks = array();

        $polls = $this->getService('Poll')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        $this->getService('Poll')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );
        $this->getService('SubjectPoll')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );

        if (count($polls)) {
            foreach ($polls as $poll) {
                $newPoll = $this->getService('Poll')->copy($poll, $toSubjectId);
                if ($newPoll) {
                    $pollsLinks[$poll->quiz_id] = $newPoll->quiz_id;
                    $this->_subjectCopyCache[HM_Event_EventModel::TYPE_POLL][$poll->quiz_id] = $newPoll->quiz_id;
                }
            }
        }

        $links = $this->getService('SubjectPoll')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($links)) {
            foreach ($links as $link) {
                $link->subject_id = $toSubjectId;
                if (isset($pollsLinks[$link->quiz_id])) {
                    $link->quiz_id = $pollsLinks[$link->quiz_id];
                }

                $this->getService('SubjectPoll')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copySections($fromSubjectId, $toSubjectId)
    {
        $sections = $this->getService('Section')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        $this->getService('Section')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );

        if (count($sections)) {
            foreach ($sections as $section) {
                $newSection = $this->getService('Section')->copy($section, $toSubjectId);
                if ($newSection) {
                    $this->_subjectCopyCache['sections'][$section->section_id] = $newSection->section_id;
                }
            }
        }
    }

    public function copyResources($fromSubjectId, $toSubjectId)
    {
        $resourcesLinks = [];

        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');

        /** @var HM_Subject_Resource_ResourceService $subjectResourceService */
        $subjectResourceService = $this->getService('SubjectResource');

        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');

        $resourceService->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));
        $subjectResourceService->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));

        $resourceSchedules = $lessonService->fetchAll([
            'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
            'CID = ?' => $fromSubjectId
        ]);

        if(count($resourceSchedules)) {

            $resources = $resourceService->fetchAll(
                $this->quoteInto('resource_id IN (?)', $resourceSchedules->getList('material_id')),
                'parent_id' //сначала получить корневые ресурсы, чтобы после копирования зависимого ресурса уже был известен новый id корня
            );


            if (count($resources)) {
                foreach ($resources as $resource) {
                    $newParentId = (int)0; // В Oracle "false" может восприниматься как NULL, что критично для NOT NULL полей.
                    if ($resource->parent_id && isset($resourcesLinks[$resource->parent_id])) {
                        $newParentId = $resourcesLinks[$resource->parent_id];
                    }

                    $newResource = $resourceService->copy($resource, $toSubjectId, $newParentId);
                    if ($newResource) {
                        $resourcesLinks[$resource->resource_id] = $newResource->resource_id;
                        $this->_subjectCopyCache[HM_Event_EventModel::TYPE_RESOURCE][$resource->resource_id] = $newResource->resource_id;
                    }
                }
            }
        }

        $links = $subjectResourceService->fetchAll($this->quoteInto('subject_id = ?', $fromSubjectId));

        if (count($links)) {
            foreach ($links as $link) {
                $link->subject_id = $toSubjectId;
                $subjectResourceService->insert($link->getValues());
            }
        }
    }

    public function copyTcTeachers($fromSubjectId, $toSubjectId)
    {
        $service = $this->getService('TcTeacherSubject');
        $subjectTeachers = $service->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        foreach ($subjectTeachers as $subjectTeacher) {
            $service->insert(array(
                'teacher_id'  => $subjectTeacher->teacher_id,
                'provider_id' => $subjectTeacher->provider_id,
                'subject_id'  => $toSubjectId,
            ));
        }
    }

    public function copyFiles($fromSubjectId, $toSubjectId)
    {
        $service = $this->getService('Files');

        $files = $service->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $fromSubjectId);
        foreach ($files as $file) {
            $service->addFile($file->getPath(), $file->getDisplayName(), HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $toSubjectId);
        }
    }

    public function copyTasks($fromSubjectId, $toSubjectId)
    {
        $tasksLinks = [];

        /** @var HM_Task_TaskService $taskService */
        $taskService = $this->getService('Task');

        /** @var HM_Subject_Task_TaskService $subjectTaskService */
        $subjectTaskService = $this->getService('SubjectTask');

        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');

        $taskService->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));
        $subjectTaskService->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));

        $taskSchedules = $lessonService->fetchAll([
            'typeID = ?' => HM_Event_EventModel::TYPE_TASK,
            'CID = ?' => $fromSubjectId
        ]);

        if(count($taskSchedules)) {
            $tasks = $taskService->fetchAll($this->quoteInto('subject_id = ?', $fromSubjectId));

            if (count($tasks)) {
                foreach ($tasks as $task) {
                    $newTask = $taskService->copy($task, $toSubjectId);
                    if ($newTask) {
                        $tasksLinks[$task->task_id] = $newTask->task_id;
                        $this->_subjectCopyCache[HM_Event_EventModel::TYPE_TASK][$task->task_id] = $newTask->task_id;
                    }
                }
            }
        }

        $links = $subjectTaskService->fetchAll($this->quoteInto('subject_id = ?', $fromSubjectId));

        if (count($links)) {
            foreach ($links as $link) {
                $link->subject_id = $toSubjectId;

                // А в copyResources этого нет. Как правильно?..
                if (isset($tasksLinks[$link->task_id])) {
                    $link->task_id = $tasksLinks[$link->task_id];
                }

                $subjectTaskService->insert($link->getValues());
            }
        }
    }

    public function copyTests($fromSubjectId, $toSubjectId)
    {
        /**
         * @var HM_Quest_QuestModel $quest
         * @var HM_Quest_QuestService $questService
         * @var HM_Subject_Quest_QuestService $subjectQuestService
         * @var HM_Lesson_LessonService $lessonService
         */
        $questService = $this->getService('Quest');
        $subjectQuestService = $this->getService('SubjectQuest');
        $lessonService = $this->getService('Lesson');

        $questService->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));
        $subjectQuestService->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));

        $testSchedules = $lessonService->fetchAll([
            'typeID IN (?)' => [HM_Event_EventModel::TYPE_TEST,HM_Event_EventModel::TYPE_POLL],
            'CID = ?' => $fromSubjectId
        ]);

        if (count($testSchedules)) {
            $quests = $questService->fetchAllDependence(
                'Settings',
                $this->quoteInto('quest_id IN (?)', $testSchedules->getList('material_id'))
            );

            if (count($quests)) {
                foreach ($quests as $quest) {
                    $newQuest = $subjectQuestService->copy($quest, $fromSubjectId, $toSubjectId);
                    if ($newQuest) {
                        $this->_subjectCopyCache[HM_Event_EventModel::TYPE_TEST][$quest->quest_id] = $newQuest->quest_id;
                    }
                }
            }
        }

        // А теперь тесты из материалов курса
        $quests = $questService->fetchAllDependence(
            'Settings',
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($quests)) {
            /** @var HM_Quest_QuestModel $quest */
            foreach ($quests as $quest) {
                $newQuest = $subjectQuestService->copy($quest, $fromSubjectId, $toSubjectId);
                if ($newQuest) {
                    // ? $quest->type - test / poll
                    $this->_subjectCopyCache[$quest->type][$quest->quest_id] = $newQuest->quest_id;
                }
            }
        }
    }

    public function copyWebinars($fromSubjectId, $toSubjectId)
    {
        $webinars = $this->getService('Webinar')->fetchAll($this->quoteInto('subject_id = ?', $fromSubjectId));
        foreach ($webinars as $oldWebinar) {

            $insert = $oldWebinar->getValues(null, array('webinar_id'));
            $insert['subject_id'] = $toSubjectId;
            $newWebinar = $this->getService('Webinar')->insert($insert);

            $oldWebinarFiles = $this->getService('WebinarFiles')->fetchAll($this->quoteInto('webinar_id = ?', $oldWebinar->webinar_id));
            $newWebinarFiles = array();
            foreach ($oldWebinarFiles as $oldWebinarFile) {
                $oldFile = $this->getService('Files')->getOne($this->getService('Files')->find($oldWebinarFile->file_id));
                $insert = $oldFile->getValues(null, array('file_id'));
                $newFile = $this->getService('Files')->insert($insert);

                $path = $oldFile->path;
                if (!$path || !file_exists($path)) {
                    $path = $oldFile->file_id . '.' . end(explode(".", $oldFile->name));
                }

                if (file_exists($path)) {
                    $update = $newFile->getValues();
                    $update['path'] = str_replace($oldFile->file_id, $newFile->file_id, $path);
                    if (copy($path, $update['path'])) {
                        $newFile = $this->getService('Files')->update($update);
                    }
                }

                $insert = $oldWebinarFile->getValues();
                $insert['webinar_id'] = $newWebinar->webinar_id;
                $insert['file_id'] = $newFile->file_id;
                $newWebinarFile = $this->getService('WebinarFiles')->insert($insert);
            }
        }
    }

    public function copyLessons($fromSubjectId, $toSubjectId)
    {
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');

        /** @var HM_Quest_Settings_SettingsService $questSettingsService */
        $questSettingsService = $this->getService('QuestSettings');

        // копируем только то, что можно скопировать (отн.даты и без ограничений); здесь же псевдо-занятия для своб.доступа
        $lessons = $lessonService->fetchAll(
            $this->quoteInto(
                array('CID = ?', ' AND timetype IN (?)'),
                array($fromSubjectId, new Zend_Db_Expr(implode(',', array(
                    HM_Lesson_LessonModel::TIMETYPE_FREE,
                    HM_Lesson_LessonModel::TIMETYPE_RELATIVE,
                ))))
            )
        );

        $lessonService->deleteBy($this->quoteInto('CID = ?', $toSubjectId));

        if (count($lessons)) {

            $lessonsLink = [];
            /** @var HM_Lesson_LessonModel $lesson */
            foreach ($lessons as $lesson) {

                $lessonID = $lesson->SHEID;
                unset($lesson->SHEID);

                $lesson->CID = $toSubjectId;
                $lesson->section_id = isset($this->_subjectCopyCache['sections'][$lesson->section_id]) ? $this->_subjectCopyCache['sections'][$lesson->section_id] : null;

                // привязываем занятие к новым сущностям
                $params = $lesson->getParams();
                $type = ($lesson->typeID >= 0) ? $lesson->typeID : $lesson->tool;
                if (isset($params['module_id']) && isset($this->_subjectCopyCache[$type][$params['module_id']])) {
                    $params['module_id'] = $this->_subjectCopyCache[$type][$params['module_id']];
                    $lesson->setParams($params);
                }

                // Аналогично params
                if (isset($lesson->material_id) && isset($this->_subjectCopyCache[$type][$lesson->material_id])) {
                    $lesson->material_id = $this->_subjectCopyCache[$type][$lesson->material_id];
                }

                $newLesson = $lessonService->insert($lesson->getValues());
                $lessonsLink[$lessonID] = $newLesson->SHEID;

                //копируем настройки для тестов, из области видимости оригинального занятия
                $lessonScope = HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON;

                if ($lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
                    $questId = $lesson->getModuleId();
                    $questSettingsService->copy(
                        $questId,
                        $lessonScope,
                        $lessonID,
                        $questId,
                        $lessonScope,
                        $newLesson->SHEID
                    );
                }
            }

            // обновление связей между новыми занятиями (например, условие выполнения)
            $newLessons = $lessonService->fetchAll(['CID = ?' => $toSubjectId]);
            if (count($newLessons) && count($lessonsLink)) {
                foreach ($newLessons as $newLesson) {
                    if ($newLesson->cond_sheid && isset($lessonsLink[$newLesson->cond_sheid])) {
                        $newLesson->cond_sheid = $lessonsLink[$newLesson->cond_sheid];
                        $lessonService->update($newLesson->getValues());
                    }
                }
            }
        }
    }

    public function copyElements($oldId, $newId)
    {
        $this->copySections($oldId, $newId);

        $this->_subjectCopyCache[HM_Event_EventModel::TYPE_COURSE] = $this->getService('Course')->copy($oldId, $newId);
        $this->copyClassifiers($oldId, $newId);
        //$this->copyExercises($subjectId, $newSubject->subid);

        // Похоже не участвует, опросы копируются в copyTests.
        // Проследить и удалить при необходимости
        //$this->copyQuizzes($oldId, $newId);
        $this->copyResources($oldId, $newId);
        $this->copyTasks($oldId, $newId);
        $this->copyTests($oldId, $newId);
        $this->copyLessons($oldId, $newId);
        $this->copyWebinars($oldId, $newId);
    }

    public function copy($subjectId)
    {
        if ($subjectId) {

            /** @var HM_Subject_SubjectModel $subject */
            $subject = $this->getOne($this->find($subjectId));

            if ($subject) {
                $subject->name = sprintf(_('%s (Копия)'), $subject->name);
                // #16795
                if ($subject->base != HM_Subject_SubjectModel::BASETYPE_SESSION) {
                    $subject->base = HM_Subject_SubjectModel::BASETYPE_PRACTICE;
                }
                $subject->external_id = '';
                unset($subject->subid);


                $values = $subject->getValues();

                if ($values['end'] != '') {
                    list($date, $time) = explode(' ', $values['end']);
                    $values['end'] = $date;
                }

                $newSubject = $this->insert($values);

                if ($values['default_uri'] != '') {
                    $values['default_uri'] = str_replace("subject_id/{$subjectId}", "subject_id/{$newSubject->subid}", $values['default_uri']);
                    $values['subid'] = $newSubject->subid;
                    $this->update($values);
                }

                $this->copyImage($subjectId, $newSubject->subid);

                if ($newSubject) {
                    $this->copyElements($subjectId, $newSubject->subid);
                }

                return $newSubject;
            }
        }

        return false;
    }

    public function getSessions($subjectId)
    {
        $sessions = array();
        if ($subject = $this->find($subjectId)->current()) {
            if ($subject->base == HM_Subject_SubjectModel::BASETYPE_BASE) {
                $sessions = $this->fetchAll(array('base_id = ?' => $subjectId));
            }
        }
        return $sessions;
    }

    /**
     * Возвращает список занятий на оценку пользователя по курсу
     * результат кешируется
     * @param $subjectID
     * @param $userID
     * @return mixed
     */
    public function getUserVedomostLessons($subjectID, $userID)
    {
        if (!isset($this->_userLessonsCache[$subjectID][$userID])) {
            $this->_userLessonsCache[$subjectID][$userID] = $this->getService('LessonAssign')
                ->fetchAllDependenceJoinInner(
                    'Lesson',
                    $this->getService('Lesson')->quoteInto(array('Lesson.CID  = ?', ' AND Lesson.vedomost = ?', ' AND self.MID = ?'), array($subjectID, 1, $userID))
                );
        }
        return $this->_userLessonsCache[$subjectID][$userID];
    }

    /**
     * Возвращает среднюю оценку прохождения пользователем занятий курса
     * @param HM_Subject_SubjectModel | int $subject - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return int
     */
    public function getUserMeanScore($subject, $userID)
    {
        $subjectID = ($subject instanceof HM_Model_Abstract) ? $subject->subid : (int) $subject;
        $lessons = $this->getUserVedomostLessons($subjectID, $userID);
        $amount = count($lessons);
        $total = 0;
        foreach ($lessons as $lesson) {
            if ($lesson->V_STATUS != -1) {
                $total += $lesson->V_STATUS;
            }
        }
        if ($amount) {
            $total = (ceil($total / $amount) <= 100) ? ceil($total / $amount) : 100;
        } else {
            $total = 0;
        }
        return $total;
    }

    public function subjectAccessibleForUser($subjectId, $userId = null): bool
    {
        $result = true;

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $userId = $userId ?: $this->getService('User')->getCurrentUserId();

        if (!$userId) return false;

        if ($aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

            $currentStudent = $this->getService('Student')->fetchAll(
                $this->getService('Student')->quoteInto(
                    ['MID = ?', ' AND CID = ?'],
                    [$userId, $subjectId]
                )
            );

            $currentGraduated = $this->getService('Graduated')->fetchAll(
                $this->getService('Graduated')->quoteInto(
                    ['MID = ?', ' AND CID = ?'],
                    [$userId, $subjectId]
                )
            );

            $subjectModel = $this->find($subjectId);

            $noAssigns = !count($currentStudent) && !count($currentGraduated);
            if ($noAssigns ||
                // Учился когда-то и курс без строгих ограничений
                (count($currentGraduated)
                    && (!count($subjectModel) ||
                        HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT !== $subjectModel->current()->period_restriction_type)
                )
            ) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Возвращает процент прохождения пользователем курса
     * @param HM_Subject_SubjectModel | int $subject - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return int
     */
    public function getUserProgress($subject, $userID)
    {
        $subjectID = ($subject instanceof HM_Model_Abstract) ? $subject->subid : (int) $subject;
        $scoreLessonsTotal = $this->getService('Lesson')
            ->countAllDependenceJoinInner(
                'Assign',
                $this->getService('Lesson')
                    ->quoteInto(array('CID = ? AND vedomost = 1 ', ' AND MID = ?', ' AND isfree = ?'), array($subjectID, $userID, HM_Lesson_LessonModel::MODE_PLAN))
            );

        $scoreLessonsScored = $this->getService('Lesson')
            ->countAllDependenceJoinInner(
                'Assign',
                $this->getService('Lesson')
                    ->quoteInto(array('CID = ? AND vedomost = 1 ', ' AND MID = ? AND V_STATUS > -1', ' AND isfree = ?'), array($subjectID, $userID, HM_Lesson_LessonModel::MODE_PLAN))
            );

        return ($scoreLessonsTotal) ? floor(($scoreLessonsScored / $scoreLessonsTotal) * 100) : 0;
    }

    /**
     * Функция возвращает TRUE в случае, если все занятия пользователя $userID по курсу $subject исмеют статус "выполнено"
     * @param HM_Subject_SubjectModel | int $subject - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return bool
     */
    public function isAllLessonsDone($subject, $userID)
    {
        $subjectID = ($subject instanceof HM_Model_Abstract) ? $subject->subid : (int) $subject;
        $lessons = $this->getUserVedomostLessons($subjectID, $userID);
        $finish = TRUE;
        foreach ($lessons as $lesson) {
            if ($lesson->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE) {
                $finish = FALSE;
            }
        }
        return $finish;
    }

    /**
     * Функция генерирует цвет в шестнадцатеричном представлении для нового курса
     * @return string
     */
    /* эта функция генерит цвета вырвиглаз, ниже хоть и рандом но мнее насыщенный
    public function generateColor()
    {
        if ($this->_subjectsColorsCache === null) {
            $this->_subjectsColorsCache = $this->fetchAll()->getList('subid','base_color');
        }
        $colorsUsed    = array_unique($this->_subjectsColorsCache);
        $subjectsCount =  count($colorsUsed) + 1;         // для случая если курс создается
        $stepCount     = ceil(pow($subjectsCount,1/3));   // количество шагов дробления
        $skipColors    = array('000000','ffffff');        // какие цвета исключить

        if ($stepCount > 255) { $stepCount = 255;}        // на всякий случай

        for($currStep = 1; $currStep <= $stepCount; $currStep++) {
            $step = (int) 255/$currStep;
            for($color_r = 0 ;$color_r <= 255; $color_r += $step) {
                for($color_g = 0 ;$color_g <= 255; $color_g += $step) {
                    for($color_b = 0 ;$color_b <= 255; $color_b += $step) {
                        $color = sprintf("%02x%02x%02x",$color_r,$color_g,$color_b);
                        if (!in_array($color, $colorsUsed) && !in_array($color,$skipColors)) {
                            return $color;
                        }
                    }
                }
            }
        }
    }*/

    public function generateColor()
    {
        $rand = array(0, 1, 2);
        shuffle($rand);
        $c[0] = rand(130, 200);
        $c[1] = rand(130, 200);
        $c[2] = 130;


        $color_r = $c[$rand[0]];
        $color_g = $c[$rand[1]];
        $color_b = $c[$rand[2]];
        return sprintf("%02x%02x%02x", $color_r, $color_g, $color_b);
    }

    public function getSubjectColor($subid)
    {
        if ($this->_subjectsColorsCache === null) {
            $this->_subjectsColorsCache = $this->fetchAll()->getList('subid', 'base_color');
        }
        if ($subid && array_key_exists($subid, $this->_subjectsColorsCache)) {
            return $this->_subjectsColorsCache[$subid];
        }

        return '';
    }

    public function getCalendarSource($source, $defaultColor = '0000ff', $inText = false, $forUsers = null)
    {
        if (!$source instanceof HM_Collection) return '';

        $curUserId = $this->getService('User')->getCurrentUserId();

        $isStudent = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $events        = array();

        $forUsers  = (array) $forUsers;

        $subIds    = $source->getList('subid');
        $rooms     = $this->getService('Room')->fetchAll()->getList('rid', 'name');
        $roomLinks = $this->getService('SubjectRoom')
            ->fetchAllJoinInner('Room', $this->quoteInto('cid in (?)', $subIds))->getList('cid', 'rid');

        foreach ($source as $event) {
            if (!$event || !$event->begin || !$event->end) continue;
            if (
                $isStudent &&
                !$this->isStudent($event->subid, $curUserId) &&
                in_array($curUserId, $forUsers)
            ) {
                continue;
            }

            $view = Zend_Registry::get('view');
            if ($isStudent) {
                $url = $this->isStudent($event->subid, $curUserId)
                    ? $this->getDefaultUri($event->subid)
                    : $view->url(array(
                        'module' => 'subject',
                        'controller' => 'list',
                        'action' => 'description',
                        'subject_id' => $event->subid
                    ), null, true);
            } else {
                $url = $view->url(array(
                    'module' => 'subject',
                    'controller' => 'index',
                    'action' => 'card',
                    'subject_id' => $event->subid
                ), null, true);
            }

            $start   = new HM_Date($event->begin);
            $end     = new HM_Date($event->end);
            $data = array(
                'id'    => $event->subid,
                'title' => $event->name,
                'start' => ($inText) ? $start->toString(HM_Date::SQL_DATE) : $start->getTimestamp(),
                'end'   => ($inText) ? $end->toString(HM_Date::SQL_DATE) : $end->getTimestamp(),
                'color' => "#{$event->base_color}",
                'textColor' => (lum($event->base_color) < 130) ? '#fff' : '#000',
                'url'   => $url
            );



            if (count($event->teachers)) {
                $teacher = $event->teachers->current();
                $data['title'] .=  '; ' . $teacher->getName();
            }
            $room = $roomLinks[$event->subid] ? $rooms[$roomLinks[$event->subid]] : false;
            if ($room) {
                $data['title'] .=  ' @ ' . $room;
            }

            if (count($forUsers) && (!$isStudent || !in_array($curUserId, $forUsers))) {
                $assigned = false;
                if ($event->teachers) {
                    foreach ($event->teachers as $teacher) {
                        if (in_array($teacher->MID, $forUsers)) {
                            $assigned = true;
                            break;
                        }
                    }
                }

                if (!$assigned) {
                    continue;
                }
            }

            $events[] = $data;
        }

        return $events;
    }

    // ВНИМАНИЕ! создает сессию с ручным стартом
    public function createSession($baseId, $appentTitle = false)
    {
        if (!$appentTitle) $appentTitle = _('сессия');
        if ($base = $this->getOne($this->find($baseId))) {
            if ($base->base != HM_Subject_SubjectModel::BASETYPE_SESSION) {

                if ($base->base == HM_Subject_SubjectModel::BASETYPE_PRACTICE) {
                    $changes = array(
                        'base'      => HM_Subject_SubjectModel::BASETYPE_BASE,
                        'period'    => HM_Subject_SubjectModel::PERIOD_FREE,
                        'claimant_process_id' => array_shift(HM_Subject_SubjectModel::getTrainingProcessIds()),
                    );
                    $this->getService('Subject')->updateWhere($changes, array('subid = ?' => $baseId));
                    $this->getService('Subject')->unlinkRooms($baseId);
                }

                $data = $base->getValues();
                $data['name'] = sprintf(_('%s (%s)'), $base->name, $appentTitle);
                $data['base'] = HM_Subject_SubjectModel::BASETYPE_SESSION;
                $data['begin'] = date('Y-m-d');
                $data['end'] = date('Y-m-d') . ' 23:59:59';
                $data['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
                $data['period_restriction_type'] = HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL;
                $data['base_id'] = $baseId;
                unset($data['subid']);
                $session = $this->insert($data);

                try {
                    $this->getService('Subject')->copyElements($baseId, $session->subid);
                } catch (HM_Exception $e) {
                    // что-то не скопировалось..(
                }
                return $session;
            }
        }
        return false;
    }


    public function setDefaultUri($uri, $subjectId)
    {
        $this->updateWhere(array('default_uri' => urldecode($uri)), array('subid = ?' => $subjectId));
    }

    public function getDefaultUri($subjectId, $force = false)
    {
        $subject = $this->find($subjectId)->current();
        if ($subject && !empty($subject->default_uri) &&($force || $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)))
        {

            // dirty hack
            $uri = str_replace(array(
                'lesson/list/index',
            ), array(
                'lesson/list/my',
            ), $subject->default_uri);

            if ($subjectId) //#17522
                $uri = preg_replace("/(.*?)\/(subject_id)\/(\d+)(\/(.*?))*/", "\\1/\\2/{$subjectId}\\4", $uri);

            return $uri;
        } else {
            $view = Zend_Registry::get('view');
            return $view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subjectId));
        }
    }

    public function getViewUrl($subjectId)
    {
        return array(
            'module' => 'subject',
            'controller' => 'index',
            'action' => 'description',
            'subject_id' => $subjectId,
        );
    }

    public function hasOnlyFreeLessons($subjectId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(array(
            'CID = ?' => $subjectId,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN
        ));
        return !count($lessons);
    }

    public function getIcal(HM_Subject_SubjectModel $subject, $userId = 0)
    {
        // create and set icalendar object
        $calendar = new HM_Ical_Calendar();
        $calendar->addTimezone(HM_Ical_Timezone::fromTimezoneId(Zend_Registry::get('config')->timezone->default));
        $calendar->properties()->add(new HM_Ical_Property('METHOD', HM_Ical_Property_Value_Text::fromString('REQUEST')));

        $event = new HM_Ical_Event();
        $event->properties()->add(new HM_Ical_Property('UID', HM_Ical_Property_Value_Text::fromString(md5('subject_' . $subject->subid . time()))));
        $event->properties()->add(new HM_Ical_Property('SUMMARY', HM_Ical_Property_Value_Text::fromString($subject->name)));
        $event->properties()->add(new HM_Ical_Property('ORGANIZER', HM_Ical_Property_Value_Text::fromString('MAILTO:' . $this->getService('Option')->getOption('dekanEMail'))));

        //$event->properties()->add(new HM_Ical_Property('LOCATION', HM_Ical_Property_Value_Text::fromString('')));
        //$event->properties()->add(new HM_Ical_Property('SEQUENCE', HM_Ical_Property_Value_Text::fromString('0')));
        //$event->properties()->add(new HM_Ical_Property('TRANSP', HM_Ical_Property_Value_Text::fromString('OPAQUE')));
        //$event->properties()->add(new HM_Ical_Property('CLASS', HM_Ical_Property_Value_Text::fromString('PUBLIC')));

        if ($subject->begin) {
            $start = new HM_Date($subject->begin);
        } elseif ($subject->begin) {
            $start = new HM_Date($subject->begin);
        }

        if ($subject->end) {
            $end = new HM_Date($subject->end);
        } elseif ($subject->end) {
            $end = new HM_Date($subject->end);
        }

        if ($userId) {
            $student = $this->getOne(
                $this->getService('Student')->fetchAll(
                    $this->quoteInto(
                        array('MID = ?', ' AND CID = ?'),
                        array($userId, $subject->subid)
                    )
                )
            );

            if ($student) {
                if ($student->time_registered) {
                    $start = new HM_Date($student->time_registered);
                }

                if ($student->end_personal) {
                    $end = new HM_Date($student->end_personal);
                }
            }
        }

        $start->setHour(0)->setMinute(0)->setSecond(0);
        $end->setHour(23)->setMinute(59)->setSecond(59);

        $event->properties()->add(new HM_Ical_Property('DTSTART', HM_Ical_Property_Value_DateTime::fromString($start->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('DTEND', HM_Ical_Property_Value_DateTime::fromString($end->toString('YYYYMMddTHHmmss'))));

        $now = new HM_Date();
        $event->properties()->add(new HM_Ical_Property('DTSTAMP', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('CREATED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('LAST-MODIFIED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $description = $subject->name;

        $collection = $this->getTeachers($subject->subid);

        $teachers = array();
        if (count($collection)) {
            foreach ($collection as $item) {
                $teachers[$item->MID] = $item->getName();
            }
        }

        if (count($teachers)) {
            $description .= ', ' . sprintf(_('Тьюторы: %s'), join(', ', $teachers));
        }

        $event->properties()->add(new HM_Ical_Property('DESCRIPTION', HM_Ical_Property_Value_Text::fromString($description)));

        $calendar->addEvent($event);
        return $calendar;
    }

    public function getTeachers($subjectId)
    {
        return $this->getService('User')->fetchAllJoinInner('Teacher', $this->quoteInto('Teacher.CID = ?', $subjectId));
    }

    public function onCriterionDelete($criterion_id, $type)
    {
        $where = $this->quoteInto(
            array(
                'criterion_id = ?',
                ' AND criterion_type = ?',
            ),
            array(
                $criterion_id,
                $type
            )
        );

        $data = array('status' => HM_Tc_Subject_SubjectModel::FULLTIME_STATUS_NOT_PUBLISHED);
        $this->updateWhere($data, $where);
    }

    public function getById($id)
    {
        $cacheName = 'getById';

        if ($this->_cache[$cacheName][$id]) {
            $output = $this->_cache[$cacheName][$id];
        } else {

            $output = $this->getOne($this->fetchAll($this->quoteInto('subid = ?', $id)));
            $this->_cache[$cacheName][$id] = $output;
        }

        return $output;
    }

    public function getSubjectsWithCompetences($criterion_ids, $type)
    {
        if (!is_array($criterion_ids)) {
            $criterion_ids = array($criterion_ids);
        }

        $select = $this->getSelect();

        $where = $this->quoteInto(
            array(
                's.criterion_id IN (?)',
                ' AND s.criterion_type = ?',
            ),
            array(
                $criterion_ids,
                $type
            )
        );

        $select->from(array('s' => 'subjects'), array('subject' => 's.name'));
        if ($type == HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION) {
            $select->joinLeft(
                array('atc' => 'at_criteria'),
                's.criterion_id = atc.criterion_id',
                array('competence' => 'atc.name')
            );
        } else if ($type == HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST) {
            $select->joinLeft(
                array('atct' => 'at_criteria_test'),
                's.criterion_id = atct.criterion_id',
                array('competence' => 'atct.name')
            );
        }
        $select->where($where);

        Zend_Registry::get('log_system')->debug(var_export($select->__toString(), true));

        $stmt = $select->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function cleanUpCacheForInfoblocks($subjectId)
    {
        $inSliderSubjects  = $this->getService('Subject')->fetchAll(array('in_slider = ?' => 1))->getList('subid');
        $inBannerSubjects  = $this->getService('Subject')->fetchAll(array('in_banner = ?' => 1))->getList('subid');
        $availableSubjects = $this->getService('Subject')->fetchAll(array(
            $this->getService('Subject')->quoteInto(
                array(
                    'reg_type = ? AND (',
                    'period IN (?) OR  ',
                    'period_restriction_type = ? OR ',
                    '(period_restriction_type = ?', ' AND (state = ? ', ' OR state = ? OR state is null) ) OR ',
                    '(period = ? AND ',
                    'end > ?))'
                ),
                array(
                    HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN,
                    array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                    HM_Subject_SubjectModel::STATE_ACTUAL,
                    HM_Subject_SubjectModel::STATE_PENDING,
                    HM_Subject_SubjectModel::PERIOD_DATES,
                    $this->getService('Subject')->getDateTime()
                )
            )
        ))->getList('subid');

        if (
            in_array($subjectId, $inSliderSubjects) ||
            in_array($subjectId, $inBannerSubjects) ||
            in_array($subjectId, $availableSubjects)
        )
            $this->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

        $this->cleanUpCache('HM_View_Infoblock_SubjectsClassifiers', Zend_Cache::CLEANING_MODE_ALL);
    }

    public function search($query, $classifiers = [], $statuses = [], $extraWhere = [], $order = null)
    {
        $subjectSelect = $this->getSelect()
            ->from(['s' => 'subjects'], [
                'id' => 'distinct(s.subid)',
                'orderDateTime' => 's.created',
                'orderRating' => 's.rating',
                'orderTitle' => 's.name',
            ])
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $this->quoteInto('tr.item_id = s.subid and tr.item_type = ?', HM_Tag_Ref_RefModel::TYPE_SUBJECT),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                []
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                'cll.item_id = s.subid AND cll.type = 0',
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                []
            )
            ->group([
                's.subid',
                's.created',
                's.rating',
                's.name',
            ]);

        $subjectSelect->where('s.status != ?', HM_Subject_SubjectModel::STATE_CLOSED);

        $keyWithNoClassifiers = array_search(HM_Classifier_ClassifierModel::NO_CLASSIFIER_ID, $classifiers);
        if (false !== $keyWithNoClassifiers) {
            unset($classifiers[$keyWithNoClassifiers]);
        }

        if (false !== $keyWithNoClassifiers and count($classifiers)) {
            $subjectSelect->where("(cll.classifier_id IS NULL or cll.classifier_id in(?))", $classifiers);
        } elseif (false !== $keyWithNoClassifiers) {
            $subjectSelect->where("cll.classifier_id IS NULL");
        } elseif (count($classifiers)) {
            $subjectSelect->where("cll.classifier_id in(?)", $classifiers);
        }

        if (count($statuses)) {
            $subjectSelect->where("status in(?)", $statuses);
        }

        if (!empty($order)) {
            $subjectSelect->order($order);
        }

        if ($query) {
            $subjectSelect->where($this->quoteInto([
                's.name LIKE ? or ',
                's.description LIKE ? or ',
                't.body = ? or ',
                'cl.name LIKE ? ',
            ], [
                '%' . $query . '%',
                '%' . $query . '%',
                $query,
                '%' . $query . '%',
            ]));
        }

        if($extraWhere) {
            foreach ($extraWhere as $key => $value)
                $subjectSelect->where($key, $value);
        }

        return $subjectSelect->query()->fetchAll();
    }


    public function getCollectionForSearch($subjectsIds)
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();

        $sum = 'SUM(claim.status)';

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $sum = 'SUM(CAST(claim.status AS INT))';
        }

        $subjectsSelect = $this->getSelect()
            ->from(
                ['s' => 'subjects'],
                [
                    's.subid',
                    's.external_id',
                    's.code',
                    's.name',
                    's.shortname',
                    's.supplier_id',
                    's.short_description',
                    //'Description' => new Zend_Db_Expr('CAST(s.description as VARCHAR(MAX))'),
                    's.type',
                    's.reg_type',
                    's.begin_planned',
                    's.end_planned',
                    's.begin',
                    's.end',
                    's.longtime',
                    's.price',
                    's.price_currency',
                    's.plan_users',
                    's.services',
                    's.period',
                    's.period_restriction_type',
                    's.created',
                    's.last_updated',
                    's.access_mode',
                    's.access_elements',
                    's.mode_free_limit',
                    's.auto_done',
                    's.base',
                    's.base_id',
                    's.base_color',
                    's.claimant_process_id',
                    's.state',
                    's.default_uri',
                    's.scale_id',
                    's.auto_mark',
                    's.auto_graduate',
                    's.formula_id',
                    's.threshold',
                    's.in_slider',
                    's.in_banner',
                    's.create_from_tc_session',
                    's.provider_id',
                    's.status',
                    's.format',
                    's.criterion_id',
                    's.criterion_type',
                    's.created_by',
                    's.category',
                    's.city',
                    's.primary_type',
                    's.provider_type',
                    's.mark_required',
                    's.check_form',
                    's.after_training',
                    's.feedback',
                    's.education_type',
                    's.rating',
                    's.direction_id',
                    's.banner_url',
                    's.is_labor_safety'
                ]
            )
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $this->quoteInto(
                    'tr.item_id = s.subid and tr.item_type = ?',
                    HM_Tag_Ref_RefModel::TYPE_SUBJECT
                ),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                ['tag' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t.body)')]
            )
            ->joinLeft(
                ['st' => 'Students'],
                'st.CID = s.subid and st.MID = ' . $currentUserId,
                ['isStudent' => new Zend_Db_Expr('CASE WHEN GROUP_CONCAT(st.SID) <> \'\' THEN 1 ELSE 0 END')]
            )
            ->joinLeft(
                ['claim' => 'claimants'],
                'claim.CID = s.subid and claim.MID = ' . $currentUserId,
                ['isClaimant' => new Zend_Db_Expr("CASE WHEN ((GROUP_CONCAT(claim.SID) <> '') AND (" . $sum . " = 0)) THEN 1 ELSE 0 END")]
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $this->quoteInto(
                    'cll.item_id = s.subid and cll.type = ?',
                    HM_Classifier_Link_LinkModel::TYPE_SUBJECT
                ),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                ['classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(cl.classifier_id, '#'),cl.name))")]
            )
            ->group([
                's.subid',
                's.external_id',
                's.code',
                's.name',
                's.shortname',
                's.supplier_id',
                's.short_description',
                //new Zend_Db_Expr('CAST(s.description as VARCHAR(MAX))'),
                's.type',
                's.reg_type',
                's.begin_planned',
                's.end_planned',
                's.begin',
                's.end',
                's.longtime',
                's.price',
                's.price_currency',
                's.plan_users',
                's.services',
                's.period',
                's.period_restriction_type',
                's.created',
                's.last_updated',
                's.access_mode',
                's.access_elements',
                's.mode_free_limit',
                's.auto_done',
                's.base',
                's.base_id',
                's.base_color',
                's.claimant_process_id',
                's.state',
                's.default_uri',
                's.scale_id',
                's.auto_mark',
                's.auto_graduate',
                's.formula_id',
                's.threshold',
                's.in_slider',
                's.in_banner',
                's.create_from_tc_session',
                's.provider_id',
                's.status',
                's.format',
                's.criterion_id',
                's.criterion_type',
                's.created_by',
                's.category',
                's.city',
                's.primary_type',
                's.provider_type',
                's.mark_required',
                's.check_form',
                's.after_training',
                's.feedback',
                's.education_type',
                's.rating',
                's.direction_id',
                's.banner_url',
                's.is_labor_safety'
            ])
            ->where('s.subid in (?)', $subjectsIds)
            ->where('s.reg_type <> ?', 2);

        $subjects = $subjectsSelect->query()->fetchAll();

        $subjectsCollection = (new HM_Collection($subjects, 'HM_Subject_SubjectModel'))->asArrayOfObjects();
        foreach ($subjectsCollection as $subjectItem) {
            $subjectItem->tag = array_filter(explode(',', $subjectItem->tag));
            $classifiersItems = array_filter(explode(',', $subjectItem->classifiers));
            $resultClassifiers = [];
            foreach ($classifiersItems as $classifiersItem) {
                $classifiersItemSplit = array_filter(explode('#', $classifiersItem));
                if (!count($classifiersItemSplit)) continue;
                $classifiersItemId = $classifiersItemSplit[0];
                $classifiersItemName = $classifiersItemSplit[1];

                $resultClassifiers[$classifiersItemId] = $classifiersItemName;
            }
            $subjectItem->classifiers = $resultClassifiers;
            $subjectItem->icon = $subjectItem->getUserIcon();
            $subjectItem->new = $subjectItem->isNew();
        }

        return $subjectsCollection;
    }

    public function getContextSwitcherData($currentSubject)
    {
        $switcherData = [];
        $siblingSubjects = $this->getService('User')->getSubjects();
        $isEndUser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        foreach ($siblingSubjects as $subject) {

            if ($currentSubject->subid == $subject->subid) continue;
            if (!$isEndUser && ($currentSubject->type != $subject->type)) continue;

            $date = ''; // не используется
            $name = $subject->name ?: '';
            $url = Zend_Registry::get('view')->url(array('subject_id' => $subject->subid));
            if (
                ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) &&
                $isEndUser
            ) {
                $name = sprintf('%s %s-%s', $name, HM_Controller_Action::formatDate($subject->begin), HM_Controller_Action::formatDate($subject->end));
            }

            $switcherData[$subject->subid] = compact('name', 'date', 'url');
        }

        return $switcherData;
    }

    public function getRecentActions($subjectId)
    {
        $assignedUsers = $this->getService('User')->fetchAllJoinInner('Student', $this->quoteInto([
            'Student.CID = ?',
            new Zend_Db_Expr(' and Student.time_registered >= (CURDATE() - INTERVAL 3 DAY)')
        ], [$subjectId]));

        foreach ($assignedUsers as $student) {
            $student->image = $student->getRealPhoto() ?: $student->getDefaultPhoto();
        }

        $news = $this->getService('News')->getRecentNews($subjectId);

        foreach ($news as $newsItem) {
            $newsItem->viewUrl = Zend_Registry::get('view')->url([
                'module' => 'news',
                'controller' => 'index',
                'action' => 'view',
                'subject' => 'subject',
                'subject_id' => $subjectId,
                'id' => $newsItem->id,
            ]);
        }

        $result = [
            'assignedUsers' => $assignedUsers,
            'news' => $news,
        ];

        return $result;
    }

    public function setLastUpdated($subjectId)
    {
        if ($subject = $this->findOne($subjectId)) {
            $this->updateWhere(
                ['last_updated' => date('Y-m-d H:i:s')],
                ['subid = ?' => $subjectId]
            );
        }
    }

    /**
     * Начальное наполнение marksheet/index/index
     */
    public function getMarkSheetItems($subjectId, $filters = []): HM_DataType_Marksheet_Data {
        $result = $this->getMarksheetData($subjectId, $filters);

        $view = Zend_Registry::get('view');
        $result->urls = [
            'setScore' => $view->url(['module' => 'marksheet', 'controller' => 'index', 'action' => 'set-score']),
            'graduateStudent' => $view->url(['module' => 'marksheet', 'controller' => 'index', 'action' => 'graduate-students']),
            'setTotalScore' => $view->url(['module' => 'marksheet', 'controller' => 'index', 'action' => 'set-total-score']),
            'print' => $view->serverUrl($view->url([
                'module' => 'marksheet',
                'controller' => 'index',
                'action' => 'print',
                'subject_id' => $subjectId
            ])),
            'excel' => $view->serverUrl($view->url([
                'module' => 'marksheet',
                'controller' => 'index',
                'action' => 'excel',
                'subject_id' => $subjectId,
            ])),
            'word' => $view->serverUrl($view->url([
                'module' => 'marksheet',
                'controller' => 'index',
                'action' => 'word',
                'subject_id' => $subjectId,
            ])),
            'setComment' => $view->url(['module' => 'marksheet', 'controller' => 'index', 'action' => 'set-comment']),
        ];

        return $result;
    }

    /**
     * Получение данных по запросу marksheet/index/get-marksheet-data
     */
    public function getMarksheetData($subjectId = null, $filters = []): HM_DataType_Marksheet_Data
    {
        $fromDate = $filters['fromDate'];
        $toDate = $filters['toDate'];
        $currentPersonId = $filters['currentPersonId'];
        $currentGroup = $filters['currentGroup'];

        $subject = $this->getService('Subject')->findOne($subjectId);
        $students = $this->getMarksheetStudents($subjectId, $filters);

        $perPage = $this->getService('Option')->getOption('grid_rows_per_page');
        $perPage = $perPage > 0 ? $perPage : Bvb_Grid::ROWS_PER_PAGE;

        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 0);
        $paginator = Zend_Paginator::factory($students);
        $paginator->setCurrentPageNumber((int)$page);
        $paginator->setItemCountPerPage($perPage);
        $students = iterator_to_array($paginator->getCurrentItems());
        $usersIds = array_keys($students);

        $lessons = $this->getMarkSheetSchedules($subjectId, $fromDate, $toDate);
        $lessonsIds = array_keys($lessons);

        $lessonsTotal = $this->getMarkSheetLessonsTotal($subjectId);
        $subjectsTotal = $this->getMarkSheetSubjectsTotal($usersIds, $subjectId); // Итого

        $persons = $this->getMarkSheetLessonPersons($students, $lessonsIds);
        $personsRows = [];
        foreach ($persons as $person) {
            $personsRows[] = [
                'personId' => $person['id'],
                'selectedUser' => false,
                'selectedUserCheckbox' => false,
                'cardUrl' => $person['cardUrl'],
                'lessonsUrl' => $person['lessonsUrl'],
                'name' => $person['name'],
                'studyGroups' => $person['studyGroups'],
                'lessonsTotal' => $lessonsTotal[$person['id']],
                'subjectsTotal' => $subjectsTotal[$person['id']]
            ];
        }

        $groups = $this->getService('StudyGroupCourse')->getCourseGroups($subjectId);
        $subGroups = $this->getService('Group')->fetchAll(['cid = ?' => $subjectId]);

        $groupsList = [];

        if (count($groups)) {
            foreach ($groups as $studygroup) {
                $groupsList['groups']['group_' . $studygroup->group_id] = $studygroup->name;
            }
        }

        if (count($subGroups)) {
            foreach ($subGroups as $item) {
                $groupsList['subgroups']['subgroup_' . $item->gid] = $item->name;
            }
        }

        $personsList = array_map([$this, 'personsFilterList'], $persons);
        if ($currentPersonId) {
            $currentPerson = array_filter($personsList, function ($item) use ($currentPersonId) {
                return $item->id === (int) $currentPersonId;
            });
        } else {
            $currentPerson = '';
        }

        $result = new HM_DataType_Marksheet_Data();

        $result->subject = $subject;
        $result->groups = $groupsList;
        $result->currentGroup = $currentGroup;
        $result->currentPerson = $currentPerson;

        $result->page = $page;
        $result->pages = $paginator->count();
        $result->lessons = $lessons;
        $result->personsRows = $personsRows;

        return $result;
    }

    public function getMarkSheetExportScore($subjectId): HM_DataType_Marksheet_ExportScore {

        // Убрал из параметров метода
        $forGraduated = false;
        $group = null;
        $subGroup = null;
        $fromDate = '';
        $toDate = '';

        $result = new HM_DataType_Marksheet_ExportScore();
        if (!$subjectId) return $result;

        $studentsDependence = $forGraduated ? 'Graduated' : 'Student';

        $where = [$studentsDependence . '.CID = ?' => $subjectId];

        $groupUsers = $this->_getGroupUsersByFilter($subjectId, $group, $subGroup);
        if (count($groupUsers)) {
            $where[$studentsDependence . '.MID IN (?)'] = $groupUsers;
        }

        $order = 'LastName';
        $students = $this->getService('User')
            ->fetchAllDependenceJoinInner($studentsDependence, $where, $order)->asArrayOfObjects();

        $result->lessons = $this->getMarkSheetSchedules($subjectId, $fromDate, $toDate);
        // $result->lessonsOrder = $this->getLessonsOrder($result->lessons);
        $lessonsIds = array_keys($result->lessons);
        $result->persons = $this->getMarkSheetLessonPersons($students, $lessonsIds);
        $result->lessonsTotal = $this->getMarkSheetLessonsTotal($subjectId);
        $usersIds = array_keys($students);
        $result->subjectsTotal = $this->getMarkSheetSubjectsTotal($usersIds, $subjectId);

        return $result;
    }

    private function getMarkSheetSchedules($subjectId, $fromDate, $toDate)
    {
        $result = $lessonsCache = $eventsCache =[];
        $lessonsCollection = $this->getMarkSheetLessons($subjectId);

        // $events = $this->getMarkSheetEvents($lessonsCollection->getList('typeID'));

        /** @var HM_Lesson_LessonModel $lesson custom? */
        foreach ($lessonsCollection as $lesson) {
            $assigns = $lesson->getAssigns();
            if ($assigns) {
                $inPeriod = false;

                foreach ($assigns as $assign) {
                    if ($assign->MID > 0) {
                        $hasStudent = !empty($students[$assign->MID]);

                        if ($fromDate && $toDate && !$inPeriod) {

                            switch ($lesson->timetype) {
                                case HM_Lesson_LessonModel::TIMETYPE_FREE:
                                    $inPeriod = true;
                                    break;
                                case HM_Lesson_LessonModel::TIMETYPE_DATES:
                                case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                                    if(!$lessonsCache[$lesson->SHEID]) {
                                        $lessonsCache[$lesson->SHEID] = $this->isDatesInPeriod($fromDate, $toDate, $lesson->begin, $lesson->end);
                                    }
                                    $inPeriod = $lessonsCache[$lesson->SHEID];
                                    break;
                                case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                                    if ($hasStudent) {
                                        $inPeriod = $this->isDatesInPeriod($fromDate, $toDate, $assign->begin_personal, $assign->end_personal);
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
                if ($inPeriod || !$fromDate || !$toDate) {

                    /*
                     * Есть проблемы с производительностью в -$lesson->typeID при строковом typeID
                     * Пока нет возможности создания кастомных занятий - убираем этот кусок
                     *
                    if (strlen($lesson->typeID) && isset($events[-$lesson->typeID])) {
                        $lesson->setEvent($events[-$lesson->typeID]);
                    } */

                    $result[] = [
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

    private function getMarkSheetLessonPersons($students, $lessonsIds)
    {
        // сортировка юзеров по ФИО
        $result = [];

        if (count($students)) {

            $assignedStudents = [];
            $assignsFilter = ['MID IN (?)' => array_keys($students)];

            if (count($lessonsIds)) {
                $assignsFilter['SHEID in (?)'] = $lessonsIds;
            }

            $lessonAssigns = $this->getService('LessonAssign')->fetchAll($assignsFilter);
            foreach ($lessonAssigns as $assign) {
                $assignedStudents[$assign->MID][] = $assign;
            }

            $view = Zend_Registry::get('view');

            /** @var HM_StudyGroup_Users_UsersService $studyGroupUsersService */
            $studyGroupUsersService = $this->getService('StudyGroupUsers');

            /** @var HM_User_UserModel $student */
            foreach ($students as $student) {
                // if (!in_array($student->MID, $groupUsers) && ($group || $subGroup)) continue;
                $studyGroups = $studyGroupUsersService->getUserGroups($student->MID);
                $result[] = [
                    'id' => $student->MID,
                    'name' => $student->getName(),
                    'studyGroups' => $studyGroups,
                    'cardUrl' => $view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $student->MID]),
                    'lessonsUrl' => $view->url(['module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'user_id' => $student->MID]),
                ];
            }
        }
        @uasort($result, [$this, 'userCompare']);

        return $result;
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
        foreach ($lessonsCollection as $lesson) {
            $assigns = $lesson->getAssigns();
            if ($assigns) {
                foreach ($assigns as $assign) {
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

    private function getMarkSheetSubjectsTotal($userIds, $subjectId)
    {
        $result = [];

        foreach ($userIds as $userId) {
            $subjects = $this->getOne($this->getService('Subject')->fetchAllHybrid('Mark', 'User', 'Student', ['subid = ?' => $subjectId]));
            if ($subjects && $subjects->marks) {
                $studentSubjectMark = $subjects->marks->exists('mid', $userId);
                if ($studentSubjectMark) {
                    $result[$userId]['mark'] = $studentSubjectMark->mark;
                    $result[$userId]['comment'] = $studentSubjectMark->comments;
                    $result[$userId]['certificate'] = $studentSubjectMark->certificate_validity_period;
                    continue;
                }
            }

            // если итоговая оценка не выставлена
            $result[$userId]['mark'] = HM_Scale_Value_ValueModel::VALUE_NA;
            $result[$userId]['comment'] = '';
            $result[$userId]['certificate'] = HM_Scale_Value_ValueModel::VALUE_NA;
        }

        return $result;
    }

    private function personsFilterList($person)
    {
        $result = new stdClass();
        $result->id = $person['id'];
        $result->name = $person['name'];

        return $result;
    }

    public function getBannerSubjects()
    {
        return $this->fetchAll(['in_banner = ?' => 1]);
    }

    /**
     * @param $subjectId
     * @param $group
     * @param $subGroup
     */
    private function _getGroupUsersByFilter($subjectId, $group, $subGroup): array
    {
        $groupUsers = [];

        /** @var HM_StudyGroup_Users_UsersService $studyGroupUsersService */
        $studyGroupUsersService = $this->getService('StudyGroupUsers');

        if ($group) {
            $users = $studyGroupUsersService->getUsersOnCourse($group, $subjectId);
            foreach ($users as $user) {
                $groupUsers[] = $user['user_id'];
            }
        } elseif ($subGroup) {
            $users = $this->getService('GroupAssign')->fetchAll(['gid = ?' => $subGroup, 'cid = ?' => $subjectId]);
            $groupUsers = $users->getList('mid');
        }

        return $groupUsers;
    }

    /**
     * @param $subjectId
     * @param string $studentsDependence
     * @param $currentPersonId
     * @param $searchQuery
     * @param HM_Responsibility_ResponsibilityService $responsibilityService
     * @param $group
     * @param $subGroup
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    private function getMarksheetStudents($subjectId, $filters = []): array
    {
        /** @var HM_Responsibility_ResponsibilityService $responsibilityService */
        $responsibilityService = $this->getService('Responsibility');

        $group = $filters['group'];
        $subGroup = $filters['subGroup'];
        $searchQuery = $filters['searchQuery'];
        $currentPersonId = $filters['currentPersonId'] ;

        $forGraduated = $filters['forGraduated'];
        $studentsDependence = $forGraduated ? 'Graduated' : 'Student';

        /** @var Zend_Db_Select $select */
        $select = $this->getSelect();
        $tableName = $this->getService($studentsDependence)->getMapper()->getAdapter()->getTableName();

        $select->from(['t1' => $tableName], ['MID'])
            ->joinInner(['subjects'], 't1.CID' . ' = subjects.subid', [])
            ->where('CID = ?', $subjectId);

        if ($currentPersonId) {
            $select->where('t1.MID = ?', $currentPersonId);
        }

        if ($searchQuery) {
            $select->joinInner(['p' => $this->getService('User')->getTableName()], 'p.MID = t1.MID', '');
            $select->where(new HM_Db_Expr('LOWER(' . new HM_Db_Expr('p.fio') . ') LIKE LOWER(?)'), sprintf('%%%s%%', $searchQuery));
        }

        // Область ответственности
        $select = $responsibilityService->checkUsers($select, '', 't1.MID');

        $statement = $select->query();
        $responsibilityStudents = $statement->rowCount() ? array_filter(array_unique(array_column($statement->fetchAll(), 'MID'))) : [];

        $where = [$studentsDependence . '.CID = ?' => $subjectId];

        if (count($responsibilityStudents)) {
            $where['self.MID IN (?)'] = $responsibilityStudents;
        } else {
            $where['0 = ?'] = 1;
        }

        $groupUsers = $this->_getGroupUsersByFilter($subjectId, $group, $subGroup);
        if (count($groupUsers)) {
            $where[$studentsDependence . '.MID IN (?)'] = $groupUsers;
        }

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        $order = 'LastName';
        $students = $userService
            ->fetchAllDependenceJoinInner($studentsDependence, $where, $order)->asArrayOfObjects();

        return $students;
    }

    public function getDataFromRest($data)
    {
        $begin = new HM_Date($data['date_begin']);
        $end = new HM_Date($data['date_end']);

        $convertedData = [
            'external_id' => $data['externalId'],
            'name' => $data['title'],
            'description' => $data['description'],
            // 'image_url' => '', // Как принять картинку, по URL ?
            'reg_type' => array_search($data['assignment_type'], HM_Subject_SubjectModel::getRestAssignmentTypes()),
            // 'application_type' => array_search($data['application_type'], HM_Subject_SubjectModel::getRestApplicationTypes()),
            'period' => array_search($data['date_type'], HM_Subject_SubjectModel::getRestDateTypes()),
            'begin' => $begin->get(HM_Date::SQL),
            'end' => $end->get(HM_Date::SQL),
            'longtime' => $data['duration'],
        ];

        if($data['id']) {
            $convertedData['subid'] = $data['id'];
        }

        return $convertedData;
    }

    /**
     * @param $subjectId
     * @param $newSubjectId
     * @throws Zend_Exception
     */
    public function copyImage($subjectId, $newSubjectId): void
    {
        $srcSubjIcon = HM_Subject_SubjectModel::getIconFolder($subjectId) . '/' . $subjectId . '.jpg';

        if (file_exists($srcSubjIcon)) {
            $destSubjFolder = HM_Subject_SubjectModel::getIconFolder($newSubjectId);
            if (!file_exists($destSubjFolder)) {
                mkdir($destSubjFolder);
            }
            copy($srcSubjIcon, $destSubjFolder . '/' . $newSubjectId . '.jpg');
        }
    }
}

function html2rgb($color)
{
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array(
            $color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]
        );
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array(
            $color[0] . $color[0],
            $color[1] . $color[1],
            $color[2] . $color[2]
        );
    } else {
        $r = $g = $b = '00';
    }

    return array(hexdec($r), hexdec($g), hexdec($b));
}

function lum($color)
{
    list($r, $g, $b) = html2rgb($color);

    return sqrt(0.241 * pow($r, 2) + 0.691 * pow($g, 2) + 0.068 * pow($b, 2));
}
