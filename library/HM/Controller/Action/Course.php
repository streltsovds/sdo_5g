<?php
class HM_Controller_Action_Course extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    protected $_courseId = 0;
    protected $_course = null;
        
    public function init()
    {
        $this->_courseId = (int) $this->_getParam('course_id', $this->_getParam('CID', 0));
        $this->_course = $this->getOne($this->getService('Course')->find($this->_courseId));

        if ($this->_course) {

            $this->initContext($this->_course); // ???

            // в любых контекстах показываем actions и sidebar от теста
            if(!empty($this->_course)) {
                if($lessonId = $this->_getParam('lesson_id', 0) && $this->_getParam('action') == 'index') {
                    $lesson = $this->getService('Lesson')->getOne(
                        $this->getService('Lesson')->findDependence('Subject', $lessonId)
                    );
                    $this->view->replaceSidebar('subject', 'subject-lesson', [
                        'model' => $lesson,
                        'order' => 100, // после Subject
                    ]);
                } else {
                    $this->view->addSidebar('course', [
                        'model' => $this->_course,
                    ]);
                }

                if (!$this->view->getHeader()) {
                    $this->view->setHeader($this->_course->Title);
                }
            }

            $subjectId = (int) $this->_getParam('subject_id', $this->_course->subject_id);
            if ($subjectId) {

                $this->_backUrl = $this->view->url([
                    'module' => 'subject',
                    'controller' => 'lessons',
                    'action' => $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'index' : 'edit',
                    'subject_id' => $subjectId,
                ], null, true);

                $this->view->setSwitchContextUrls(false);
            } else {
                $this->_backUrl = $this->view->url([
                    'module' => 'kbase',
                    'controller' => 'courses',
                    'action' => 'index',
                ], null, true);
            }

            $this->view->setBackUrl($this->_backUrl);
        }

        parent::init();
    }
}