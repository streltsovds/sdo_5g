<?php
class Project_CourseController extends HM_Controller_Action_Project
{
    public function indexAction()
    {

        $projectId = (int) $this->_getParam('project_id', 0);
        $courseId = (int) $this->_getParam('course_id', 0);

        $course = $this->getOne($this->getService('Course')->find($courseId));

        if ($course) {
            $this->view->setSubHeader($course->Title);
        }

        $this->view->course = $course;
        $this->view->projectId = $projectId;
        $this->view->courseId = $courseId;
    }
}