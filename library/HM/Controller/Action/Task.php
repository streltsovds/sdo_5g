<?php
class HM_Controller_Action_Task extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;

    protected $_task;
    protected $_taskId;

    protected $_backUrl = null;

    public function init()
    {
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        $taskId = $this->_taskId = (int) $this->_getParam('task_id', 0);

        if ($taskId) {

            $task = $this->_task = $this->getOne(
                $this->getService('Task')->findDependence(['TaskVariant'], $taskId)
            );

            if ($task) {

                $this->view->task = $this->_task;

                $subjectId = (int) $this->_getParam('subject_id', $this->_task->subject_id);

                $this->initContext($this->_task, 'task');

                if ($acl->isSubjectContext()) {

                    // @todo: не всегда
                    $this->view->setBackUrl($this->view->url([
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'edit',
                        'subject_id' => $subjectId,
                    ], null, true));
                } else {

                    $this->view->setBackUrl($this->view->url([
                        'module' => 'task',
                        'controller' => 'list',
                        'action' => 'index',
                    ], null, true));
                }

                // в любых контекстах показываем actions и sidebar от теста
                if (!empty($this->_task)) {
                    $page = sprintf('%s-%s-%s',
                        $this->getRequest()->getModuleName(),
                        $this->getRequest()->getControllerName(),
                        $this->getRequest()->getActionName()
                    );
                    if(($lessonId = $this->_getParam('lesson_id', 0)) && $page != 'task-variant-list') { // Страница редактирования материала
                        $lesson = $this->getService('Lesson')->getOne(
                            $this->getService('Lesson')->findDependence('Subject', $lessonId)
                        );
                        $this->view->replaceSidebar('subject', 'subject-lesson', [
                            'model' => $lesson,
                            'order' => 100, // после Subject
                        ]);
                    } else {
                        $this->view->addSidebar('task', [
                            'model' => $this->_task,
                        ]);
                    }

                    $this->view->setHeader($this->_task->title);
                }
            }
        }

        parent::init();
    }

    public function getContextNavigationModifiers()
    {
        $modifiers = [];

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        if($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER) && !$acl->isSubjectContext())
            $modifiers[] = new HM_Navigation_Modifier_Remove_Action('action', 'new');

        return $modifiers;
    }

}