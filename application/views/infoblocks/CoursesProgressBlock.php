<?php

require_once APPLICATION_PATH . '/views/helpers/Score.php';

class HM_View_Infoblock_CoursesProgressBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'CoursesProgress';

    public function coursesProgressBlock($param = null)
    {
        $services = Zend_Registry::get('serviceContainer');

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        $currentUserId = $userService->getCurrentUserId();

        $subjects = $services->getService('Student')->getSubjects()->getList('subid');

        $courses = array();

        if (count($subjects)) {
            $lessonAssigns = $services->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $services->getService('Lesson')->quoteInto(
                array(
                    'self.MID = ?',
                    ' AND Lesson.typeID IN (?)',
                    ' AND Lesson.CID IN (?)',
                    ' AND Lesson.isfree != ?',
                ), array(
                    $currentUserId,
                    array(HM_Event_EventModel::TYPE_COURSE, HM_Event_EventModel::TYPE_LECTURE),
                    $subjects,
                    HM_Lesson_LessonModel::MODE_FREE_BLOCKED
                ))
            );

            if (count($lessonAssigns)) {

                foreach ($lessonAssigns as $lessonAssign) {

                    /** @var HM_Lesson_LessonModel $lesson */
                    $lesson = $lessonAssign->lessons->current();
                    $lessonType = $lesson->getType();
                    $params = $lesson->getParams();
                    if (!$params['module_id']) continue;

                    $courseId = $params['module_id'];
                    if ($lessonType == HM_Event_EventModel::TYPE_LECTURE) {
                        $courseId = $params['course_id'];
                    }

                    /** @var HM_Course_CourseService $courseService */
                    $courseService = $this->getService('Course');
                    /** @var HM_Course_CourseModel $courseModel */
                    $courseModel = $courseService->getOne($courseService->find($courseId));
                    if (!$courseModel || (!$courseModel->isImportFormat() && !$courseModel->isScormEmulationAllowed())) {
                        //не отображаем модули без SCORM
                        continue;
                    }

                    $course = array(
                        'isfree' => $lesson->isfree,
                        'lesson' => $lesson,
                        'lessonAssign' => $lessonAssign,
//                        'lessonUrl' => $this->view->url(array('action' => 'my', 'controller' => 'list', 'module' => 'lesson', 'subject_id' => $lesson->CID), false, true) . '/#' . $lesson->SHEID,
                        'launchUrl' => $this->view->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson->SHEID, 'subject_id' => $lesson->CID), false, true),
                        'statsUrl' => $this->view->url(array(
                            'action' => 'listlecture',
                            'controller' => 'result',
                            'module' => 'lesson',
                            'lesson_id' => $lesson->SHEID,
                            'subject_id' => $lesson->CID,
                            'userdetail' => 'yes' . $currentUserId,
                            'switcher' => 'listlecture',
                        ), false, true),
                        'isStatsAllowed' => true, //($lessonAssign->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART), // in_array($lesson->material->format, HM_Course_CourseModel::getInteractiveFormats()), // @todo: не показывать те форматы, которые в принципе не имеют статистики
                        'isLaunchAllowed' => true, //($lessonAssign->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE),
                        'progress' => (int)$this->getService('Lesson')->getTotalCoursePercent($lesson, $currentUserId, $courseId),
                    );

                    if ($lessonType == HM_Event_EventModel::TYPE_COURSE){
                        $course['new_window'] = $courseModel->new_window;
                    }

                    if (!$lesson->isfree) {

                        $begin = $lesson->getBeginDatePersonal($currentUserId);
                        $end = $lesson->getEndDatePersonal($currentUserId);

// @todo: здесь ошибка 500
//                        $datetime = HM_View_Helper_LessonPreview::formatBeginEnd($begin, $end);

                        $course['datetime'] = $datetime;
                        $course['datetimeLabel'] = $lesson->recommend ? _('рекомендуемое время выполнения') : _('время выполнения');
                        
                        $course['status'] = ($lessonAssign->V_STATUS == -1) ? 'incomplete' : 'passed';
                        $course['statusLabel'] = $this->view->score(array(
                            'score' => $lessonAssign->V_STATUS,
                            'user_id' => $currentUserId,
                            'lesson_id' => $lesson->SHEID,
                            'scale_id' => $lesson->getScale(),
                            'mode' => HM_View_Helper_Score::MODE_DEFAULT,
                        ));
                        
                    } else {
                        
                        switch ($lessonAssign->V_DONE) {
                        	case HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART:
                        		$course['status'] = 'not-attempted';
                        		$course['statusLabel'] = _('не начат');
                        		break;
                        	case HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS:
                        		$course['status'] = 'incomplete';
                        		$course['statusLabel'] = _('в процессе');
                        		break;
                        	case HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE:
                    		    $course['status'] = 'passed';
                    		    $course['statusLabel'] = _('завершен');
                        		break;
                        }
                        
                    }

                    if (!isset($courses[$params['module_id']])) {
                        $courses[$params['module_id']] = $course; // например, если чела дважды назначили на один и тот же курс - результаты удваиваются; 
                    }
                }

                uasort($courses, array('HM_View_Infoblock_CoursesProgressBlock', '_sort'));
            }
        }

        $this->view->courses = $courses;

		$content = $this->view->render('coursesProgressBlock.tpl');
        
        return $this->render($content);
    }
    
    public function _sort($course1, $course2)
    {
        if ($course1['subject_id'] == $course2['subject_id']) {
            if ($course1['lesson']->order == $course2['lesson']->order) {
                return ($course1['lesson']->SHEID < $course2['lesson']->SHEID) ? -1 : 1;
            } else {
                return ($course1['lesson']->order < $course2['lesson']->order) ? -1 : 1;
            }
        } else {
            return ($course1['subject_id'] < $course2['subject_id']) ? -1 : 1;
        }
    }        
}