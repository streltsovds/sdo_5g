<?php
class HM_View_Helper_SubjectPreview extends HM_View_Helper_Abstract
{
    public function subjectPreview($subject, $marks,  $graduatedList, $studentCourseData, $data = array('isElective' => false, 'switcher' => 'list'), $fromProgram = false)
    {
        static $counter = 0;

        $services = Zend_Registry::get('serviceContainer');

        $userService      = $services->getService('User');
        $aclService       = $services->getService('Acl');

        $subjectId     = $subject->subid;
        $descriptionId = 'hm-subject-list-item-description-container-'.(++$counter);

        $userId    = $userService->getCurrentUserId();
        $userRole  = $userService->getCurrentUserRole();

        $isEndUser = $aclService->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $view = $this->view;

        $view->assign(array(
            'currentUserId'     => $userId,
            'subject'           => $subject,
            'subjectId'         => $subjectId,
            'studentCourseData' => $studentCourseData,
            'isElectiv'         => $data['isElective'],
            'switcher'          => $data['switcher'],
            'showScore'         => $isEndUser,
            'isTeacher'         => !$isEndUser,
            'disperseName'      => '',
            'disperse'          => false, // "Завершить обучение"
            'graduated'         => false, // "Курс завершен"
            'action'            => $isEndUser ? 'my' : 'index',
            'descriptionId'     => $descriptionId // id дива с описанием (табы) в правой части
        ));

        if ($isEndUser) {

            $courseIsGraduated = $graduatedList ? $graduatedList->exists('CID', $subject->subid) : false;

            $view->graduated = $courseIsGraduated;

            if (!$courseIsGraduated) {
                if (($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_FREE || $subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN) && $subject->isAccessible()){
                    $view->disperse = true;
                }

                $this->createDescriptionTabs($descriptionId, $subject);
            }
        } else {
            $this->createDescriptionTabs($descriptionId, $subject);
        }

        $view->fromProgram = $fromProgram;

        return $view->render('subject-preview.tpl');
    }

    protected function createDescriptionTabs($descriptionId, $subject)
    {
        $services = Zend_Registry::get('serviceContainer');

        $lessonService       = $services->getService('Lesson');
        $lessonAssignService = $services->getService('LessonAssign');
        $userService         = $services->getService('User');
        $aclService          = $services->getService('Acl');

        $isTeacher = $aclService->inheritsRole(
            $userService->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_TEACHER
        );

        $userId = (int)$userService->getCurrentUserId();

        $view = $this->view;

        if ($isTeacher) {
            $where = $lessonService->quoteInto(
                array(
                    ' CID = ?',
                    ' AND isfree = ?'
                ),
                array(
                    $subject->subid,
                    HM_Lesson_LessonModel::MODE_PLAN,
                )
            );
            $lessons = $lessonService->fetchAll($where);

        } else {
            $where = $lessonService->quoteInto(
                array(
                    'Assign.MID = ?',
                    ' AND self.CID = ?',
                    ' AND self.isfree = ?'
                ),
                array(
                    $userId,
                    $subject->subid,
                    HM_Lesson_LessonModel::MODE_PLAN,
                )
            );
            $lessons = $lessonService->fetchAllDependenceJoinInner('Assign', $where);
        }

        $lessonsArray = array();
        $now = time();

        /**
         * @var $lesson HM_Lesson_LessonModel
         */

//pr($assigns);
        foreach ($lessons as $lesson) {

            if ($lesson) {

                // берём персональные даты из scheduleID, т.к. возможна индивидуальная настройка
                if (!$isTeacher) {
                    $lesson->begin = $lesson->getBeginDatePersonal($userId);
                    $lesson->end = $lesson->getEndDatePersonal($userId);
                }

                $begin = strtotime($lesson->begin);
                $end   = strtotime($lesson->end);
                $isFree = !$begin && !$end; //$lesson->isTimeFree();

                $datetimeFormat = (date('H:i', $begin) != '00:00') ? 'd.m H:i' : 'd.m';

                $lessonUrlParams = array(
                    'action'     => 'index',
                    'controller' => 'execute',
                    'module'     => 'lesson',
                    'lesson_id'  => $lesson->SHEID,
                    'subject_id' => $lesson->CID
                );

                $lessonsArray[$lesson->SHEID] = array(
                    'SHEID'       => $lesson->SHEID,
                    'CID'         => $lesson->CID,
                    'description' => $lesson->descript,
                    'title'       => $lesson->title,
                    'isFree'      => $isFree,
                    'isTeacher'   => $isTeacher,
                    'begin'       => date($datetimeFormat, $begin),
                    'end'         => date($datetimeFormat, $end),
                    'isExpired'   => (!$isFree) && ($end < $now),
                    'url'         => $view->url($lessonUrlParams, false, true),
                    'order'       => $lesson->order,
                );
            }

        }

        usort($lessonsArray, function($a, $b){
            if($a['order'] || $b['order']){
                if($a['order'] > $b['order']){
                    return 1;
                } else {
                    return -1;
                }
            } else {
                if($a['SHEID'] > $b['SHEID']){
                    return 1;
                } else {
                    return -1;
                }
            }
        });
        
        $HM = $view->HM();

        // создаём описание курса с табами в правой части
        $HM->create('hm.module.course.ui.list.CourseDescriptionTabs', array(
            'renderTo' => '#'.$descriptionId,
            'lessons'  => $lessonsArray,
            'course_id' => $subject->subid,
            'hideTabs' => $isTeacher ? array('progress') : array()
        ));
    }
}