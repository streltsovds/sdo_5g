<?php
class HM_Feedback_FeedbackService extends HM_Service_Abstract
{
    public function isDeletable($questId)
    {
        if (in_array($questId, HM_Feedback_FeedbackModel::getHardcodeDeleteIds())) {
            return false;
        }
        return true;
    }

    public function isEditable($questId)
    {
        if (in_array($questId, HM_Feedback_FeedbackModel::getHardcodeEditIds())) {
            return false;
        }
        return true;
    }



    public function getUserFeedback($userId)
    {
        $select = $this->getSelect();

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_STUDENT, HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            $ifStudent = 'AND g.CID IS NOT NULL AND g.MID IS NOT NULL';
            $endField = 'g.end';
        } else {
            $ifStudent = '';
            $endField = 's.end';
        }

        $select->distinct()
            ->from(array('sfu' => 'feedback_users'), array(
                'sfu.feedback_user_id',
                'sf.feedback_id',
                'name' => 'sf.name',
                'subid' => 's.subid',
                'subject' => 's.name',
                'quest_id' => 'sf.quest_id',
                'poll' => 'q.name',
                'end' => $endField,
                'sf.assign_type',
                'sf.assign_days',
                'sf.respondent_type',
                'p_mid' => 'p.mid',
                's_name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            ))
            ->joinInner(array('sf' => 'feedback'), 'sf.feedback_id = sfu.feedback_id', array())
            ->joinInner(array('q' => 'questionnaires'), 'sf.quest_id = q.quest_id ', array())
            ->joinLeft(array('s' => 'subjects'),       'sf.subject_id = s.subid', array())
            ->joinLeft(array('p' => 'People'), 'sfu.subordinate_id = p.MID', array())
            ->joinLeft(array('g' => 'graduated'),      'sf.subject_id = g.CID AND sfu.user_id = g.MID', array())
            ->joinLeft(array('qa' => 'quest_attempts'), 'qa.quest_id = sf.quest_id AND qa.context_event_id = sfu.feedback_user_id AND qa.context_type = ' . HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK . ' AND qa.user_id = sfu.user_id' , array())
            ->where('sfu.user_id = ?', (int) $userId)
            ->where('qa.status != ?  OR qa.status IS NULL', (int) HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED)
            ->where('sf.status in (?)', array(HM_Feedback_FeedbackModel::STATUS_ASSIGNED, HM_Feedback_FeedbackModel::STATUS_INPROGRESS))
            ->where($this->quoteInto(
            array(
                'q.type = ?',
                ' AND q.status = ?',
//                    ' AND sf.respondent_type = ?',
                ' AND (sf.assign_type = ?',
                ' OR ((sf.assign_type = ?', ' OR sf.assign_type = ?) '. $ifStudent . ' ))',

            ),
            array(
                HM_Quest_QuestModel::TYPE_POLL,
                HM_Quest_QuestModel::STATUS_RESTRICTED,
//                    HM_Subject_Feedback_FeedbackModel::RESPONDENT_TYPE_USER,
                HM_Feedback_FeedbackModel::ASSIGN_NOW,
                HM_Feedback_FeedbackModel::ASSIGN_AFTER_COMPLETE,
                HM_Feedback_FeedbackModel::ASSIGN_AFTER_DAYS,
            )
        ))
//            ->order(array('g.end DESC'))
;
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            // Под ролью супервайзера показываем только опросы для прочиненных
            $select->where('sfu.subordinate_id != ?', 0);
        } else {
            // Под ролью юзера показываем только свои опросы
            $select->where('sfu.subordinate_id = ?', 0);
        }

        $rows = $select->query()->fetchAll();

        $feedback = array();
        foreach ($rows as $row) {
            if (!isset($feedback[$row['subid']])) {
                $feedback[$row['subid']] = array(
                    'name'      => $row['subject']?$row['subject']:_('Обратная связь'),
                    'feedbacks' => array()
                );
            }

            if (is_null($row['end']) && ($row['assign_type'] != HM_Feedback_FeedbackModel::ASSIGN_NOW)) {
                continue;
            } else {
                if ($row['assign_type'] == HM_Feedback_FeedbackModel::ASSIGN_AFTER_COMPLETE) {
                    if ((strtotime($row['end'])) > time()) {
                        continue;
                    }
                }

                if ($row['assign_type'] == HM_Feedback_FeedbackModel::ASSIGN_AFTER_DAYS) {
                    if ((time() - strtotime($row['end'])) < $row['assign_days'] * 24 * 60 * 60) {
                        continue;
                    }
                }
            }


            $feedback[$row['subid']]['feedbacks'][] = $row;
        }


        foreach ($feedback as $k => $oneFeedback) {
            if (!count($oneFeedback['feedbacks'])) {
                unset($feedback[$k]);
            }
        }

        return $feedback;
    }

