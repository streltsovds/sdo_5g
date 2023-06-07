<?php
class HM_Form_TcAssignTeachersToSubject extends HM_Form_SimpleForm
{
    protected $_subjectId = 0;
    protected $_teachers  = array();

    public function setSubjectId($subjectId)
    {
        $this->_subjectId = $subjectId;
    }

    public function setTeachers($teachers)
    {
        $this->_teachers = $teachers;
    }

    protected function _initElements()
    {
        $this
            ->simpleMultiSelectElement('teachers', array(
                'Label'   => _('Учебные курсы'),
                'Type'    => 'int',
                'Options' => $this->_teachers,
            ))

            ->simpleGroup(_('Общие свойства'));

    }

}