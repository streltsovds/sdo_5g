<?php
class HM_State_Form_FilesForm extends HM_State_Form_AbstractForm
{
    public function init()
    {
        $this->setName('state_files');

        parent::init();

    }

    protected function _initGroupMain()
    {
        $elements = array();

        $this->addElement($this->getDefaultFileElementName(), $elements[] = 'files', array(
            'Validators' => array(
                array('Count', false, 10)
            ),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'file_size_limit' => 0,
            'file_types' => '*.*',
            'file_upload_limit' => 10,
            'Required' => false
        ));

    }

    protected function _getSaveUrl()
    {
        return $this->getView()->url(array(
            'baseUrl'    => '',
            'module'     => 'state',
            'controller' => 'edit',
            'action'     => 'edit',
            'field'      => 'file',
            'state'      => $this->_state,
            'stateId'    => $this->_stateId
        ));
    }

    public function getIconConfig()
    {
        return array(
            'title'     => _('Прикрепить файлы'),
            'name' => 'attach_file'
        );
    }

}