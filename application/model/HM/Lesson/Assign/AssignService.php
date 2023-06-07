<?php

class HM_Lesson_Assign_AssignService extends HM_Service_Abstract
{

    protected $userLessonScoreSet = false;

    public function setUserScore($userId, $lessonId, $score, $courseId = 0, $automatic = false)
    {
        try {
            // не работает из unmanaged
            $currentUserId = $this->getService('User')->getCurrentUserId();
        } catch (Zend_Session_Exception $e) {
            $currentUserId = $GLOBALS['s']['mid'];
        }

        /** @var HM_Subject_Mark_MarkService $subjectMarkService */
        $subjectMarkService = $this->getService('SubjectMark');

        if (!in_array($lessonId, array('total', 'certificate'))) {

            // обычная оценка за занятие
            if ($score === '') {
                $score = -1;
            }

            $collection = $this->fetchAll(array('MID = ?' => $userId, 'SHEID = ?' => $lessonId));
            if (count($collection)) {
                $lessonAssign = $collection->current();
                $oldScore = (int)$lessonAssign->V_STATUS;
                $lessonAssign->V_STATUS = (int)$score;
                $this->updateUserLessonScore($lessonAssign->getValues(), $oldScore);

                $this->getService('LessonAssignMarkHistory')->insert(array(
                        'MID' => $currentUserId,
                        'SSID' => $lessonAssign->SSID,
                        'mark' => intval($score),
                        'updated' => $this->getService('User')->getDateTime())
                );

                $lesson = $this->getService('Lesson')->fetchRow(array('SHEID = ?' => $lessonAssign->SHEID));

                // только если это автоматическое выставление оценки за зантяие - пересчитываем оценку за курс
                if ($automatic) {
                    $subjectMarkService->onLessonScoreChanged($lesson->CID, $currentUserId);
                }

                $this->cleanUpCache('HM_View_Infoblock_ScheduleDailyBlock', $lessonAssign->MID);

                $score = HM_Scale_Value_ValueModel::getTextStatus($lesson->getScale(), $score);

                /** @var HM_Messenger $messenger */
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(HM_Messenger::TEMPLATE_LESSON_MARK, ['mark' => $score, 'lesson_id' => $lesson->SHEID]);
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
            }
        } else {

            // итоговая оценка или срок жизни сертификата
            $subjectMark = $this->getOne($subjectMarkService->fetchAll(array('mid = ?' => $userId, 'cid = ?' => $courseId)));

            if (!$subjectMark) {
                $subjectMark = $subjectMarkService->insert(array(
                    'mid' => $userId,
                    'cid' => $courseId,
                    'certificate_validity_period' => -1
                ));
            }

            if ($lessonId == 'certificate') {

                $subjectMark->certificate_validity_period = $score;

            } elseif ($lessonId == "total") {

                $subjectMark->mark = $score;
                $subjectMark->confirmed = HM_Subject_Mark_MarkModel::MARK_CONFIRMED;

            }

            $subjectMarkService->update($subjectMark->getData());

            $subject = $this->getService('Subject')->find($courseId)->current();

            $score = HM_Scale_Value_ValueModel::getTextStatus($subject->scale_id, $score);

            /** @var HM_Messenger $messenger */
            $messenger = $this->getService('Messenger');
            $messenger->setOptions(HM_Messenger::TEMPLATE_SUBJECT_MARK, ['mark' => $score, 'subject_id' => $subject->subid]);
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
        }
    }

    public function updateUserLessonScore($values, $oldScore) {

        if ($oldScore != $values['V_STATUS']) {
            $result = $this->update($values);
        }

        return $result;
    }

    public function setUserComments($userId, $scheduleId, $comment, $courseId = 0){
        if ($scheduleId != 'total')
            $res = $this->updateWhere(array('comments' => $comment),array('MID = ?' => $userId, 'SHEID = ?' => $scheduleId));
        else {
            $one = $this->getOne($this->getService('SubjectMark')->fetchAll(array('mid = ?' => $userId, 'cid = ?' => $courseId)));
            if ($one) {
                $one->comments = $comment;
                $this->getService('SubjectMark')->update($one->getValues());
            }
        }
        /*
        $one = $this->fetchAll(array('MID = ?' => $userId, 'SHEID = ?' => $scheduleId));
        if(count($one) > 0){
            $this->update(array('SSID' => $one[0]->SSID, 'comments' => $comment));
      }*/
    }

