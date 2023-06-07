<?php
class HM_Feedback_Users_UsersService extends HM_Service_Abstract
{
    /*
     *  Назначаем одного (при этом не удаляем остальных)
     */
    public function assignUser($userId, $feedbackId)
    {
        /** @var HM_Orgstructure_OrgstructureService $orgstructureService */
        $orgstructureService = $this->getService('Orgstructure');
        /** @var HM_Feedback_FeedbackService $feedbackService */
        $feedbackService = $this->getService('Feedback');

        $feedback = $feedbackService->getOne($feedbackService->findDependence('Subject', $feedbackId));
        if (!$feedback) return false;

        $currentUserIds = $this->fetchAll(array(
            'feedback_id = ?' => $feedbackId,
        ))->getList('user_id');

        if (in_array($userId, $currentUserIds)) return false;

        $data = array(
            'feedback_id'    => $feedbackId,
            'user_id'        => $userId,
            'subordinate_id' => 0,
        );

        if ($feedback->respondent_type == HM_Feedback_FeedbackModel::RESPONDENT_TYPE_MANAGER) {

            $subordinate = $orgstructureService->getOne($orgstructureService->fetchAllDependence('User', array('mid = ?' => $userId)));
            if ($subordinate) {
                $manager = $orgstructureService->getManager($subordinate->soid);

                $data['subordinate_id'] = $userId;
                $data['user_id'] = $manager->mid;

            } else {
                return false;
            }
        }

        $feedbackUser = $this->insert($data);

        $this->cleanUpCache('HM_View_Infoblock_FeedbackBlock', $data['user_id']);

        if ($feedback->assign_type == HM_Feedback_FeedbackModel::ASSIGN_NOW) {
            $feedbackUser->feedback = $feedback;
            $this->notify($feedbackUser);
        }
    }

    /*
     * Назначаем нескольких из UIMultiSelect
     * соответственно, тех кто не в списке - отписываем
     */
    public function assignUsers($userIds, $feedbackId)
    {
        /** @var HM_Orgstructure_OrgstructureService $orgstructureService */
        $orgstructureService = $this->getService('Orgstructure');
        /** @var HM_Feedback_FeedbackService $feedbackService */
        $feedbackService = $this->getService('Feedback');

        $feedback = $feedbackService->getOne($feedbackService->findDependence('Subject', $feedbackId));
        if (!$feedback) return false;

        if (!is_array($userIds)) $userIds = array($userIds);

        $this->deleteBy(array(
            'feedback_id IN (?)' => $feedbackId,
            'user_id NOT IN (?)' => $userIds
        ));

        $currentUserIds = $this->fetchAll(array(
            'feedback_id = ?' => $feedbackId,
        ))->getList('user_id');

        $feedbackUsers = $assignedUserIds = array();
        foreach ($userIds as $userId) {

            // повторно не назначаем
            if (in_array($userId, $currentUserIds) || in_array($userId, $assignedUserIds)) {
                continue;
            }

            $data = array(
                'feedback_id'    => $feedbackId,
                'user_id'        => $userId,
                'subordinate_id' => 0,
            );

            if ($feedback->respondent_type == HM_Feedback_FeedbackModel::RESPONDENT_TYPE_MANAGER) {

                $subordinate = $orgstructureService->getOne($orgstructureService->fetchAllDependence('User', array('mid = ?' => $userId)));
                if ($subordinate) {
                    $manager = $orgstructureService->getManager($subordinate->soid);

                    if ($manager && $manager->mid) {
                        $data['subordinate_id'] = $userId;
                        $data['user_id'] = $manager->mid;
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            $assignedUserIds[] = $userId;
            $feedbackUsers[] = $this->insert($data);
        }

        if ($feedback->assign_type == HM_Feedback_FeedbackModel::ASSIGN_NOW) {
            foreach ($feedbackUsers as $feedbackUser) {
                $this->notify($feedbackUser);
            }
        }
    }

    public function notify($feedbackUser)
    {
        /** @var HM_Feedback_FeedbackService $feedbackService */
        $feedbackService = $this->getService('Feedback');
        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Messenger $messenger */
        $messenger = $this->getService('Messenger');

        $feedback = $feedbackService->getOne($feedbackService->findDependence('Subject', $feedbackUser->feedback_id));
        if ($feedback) {
            $url =  Zend_Registry::get('view')->serverUrl(
                Zend_Registry::get('view')->url(array(
                    'module'      => 'quest',
                    'controller'  => 'feedback',
                    'action'      => 'start',
                    'quest_id'    => $feedback->quest_id,
                    'feedback_id' => $feedback->feedback_id,
                    "ordergrid{$feedback->subject_id}" => null,
                    "fiogrid{$feedback->subject_id}" => null,
                    'courseId' => null,
                    'gridmod' => null,
                    'all' => null,
                    'baseUrl' => '',
                ))
            );

            $subjectTitle = '';
            if (!count($feedback->subject)) {
                $subject = $subjectService->getOne($subjectService->find($feedback->subject_id));
            } else {
                $subject = $feedback->subject->current();
            }
            if ($subject) {
                $subjectTitle = $subject->name;
            }

            $templateVars = array(
                'subject_id' => $feedback->subjectId,
                'poll' => sprintf("<a href='%s'>%s</a>", $url, $feedback->name),
                'course' => $subjectTitle,
            );

            $template = HM_Messenger::TEMPLATE_POLL_STUDENTS;

            if ($feedback->respondent_type == HM_Feedback_FeedbackModel::RESPONDENT_TYPE_MANAGER) {

                $template = HM_Messenger::TEMPLATE_POLL_LEADERS;

                $subordinate = $userService->getOne($userService->find($feedbackUser->subordinate_id));
                if ($subordinate) {
                    $templateVars['user_fio'] = $subordinate->getName();
                } else {
                    return false;
                }
            }

            $messenger->setTemplate($template);
            $messenger->assign($templateVars);
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $feedbackUser->user_id);

//[ES!!!] //array('feedback' => $feedback))
        }
    }

}