<?php
class HM_Eclass_EclassModel extends HM_Model_Abstract implements HM_Material_Interface
{
    protected $_primaryName = 'id';

    public function getName()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return '';
    }

    public function becomeLesson($subjectId)
    {
        return $this->getService()->createLesson($subjectId, $this->id);
    }

    public function getServiceName()
    {
        return 'Eclass';
    }
}