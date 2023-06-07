<?php

class Task_IndexController extends HM_Controller_Action_Task
{
    use HM_Controller_Action_Trait_Grid {
        newAction as newActionTraitGrid;
        editAction as editActionTraitGrid;
    }

    protected $id;
    protected $subject;
    
    public function init()
    {
        $this->_setForm(new HM_Form_Task());
        parent::init();
    }

    public function newAction()
    {
        $this->view->setSubHeader(_('Создание задания'));
        $this->newActionTraitGrid();
    }

    public function editAction()
    {
        $this->view->setSubHeader(_('Редактирование задания'));
        $this->editActionTraitGrid();
    }

    protected function _redirectToIndex()
    {
        if ($this->_task) {

            $redirect = [
                'module' => 'task',
                'controller' => 'variant',
                'action' => 'list',
                'task_id' => $this->_task->task_id,
            ];
            if ($this->_task->subject_id) {
                $redirect['subject_id'] = $this->_task->subject_id;
            }
            $this->_redirector->gotoUrl($this->view->url($redirect));
        }
        $this->_redirector->gotoSimple('index', 'list', 'task');
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Задание успешно создано'),
            self::ACTION_UPDATE => _('Задание успешно обновлено'),
            self::ACTION_DELETE => _('Задание успешно удалено'),
            self::ACTION_DELETE_BY => _('Задания успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $array = array(
                    'title' => $form->getValue('title'),
                    'status' => $form->getValue('status'),
                    'description' => $form->getValue('description'),
                    'subject_id' => $subjectId
                );


        if($subjectId == 0){
            $array['location'] = 1;
        }



        $this->_task = $task = $this->getService('Task')->insert(
            $array
        );

        if ($task) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($task->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($task->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK, $classifierId);
                    }
                }
            }
        }
        if ($tags = $form->getParam('tags',array())) {
            $this->getService('Tag')->updateTags( $tags, $task->task_id, $this->getService('TagRef')->getTaskType() );
        }

        if (($subjectId > 0 && $task)) {
            $this->getService('SubjectTask')->insert(array('subject_id' => $subjectId, 'task_id' => $task->task_id));
        }
    }

    public function update(Zend_Form $form)
    {

        $subjectid = (int) $this->_getParam('subject_id', 0);

        $task = $this->getService('Task')->getOne($this->getService('Task')->find($form->getValue('task_id')));

        if(!$task){
            return false;
        }

        $userRole = $this->getService('User')->getCurrentUserRole();

        if(!$this->getService('Task')->isEditable($task->subject_id, $subjectid, $task->location)){
            return false;
        }
        $task = $this->getService('Task')->update(
             array(
                 'task_id' => $form->getValue('task_id'),
                 'title' => $form->getValue('title'),
                 'status' => $form->getValue('status'),
                 'description' => $form->getValue('description')
             )
         );

        $this->getService('Tag')->updateTags( $form->getParam('tags',array()), $form->getValue('task_id'), $this->getService('TagRef')->getTaskType() );


        if ($task) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($task->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($task->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK, $classifierId);
                    }
                }
            }
        }

    }

    public function delete($id)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $task = $this->getService('Task')->getOne($this->getService('Task')->find($id));

        if(!$this->getService('Task')->isEditable($task->subject_id, $subjectId, $task->location)){

            return false;
        }
        $this->getService('Task')->delete($id);
        return true;
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('task_id', 0);
        if ($id) {
            $res = $this->delete($id);

            if($res == true){
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            }else{
                 $this->_flashMessenger->addMessage(_('Для удаления заданий не хватает прав'));
            }

        }
        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            $error = false;
            if (count($ids)) {
                foreach($ids as $id) {
                    $temp = $this->delete($id);
                    if($temp === false){
                        $error = true;
                    }
                }
                if($error === false){
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
                }else{
                    $this->_flashMessenger->addMessage(_('Глобальные задания невозможно удалить из учебного курса.'));
                }
            }
        }

        $this->_redirectToIndex();
    }

    public function previewAction()
    {
        $taskId = (int)$this->_getParam('task_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);

        $lesson = null;
        if ($lessonId) {
            $collection = $this->getService('Lesson')->find($lessonId);
            if (count($collection)) {

                /** @var HM_Lesson_LessonModel $lesson */
                $lesson = $collection->current();

                if(!$taskId)
                    $taskId = $lesson->getModuleId();

                $this->view->setHeader($lesson->title);
                $this->view->setBackUrl($this->view->url([
                    'module' => 'subject',
                    'controller' => 'lessons',
                    'action' => 'edit',
                    'subject_id' => $subjectId,
                    'lesson_id' => null,
                    'task_id' => null
                ]));
            }
        } else {
            $this->view->setBackUrl($this->view->url([
                'module' => 'task',
                'controller' => 'variant',
                'action' => 'list',
                'task_id' => $taskId,
            ], null, true));
        }

        $this->view->setSubHeader(_('Предварительный просмотр'));
        $select = $this->getService('TaskVariant')->getSelect();
        $select->from(['t' => 'tasks_variants'], ['variant_id'  => 't.variant_id','name'  => 't.name','description'  => 't.description'])
            ->where("t.task_id = ?", $taskId)
            ->order(['variant_id']);

        $variants = $select->query()->fetchAll();

        foreach($variants as &$variant) {
            $variant['description'] = $variant['description'];
            $files = $this->getService('TaskVariant')->getPopulatedFiles($variant['variant_id']);

            foreach ($files as $file) {
                if(empty($file->getDisplayName())) continue;
                $variant['files'][] = [
                    'id' => $file->getId(),
                    'displayName' => $file->getDisplayName(),
                    'path' => $file->getPath(),
                    'url' => $file->getUrl(),
                    'size' => $file->getSize(),
                    'type' => HM_Files_FilesModel::getFileType($file->getDisplayName()),
                ];
            }
        }

        $task = $this->getService('Task')->find($taskId)->current()->getValues();

        $task = [
            'name' => $lesson->title ?: $task['title'],
            'description' => $task['description']
        ];

        $this->view->task = $task;
        $this->view->variants = $variants;
        $this->view->editUrl = $this->view->url(array(
            'module'     => 'task',
            'controller' => 'variant',
            'action'     => 'list',
            'subject_id' => $subjectId,
            'task_id'    => $taskId
        ));
    }

    public function setDefaults(Zend_Form $form)
    {
        $taskId = (int) $this->_getParam('task_id', 0);

        $task = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));
        $values = $task->getValues();
        $values['tags'] = $this->getService('Tag')->convertAllToStrings($this->getService('Tag')->getTags($taskId, $this->getService('TagRef')->getTaskType()));
        if ($task) {
            $form->setDefaults( $values );
        }
    }
}