    public function insert($data, $unsetNull = true)
    {
        $data['V_STATUS'] = (isset($data['V_STATUS']) &&
            $mark = HM_Subject_Mark_MarkModel::filterMark($data['V_STATUS'])) ?
            $mark : -1;
        $data['created'] = $data['updated'] = $this->getDateTime();

        return parent::insert($data);
    }

    public function update($data, $unsetNull = true)
    {
        if(isset($data['V_STATUS'])) {
            $data['V_STATUS'] = HM_Subject_Mark_MarkModel::filterMark($data['V_STATUS']);
        }
        $data['updated'] = $this->getDateTime();

        return parent::update($data);
    }

    public function onLessonStart($lesson)
    {
        if ($this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $userId = $this->getService('User')->getCurrentUserId();

            if (
                ($lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) &&
//                $lesson->getFormulaId() &&
                $lesson->vedomost
            ) {
                $score = false;
                // сюда добавлять логику обработки onStart для других типов занятий и других шкал
                switch ($lesson->getType()) {
                    case HM_Event_EventModel::TYPE_RESOURCE:
                        if ($lesson->getScale() == HM_Scale_ScaleModel::TYPE_BINARY) {
                            $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                        }
                        break;
                }

                if ($score !== false) {
                    $this->setUserScore($userId, $lesson->SHEID, $score, 0, true);
                }

            } elseif ($lesson->isfree == HM_Lesson_LessonModel::MODE_FREE) {
                // это для страницы "статистика изучения свободных материалов"
                $this->updateWhere(
                    array(
                        'V_DONE' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS,
                        'launched' => date('Y-m-d H:i:s'),
                    ),
                    array(
                        'SHEID = ?' => $lesson->SHEID,
                        'MID = ?' => $userId,
                        'V_DONE = ?' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART,
                    )
                );
            }
        }
    }

