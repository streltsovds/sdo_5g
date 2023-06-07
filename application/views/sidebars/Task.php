<?php

class HM_View_Sidebar_Task extends HM_View_Sidebar_Abstract
{
    function getIcon()
    {
        return 'Material'; // @todo
    }

    public function getTitle()
    {
        return 'Материалы';
    }

    function getContent()
    {
        $data = [];
        $task = $this->getModel();

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $isTeacherOrDean = $aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        ]);

        if ($isTeacherOrDean) {
            if ($aclService->isAllowed($this->getService('User')->getCurrentUserRole(), sprintf('mca:%s:%s:%s', 'task', 'index', 'edit'))) {

                $data['editUrl'] = $this->view->url([
                    'module' => 'task',
                    'controller' => 'index',
                    'action' => 'edit',
                    'task_id' => $task->task_id,
                    'subject_id' => $task->subject_id,
                ], null, true);
            }

            $data['previewUrl'] = $this->view->url([
                'module' => 'task',
                'controller' => 'index',
                'action' => 'preview',
                'task_id' => $task->task_id,
                'subject_id' => $task->subject_id,
            ], null, true);
        }

        if ($task->subject_id) {
            /** @var HM_Subject_SubjectModel $subject */
            $subject = $this->getService('Subject')->fetchRow(['subid = ?' => $task->subject_id]);
            if($subject) {
                $subject->icon = $subject->getIcon();
                $data['subject'] = $subject->getData();
            }
        }

        $data['taskTags'] = $this->getService('Tag')->getTags($task->task_id, HM_Tag_Ref_RefModel::TYPE_TASK);
        $data['taskClassifiers'] = $this->getService('Classifier')->getItemClassifiers($task->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK)->asArrayOfArrays();
        $data['task'] = $task;

        $data = HM_Json::encodeErrorSkip($data);

        return $this->view->partial('task.tpl', ['data' => $data]);
    }
}