    // DEPRECATED ??
    // есть HM_Feedback_Users_UsersService::assignUser
    public function assignFeedback($studentId, $subjectId)
    {
        $feedbackService = $this->getService('Feedback');

        $exist = $feedbackService->fetchAll($this->quoteInto(
            array('subject_id=?', ' AND user_id=?'),
            array($subjectId, $studentId)
        ))->getList('quest_id');

        $select = $this->getService('Quest')->getSelect();
        $select->from(
            array('q' => 'questionnaires'),
            array('q.quest_id', 'q.name'))
            ->joinInner(array('sq' => 'subjects_quests'),'q.quest_id = sq.quest_id', array())
            ->where('sq.subject_id=?', $subjectId)
            ->where('q.status=?', HM_Quest_QuestModel::STATUS_RESTRICTED)
            ->where('q.type=?', HM_Quest_QuestModel::TYPE_POLL);

        $polls = $select->query()->fetchAll();

        $messenger = $this->getService('Messenger');
        $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_STUDENTS);

        foreach ($polls as $poll) {
            if (empty($exist[$poll['quest_id']])) {
                $feedback = $feedbackService->insert(
                    array(
                        'subject_id' => $subjectId,
                        'user_id'    => $studentId,
                        'quest_id'   => $poll['quest_id'],
                        'status'     => HM_Feedback_FeedbackModel::STATUS_ASSIGNED
                    )
                );

                $url2 =  Zend_Registry::get('view')->serverUrl(
                    Zend_Registry::get('view')->url(array(
                        'module'      => 'quest',
                        'controller'  => 'feedback',
                        'action'      => 'start',
                        'quest_id'    => $poll['quest_id'],
                        'feedback_id' => $feedback->feedback_id
                    )));
                $messenger->assign(
                    array(
                        'subject_id' => $subjectId,
                        'title'      => $poll['name'],
                        'url2'       => '<a href="'.$url2.'">'.$url2.'</a>',
                        'poll'       => '<a href="'.$url2.'">'.$url2.'</a>',
                    ));
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentId);
//[ES!!!] //'feedback' => $feedback
            }
        }
    }

    public function getRelatedUserList($id) {
        $feedback = $this->find($id)->current();
        $result   = $feedback ? array($feedback->user_id) : array();
        return $result;
    }

    public function assingNewPoll($subjectId, $questId) {
        $subjectQuestService = $this->getService('SubjectQuest');
        $subjectQuest = $subjectQuestService->fetchAll($subjectQuestService->quoteInto(
            array('subject_id = ?', ' AND quest_id = ?'),
            array($subjectId, $questId)
        ))->current();
        if(!$subjectQuest->subject_id && !$subjectQuest->quest_id){
            $subjectQuestService->insert(array(
                'subject_id' => $subjectId,
                'quest_id'   => $questId,
            ));
        }
    }
    
    public function onStudentAssign($userId, $subjectId)
    {
        $feedbacks = $this->fetchAll($this->quoteInto(
            array('subject_id = ?', ' AND assign_new = ?'),
            array($subjectId, 1)
        ));
        
        $feedbackUsersService = $this->getService('FeedbackUsers');

        foreach($feedbacks as $feedback) {
            $feedbackUsersService->assignUser($userId, $feedback->feedback_id);
        }
    }

    public function onStudentGraduate($userId, $subjectId)
    {
        $feedbackUsersService = $this->getService('FeedbackUsers');
        $feedbacksUsers = $feedbackUsersService->fetchAllDependenceJoinInner('Feedback', $this->quoteInto(
            array(
                'Feedback.subject_id = ?',
                ' AND Feedback.assign_type = ?',
                ' AND (
                    (Feedback.respondent_type = ?',
                        ' AND self.user_id = ?)',
                    ' OR (Feedback.respondent_type = ?',
                        ' AND self.subordinate_id = ?))'),
            array(
                $subjectId,
                HM_Feedback_FeedbackModel::ASSIGN_AFTER_COMPLETE,
                HM_Feedback_FeedbackModel::RESPONDENT_TYPE_USER,
                $userId,
                HM_Feedback_FeedbackModel::RESPONDENT_TYPE_MANAGER,
                $userId
            )
        ));

        foreach ($feedbacksUsers as $feedbackUser) {
            $feedbackUsersService->notify($feedbackUser);
        }
    }

    public function getFeedbackResultsForSubject($subjectId)
    {
        $reviewsSelect = $this->getSelect()
            ->from(
                ['fb' => 'feedback'],
                [
                    'user_id' => 'qa.user_id',
                    'date' => 'qa.date_end',
                    'review' => 'qqr.free_variant',
                ]
            )
            ->joinInner(['fbu' => 'feedback_users'], 'fbu.feedback_id=fb.feedback_id', [])
            ->joinInner(['p' => 'People'], 'p.MID=fbu.user_id', [])
            ->joinInner(
                ['qa' => 'quest_attempts'],
                $this->quoteInto(
                    ['qa.context_event_id=fbu.feedback_user_id and qa.context_type=?'],
                    [HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK]
                ),
                []
            )
            ->joinInner(['qqr' => 'quest_question_results'], "qqr.attempt_id=qa.attempt_id and qqr.free_variant <> '' and qqr.free_variant IS NOT NULL and qqr.show_feedback=1", [])
            ->joinInner(
                ['qq' => 'quest_questions'],
                $this->quoteInto(
                    ['qqr.question_id=qq.question_id and qq.type = ?'],
                    [HM_Quest_Question_QuestionModel::TYPE_FREE]
                ),
                []
            )
            ->where('fb.subject_id = ?', $subjectId);
        ;

        return $reviewsSelect->query()->fetchAll();
    }
}