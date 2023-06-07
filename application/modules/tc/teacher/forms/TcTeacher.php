<?php
class HM_Form_TcTeacher extends HM_Form_SimpleForm
{
    protected $_subjectId  = 0;
    protected $_providerId = 0;
    protected $_teacherId  = 0;
    protected $_providers  = array();
    protected $_subjects   = array();

    public function setProviders($providers)
    {
        $this->_providers = $providers;
    }

    public function setSubjectId($subjectId)
    {
        $this->_subjectId = $subjectId;
    }

    public function setProviderId($providerId)
    {
        $this->_providerId = $providerId;
    }

    public function setSubjects($subjects)
    {
        $this->_subjects = $subjects;
    }

    public function setTeacherId($teacherId)
    {
        $this->_teacherId = $teacherId;
    }

    protected function _initElements()
    {
        $this->simpleHiddenElement('teacher_id', array(
            'Type'  => 'int',
            'Value' => $this->getParam('teacher_id', 0)
        ));

        $this->_initGroupMain();
        $this->_initGroupSubjects();
        $this->_initGroupFiles();

    }

    protected function _initGroupSubjects()
    {
        if (!$this->_providerId) {
            return;
        }

        $this
            ->simpleMultiSelectElement('subjects', array(
                'Label'   => _('Очные курсы'),
                'Type'    => 'int',
                'Options' => $this->_subjects,
            ))

            ->simpleGroup(_('Назначения'));

    }

    protected function _initGroupFiles()
    {
        $this
            ->simpleFileElement('files')

            ->simpleGroup(_('Дополнительная информация'));

    }

    protected function _initGroupMain()
    {
        $this
            ->simpleTextElement('name', array(
                'Label'    => _('ФИО'),
                'Required' => true,
            ))

            ->simpleTextAreaElement('description', array(
                'Label' => _('Краткое описание'),
            ))

            ->simpleTextAreaElement('contacts', array(
                'Label' => _('Контактная информация'),
            ));

        if (!empty($this->_providers)) {

            $this->simpleSelectElement('provider_id', array(
                'Label'    => _('Провайдер'),
                'Required' => true,
                'Options'  => $this->_providers,
                'Type'     => 'int'
            ));

        }

        $this->simpleGroup(_('Общие свойства'));

    }

}