    public function onLessonFinish($lesson, $result)
    {
        try {
            // не работает из unmanaged
            $roleCondition = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
            $userId = $this->getService('User')->getCurrentUserId();

        } catch (Zend_Session_Exception $e) {
            $roleCondition = true;
            $userId = $GLOBALS['s']['mid'];
        }
        if ($roleCondition) {

            if (
                ($lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) &&
                $lesson->vedomost
            ) {
                $score = false;
                // сюда добавлять логику обработки onFinish для других типов занятий и других шкал
                // @todo: рефакторить эти вложенные switch'и

                switch ($lesson->getType()) {
                    case HM_Event_EventModel::TYPE_COURSE:
                    case HM_Event_EventModel::TYPE_LECTURE:
//                        if ($lesson->getFormulaId()) { // не понятно при чём тут формула; при генерации занятий formula_id в params вообще отсутствует
                        switch ($lesson->getScale()) {
                            case HM_Scale_ScaleModel::TYPE_BINARY:
                                if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                    $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                                }
                                break;
                            case HM_Scale_ScaleModel::TYPE_TERNARY:
                                if (isset($result['status'])) {
                                    if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                                    } elseif ($result['status'] == HM_Scorm_Track_Data_DataModel::STATUS_FAILED) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                                    }
                                }
                                break;
                            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                                if (isset($result['score']) && isset($result['status'])) {
                                    if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                        $score = $result['score'];
                                    } else {
                                        // сбрасываем при повторном прохождении
                                        $score = HM_Scale_Value_ValueModel::VALUE_NA;
                                    }
                                }
                                break;
                        }
//                        }
                        break;
                    case HM_Event_EventModel::TYPE_POLL:
                        $scale = $lesson->getScale();
                    $questAttempts = $this->getService('QuestAttempt')->fetchAll(array(
                            'context_event_id = ?' => $lesson->SHEID,
                            'user_id = ?' => $userId
                    ));
                        foreach ($questAttempts as $questAttempt) {
                            if ($questAttempt->status == HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED) {
                                $this->getService('LessonAssign')->updateWhere(
                                array('V_STATUS' => 1),
                                array(
                                        'SHEID = ?' => $lesson->SHEID,
                                        'MID = ?' => $userId
                                )
                                );
                            }
                        }
                        break;
                    case HM_Event_EventModel::TYPE_TEST:
                        switch ($lesson->getScale()) {
                            case HM_Scale_ScaleModel::TYPE_BINARY:
                                if (isset($result['score'])) {
                                    if (empty($lesson->threshold) || ($result['score'] >= $lesson->threshold)) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                                    }
                                }
                                break;
                            case HM_Scale_ScaleModel::TYPE_TERNARY:
                                if (isset($result['score'])) {
                                    if (empty($lesson->threshold) || ($result['score'] >= $lesson->threshold)) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                                    } else {
                                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                                    }
                                }
                                break;
                            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                                if (isset($result['score'])) {
                                    if ($formulaId = $lesson->getFormulaId()) {
                                        $formula = $this->getService('Formula')->getById($formulaId);
                                        if ($formula) {
                                            $score = $formula->getResultValue($result['score']);
                                        }
                                    } else {
                                        $score = $result['score'];
                                    }
                                }
                                break;
                        }

                        //привязка к подгруппе по результатам теста
                        if (isset($result['score']) && ($formulaGroupId = $lesson->getFormulaGroupId())) {
                            $formula = $this->getService('Formula')->getById($formulaGroupId);
                            if ($formula) {
                                $groupName = $formula->getResultValue($result['score']);

                                if (!$group = $this->getOne($this->getService('Group')->fetchAll($this->quoteInto(
                                array('name like ?', ' AND cid=?'),
                                array($groupName, $lesson->CID))))) {
                                $group = $this->getService('Group')->insert(array(
                                        'cid' => $lesson->CID,
                                        'name' => $groupName
                                ));
                                }

                                $this->getService('Group')->assignStudent($group, $userId);
                            }
                        }

                        break;
                }

                if ($score !== false) {
                    $this->setUserScore($userId, $lesson->SHEID, $score, 0, true);
//                    Было сделано для фиксации оценки за текущую попытку
//                    if (isset($result['attemptId'])) {
//                        $attemptId = $result['attemptId'];
//                        $this->getService('QuestAttempt')->update(array('attempt_id' => $attemptId, 'V_STATUS' => $score));
//                    }
                }

            } elseif ($lesson->isfree == HM_Lesson_LessonModel::MODE_FREE) {

                // это для страницы "статистика изучения свободных материалов"
                if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                    $this->updateWhere(array(
                        'V_DONE' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE,
                    ),
                    array(
                            'SHEID = ?' => $lesson->SHEID,
                            'MID = ?' => $userId,
                            'V_DONE = ?' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS
                    ));
                }
            }
        }
    }

    public function getRelatedUserList($id)
    {
        $result = array();
        if ($this->userLessonScoreSet) {
            $assign = $this->find($id)->current();
            $result[] = intval($assign->MID);
        } else {
            $listeners = $this->getService('LessonAssign')->fetchAll('SHEID = ' . $id . ' AND MID > 0');
            if ($listeners->count() > 0) {
                foreach ($listeners as $shid) {
                    $result[] = intval($shid->MID);
                }
            }
        }
        return $result;
    }

    /**
     * @param int $subjectId
     * @param int $userId
     * @param false $forceManualUpdate
     * @param false $rethrowHmException
     *
     * @return int - mark
     * @throws HM_Exception
     */
    public function onLessonScoreChanged($subjectId, $userId, $forceManualUpdate = false, $rethrowHmException = false){
        /** @var HM_Subject_Mark_MarkService $subjectMarkService */
        $subjectMarkService = $this->getService('SubjectMark');

        return $subjectMarkService->onLessonScoreChanged($subjectId, $userId, $forceManualUpdate, $rethrowHmException);
    }

    function isInNotificationPeriod($lesson, $userId)
    {
        if (is_object($lesson)) {
            $lesson = $lesson->getValues();
        }

        if (!$lesson['notify_before']) return false;
        $currentDate = new HM_Date();
        $lessonAssign = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array('SHEID = ?'  => $lesson['SHEID'], 'MID = ?' => $userId)));
        if ($lessonAssign->end_personal) {
            $lessonEnd = new HM_Date($lessonAssign->end_personal);
            $notifyStart = HM_Date::getRelativeDate(clone $lessonEnd, -$lesson['notify_before']);
            return ($currentDate->get('Y-MM-dd') <= $lessonEnd->get('Y-MM-dd')) && ($currentDate->get('Y-MM-dd') >= $notifyStart->get('Y-MM-dd')) ? $lessonEnd : false;
        }
        return false;
    }
}
