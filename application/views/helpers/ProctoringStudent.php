<?php
class HM_View_Helper_ProctoringStudent extends HM_View_Helper_Abstract
{
    public function proctoringStudent($lessonId, $autoScreenShare = true)
    {
        if(empty($lessonId)) return;
        $serviceContainer = Zend_Registry::get('serviceContainer');
        /** @var HM_User_UserService $userService */
        $userService = $serviceContainer->getService('User');
        $currentRole = $userService->getCurrentUserRole();
        $isStudent = $serviceContainer->getService('Acl')->inheritsRole($currentRole, [HM_Role_Abstract_RoleModel::ROLE_STUDENT, HM_Role_Abstract_RoleModel::ROLE_ENDUSER]);
        $lesson = $this->getService('Lesson')->find($lessonId)->current();
        if ($isStudent && $lesson->has_proctoring) {
            $currentUserId = $userService->getCurrentUserId();

//            $select = $userService->getSelect()
//                ->from(
//                    array('scid' => 'scheduleID'),
//                    array('scid.remote_event_id')
//                )
//                ->joinInner(array('sc' => 'schedule'), 'scid.SHEID=sc.SHEID and sc.has_proctoring=1', array())
//                ->where('scid.SHEID = ?', $lessonId)
//                ->where('scid.MID = ?', $currentUserId);
////                ->where('scid.passed_proctoring = 0');
//            $eventId = $select->query()->fetchColumn(0);

//            $lessonService = $serviceContainer->getService('Lesson');
//            $lesson = $lessonService->getOne($lessonService->find($lessonId));

//            $teacherUserId = $lesson->teacher;

//            if($eventId) {
                $this->view->studentEventUrl = Zend_Registry::get('serviceContainer')
                    ->getService('Proctoring')
                    /** proctoring url для студента */
                    ->getEventUrl(
//                        $eventId,
                        $lessonId,
                        HM_Proctoring_ProctoringService::ELS_ROLE_STUDENT,
                        $currentUserId,
                        null, // $teacherUserId // privateWithUserId
                        null, // massWatchUserIds
                        array(
                            'autoScreenShare' => $autoScreenShare ? 1 : 0,
                        ) // additionalParams
                    );

                return $this->view->render('proctoringStudent.tpl');
//            }
        }
    }
}
