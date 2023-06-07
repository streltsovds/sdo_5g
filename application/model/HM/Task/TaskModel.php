<?php

class HM_Task_TaskModel extends HM_Test_Abstract_AbstractModel implements HM_Material_Interface
{
    //Статусы
    const STATUS_UNPUBLISHED = 0;
    const STATUS_STUDYONLY   = 1;

    protected $_primaryName = 'task_id';

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED    => _('Не опубликован'),
            self::STATUS_STUDYONLY      => _('Ограниченное использование'),
        );
    }

    public function getTestType()
    {
        return HM_Test_TestModel::TYPE_TASK;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getViewUrl()
    {
        if(!$this->task_id) return false;
        return array(
            'module' => 'task',
            'controller' => 'variant',
            'action' => 'list',
            'task_id' => $this->task_id,
        );
    }

    public function getIconClass()
    {
        return '';
    }

    public function getCreateUpdateDate()
    {
        $return = sprintf(_('Создан: %s'), $this->dateTime($this->created));
        if ($this->created != $this->updated) {
            $return .= ', ' . sprintf(_('обновлён: %s'), $this->dateTime($this->updated));
        }
        return $return;
    }

    /*
     * 5G
     * Implementing HM_Material_Interface
     */
    public function becomeLesson($subjectId)
    {
        return $this->getService()->createLesson($subjectId, $this->task_id);
    }

    public function getServiceName()
    {
        return 'Task';
    }

    public function getUnifiedData()
    {
        $modelData = $this->getData();
        $unifiedData = [
            'id' => $modelData['task_id'],
            'title' => $modelData['title'],
            'created' => $modelData['created'],
            'updated' => $modelData['updated'],
            'kbase_type' => 'task',
            'tag' => $modelData['tag'],
            'classifiers' => $modelData['classifiers'],
            'subject_id' => $modelData['subject_id'],
        ];

        $view = Zend_Registry::get('view');
        $unifiedData['viewUrl'] = $view->url([
            'module' => 'task',
            'controller' => 'variant',
            'action' => 'list',
            'task_id' => $modelData['task_id'],
        ]);

        return array_merge($modelData, $unifiedData);
    }


}