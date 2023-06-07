<?php
class HM_Controller_Action_Helper_QuestContextFeedbackUser extends Zend_Controller_Action_Helper_Abstract
{
    protected $_event;
    
    public function direct($event)
    {
        $this->_event = $event;
        return $this;
    }

    
    public function finalize($questAttempt)
    {
        $feedbackService = Zend_Registry::get('serviceContainer')->getService('Feedback');
        $feedbackUserService = Zend_Registry::get('serviceContainer')->getService('FeedbackUsers');
        $questAttemptService = Zend_Registry::get('serviceContainer')->getService('QuestAttempt');
        $subjectService = Zend_Registry::get('serviceContainer')->getService('Subject');

        if (
            $questAttempt->type == HM_Quest_QuestModel::TYPE_POLL
            && $questAttempt->context_type == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK
        ) {

            $feedbackUser = $feedbackUserService->getOne($feedbackUserService->fetchAllDependence('Feedback', array('feedback_user_id = ?' => $questAttempt->context_event_id)));

            $subjectId = $feedbackUser->feedback->current()->subject_id;
            $subject = $subjectService->getOne($subjectService->fetchAll(array('subid = ?' => $subjectId)));

            if ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION
                && $subject->base_id != 0
            ) {
                $subjects = $subjectService->fetchAll(array('base_id = ?' => $subject->base_id));
                $subjectIds = $subjects->getList('subid');

                $subjectIds = array_unique($subjectIds);
                $allSubjectFeedbacks = $feedbackService->fetchAllDependence('Quest', array('subject_id IN (?)' =>  $subjectIds));
                $subjectFeedbackKeys = array();
                foreach ($allSubjectFeedbacks as $subjectFeedback) {
                    if ($subjectFeedback->quests->current()->scale_id != 0) {
                        $subjectFeedbackKeys[] = $subjectFeedback->feedback_id;
                    }
                }




                $allSubjectFeedbackUsersList = array();
                if ( count($subjectFeedbackKeys) ) {
                    $allSubjectFeedbackUsers = $feedbackUserService->fetchAll(array('feedback_id IN (?)' =>  $subjectFeedbackKeys));
                    $allSubjectFeedbackUsersList = $allSubjectFeedbackUsers->getList('feedback_user_id');
                }


                if (count($allSubjectFeedbackUsersList)) {
                    $allSubjectsAttempts  = $questAttemptService->fetchAll(
                        array(
                            'context_type = ?' => $questAttempt->context_type,
                            'context_event_id IN (?)' => $allSubjectFeedbackUsersList,
                            'type = ?' => HM_Quest_QuestModel::TYPE_POLL,
                            'is_resultative = ?' => 1

                        )
                    );

                    $allSubjectAttemptsList = $allSubjectsAttempts->getList('attempt_id', 'score_weighted');

                    if (count($allSubjectAttemptsList)) {
                        $scoreSessionWeighted = array_sum($allSubjectAttemptsList) / count($allSubjectAttemptsList);

                        $subjectService->update(
                            array(
                                'subid' => $subject->base_id,
                                'rating' => $scoreSessionWeighted
                            )
                        );
                    }
                }

//                $allSubjectFeedbackUsers = $feedbackUserService->fetchAll(array('feedback_id IN (?)' =>  $subjectFeedbackKeys));
//
//                $allSubjectsAttempts  = $questAttemptService->fetchAll(
//                    array(
//                        'context_type = ?' => $questAttempt->context_type,
//                        'context_event_id IN (?)' => $allSubjectFeedbackUsers->getList('feedback_user_id'),
//                        'type = ?' => HM_Quest_QuestModel::TYPE_POLL,
//                        'is_resultative = ?' => 1
//
//                    )
//                );
//
//                $allSubjectAttemptsList = $allSubjectsAttempts->getList('attempt_id', 'score_weighted');
//                $scoreSessionWeighted = array_sum($allSubjectAttemptsList) / count($allSubjectAttemptsList);
//
//                $subjectService->update(
//                    array(
//                        'subid' => $subject->base_id,
//                        'rating' => $scoreSessionWeighted
//                    )
//                );
            }


            $allSessionFeedbacks = $feedbackService->fetchAll(array('subject_id IN (?)' =>  $subjectId));
            $allSessionFeedbackUsers = $feedbackUserService->fetchAll(array('feedback_id IN (?)' =>  $allSessionFeedbacks->getList('feedback_id')));
            $allSessionAttempts  = $questAttemptService->fetchAll(
                array(
                    'context_type = ?' => $questAttempt->context_type,
                    'context_event_id IN (?)' => $allSessionFeedbackUsers->getList('feedback_user_id'),
                    'type = ?' => HM_Quest_QuestModel::TYPE_POLL,
                    'is_resultative = ?' => 1

                )
            );

            $allSessionAttemptsList = $allSessionAttempts->getList('attempt_id', 'score_weighted');
            $scoreSubjectWeighted = array_sum($allSessionAttemptsList) / count($allSessionAttemptsList);

            $subjectService->update(
                array(
                    'subid' => $subjectId,
                    'rating' => $scoreSubjectWeighted
                )
            );
        }
    }
}