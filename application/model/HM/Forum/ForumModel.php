<?php
class HM_Forum_ForumModel extends HM_Model_Abstract implements HM_Material_Interface
{
    const DEFAULT_FORUM = 1; // главный форум на уровне портала

    // 5G Implementing HM_Material_Interface для занятия типа Форум
    public function becomeLesson($subjectId)
    {
        return $this->getService()->createLesson($subjectId, $this);
    }

    public function getName()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getServiceName()
    {
        return 'Forum';
    }
}
