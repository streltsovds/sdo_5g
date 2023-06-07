<?php

class StudyGroups_CoursesController extends HM_Controller_Action
{

    public function assignCourseAction($base = HM_Subject_SubjectModel::BASETYPE_BASE)
    {
        /** @var HM_StudyGroup_Course_CourseService $studyGroupCourse */
        $studyGroupCourse = $this->getService('StudyGroupCourse');

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        /** @var HM_StudyGroup_StudyGroupService $studyGroupService */
        $studyGroupService = $this->getService('StudyGroup');

        $groupIds = [];
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $groupIds = explode(',', $postMassIds);
        }
        $subjectIds = $this->_getParam('subjectId', 0);

        if (!count($groupIds)) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ]);
            $this->_redirectToIndex();
        }

        if ($subjectIds === 0) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' =>
                    $base == HM_Subject_SubjectModel::BASETYPE_BASE
                        ? _('Укажите один или несколько курсов')
                        : _('Укажите одну или несколько сессий')
            ]);
            $this->_redirectToIndex();
        }

        foreach ($subjectIds as $subjectId) {
            /* Проверяем или существует курс */
            $subject = $subjectService->getById($subjectId);

            if (!$subject) {
                continue;
            }

            /* Перебираем группы */
            foreach ($groupIds as $id) {
                $group = $studyGroupService->getById($id);

                /* Записываем группу на курс */
                if ($group) {
                    $studyGroupCourse->addCourseOnGroup($subject->subid, $group->group_id);
                }
            }
        }

        $this->_flashMessenger->addMessage(
            $base == HM_Subject_SubjectModel::BASETYPE_BASE
                ? _('Учебные курсы успешно назначены')
                : _('Учебные сессии успешно назначены')
        );
        $this->_redirectToIndex();
    }

    public function unassignCourseAction($base = HM_Subject_SubjectModel::BASETYPE_BASE)
    {
        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        /** @var HM_StudyGroup_StudyGroupService $studyGroupService */
        $studyGroupService = $this->getService('StudyGroup');

        /** @var HM_StudyGroup_Course_CourseService $studyGroupCourseService */
        $studyGroupCourseService = $this->getService('StudyGroupCourse');

        $groupIds = [];
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $groupIds = explode(',', $postMassIds);
        }
        $subjectIds = $this->_getParam('subjectId', 0);

        if (!count($groupIds)) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ]);
            $this->_redirectToIndex();
        }

        if ($subjectIds === 0) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' =>
                    $base == HM_Subject_SubjectModel::BASETYPE_BASE
                        ? _('Укажите один или несколько курсов')
                        : _('Укажите одну или несколько сессий')
            ]);
            $this->_redirectToIndex();
        }

        foreach ($subjectIds as $subjectId) {
            /* Проверяем или существует курс */
            $course = $subjectService->getById($subjectId);
            if (!$course) {
                continue;
            }

            /* Перебираем группы */
            foreach ($groupIds as $id) {
                $group = $studyGroupService->getById($id);
                if ($group) {
                    /* Отписываем группу с курса */
                    $studyGroupCourseService->removeGroupFromCourse($group->group_id, $course->subid);
                }
            }
        }

        $this->_flashMessenger->addMessage(
            $base == HM_Subject_SubjectModel::BASETYPE_BASE
                ? _('Учебные курсы успешно назначены')
                : _('Учебные сессии успешно назначены')
        );
        $this->_redirectToIndex();
    }

    public function assignSessionAction()
    {
        $this->assignCourseAction(HM_Subject_SubjectModel::BASETYPE_SESSION);
    }

    public function unassignSessionAction()
    {
        $this->unassignCourseAction(HM_Subject_SubjectModel::BASETYPE_SESSION);
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoRoute([
            'module' => 'study-groups',
            'controller' => 'list',
            'action' => 'index'
        ], null, true);
    }

}