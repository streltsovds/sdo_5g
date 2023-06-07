<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_SubjectLesson extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'lesson'; // @todo
    }

    public function getTitle()
    {
        return 'Занятие';
    }

    public function getContent()
    {
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');

        $currentLesson = $this->getModel();
        $currentLesson->icon = $currentLesson->getUserIcon() ? : $currentLesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM);

        $currentLesson->lessonDate = $currentLesson->getBeginEnd();

        /** @var HM_Lesson_LessonModel $nextLesson */
        $nextLesson = $lessonService->getNextLesson($currentLesson->SHEID);

        $currentUser = $this->getService('User')->getCurrentUser();

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        if($nextLesson) {

            try {
                $isExecutable = $nextLesson->isExecutable();
                $nextLesson->isExecutable = $isExecutable;
            } catch (Exception $e) {
                $nextLesson->isExecutable = false;
            }

            if ($isExecutable) {
                $nextLesson->executeUrl = $this->view->url(['module' => 'subject', 'controller' => 'lesson', 'action' => 'index', 'lesson_id' => $nextLesson->SHEID]);;
                $nextLesson->icon = $nextLesson->getUserIcon() ?: $nextLesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM);

                if ($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

                    /** @var HM_Lesson_Assign_AssignModel $assign */
                    $assign = $this->getService('LessonAssign')->fetchRow(['SHEID = ?' => $nextLesson->SHEID, 'MID = ?' => $currentUser->MID]);

                    if ($assign)
                        $nextLesson->lessonDate = $assign->getBeginEnd();
                } else {
                    $nextLesson->lessonDate = $nextLesson->getBeginEnd();
                }
            } else {
                $nextLesson = false;
            }
        }

        $subject = $currentLesson->subject->current();
        $subject->icon = $subject->getDefaultIcon();
        $subject->image = $subject->getUserIcon();
        $subject->begin = $subject->getBegin();
        $subject->end = $subject->getEnd();
        $subject->isAccessible = $subject->isAccessible();

        $data = [
            'currentLesson' => $currentLesson,
            'nextLesson' => $nextLesson,
            'subject' => $subject,
        ];

        $data['editUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'list', 'action' => 'edit', 'subid' => $subject->subid]);
        $data['showEdit'] = $acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]);
        $data['currentUser'] = $currentUser;

        $data['subjectPlanUrl'] = $this->view->url([
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => 'index',
            'subject_id' => $subject->subid
        ]);

        $data = HM_Json::encodeErrorSkip($data);

        return $this->view->partial('subject/lesson.tpl', ['data' => $data]);
    }
}