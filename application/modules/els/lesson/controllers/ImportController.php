<?php

class Lesson_ImportController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_module = 'lesson';
    protected $_controller = 'import';
    
    public function init() {
        $form = new HM_Form_Upload();
        $form->getElement('file')->setOptions(
            array(
                'Label' => _('Файл данных (csv)'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Validators' => array(
//                    array('Count', false, 1),
                    array('Extension', false, 'csv')
                ),
                'file_size_limit' => 0,
                'file_types' => '*.csv',
                'file_upload_limit' => 0,
                'Required' => true
            )
        );
        
        $this->_setForm($form);
        parent::init();
    }  
    
    public function csvAction()
    {
        if (!$this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN)
        )) {
            return false;
        }
        
        $form = $this->_getForm();
        if ($this->_request->isPost()) {            
            $file = $form->getElement('file');
            if($file->isUploaded()){
                $file_names = $form->getValue('file');
                if(!is_array($file_names)){
                    $file_names = array('file' => $file_names);
                }
                $csvService = $this->getService('LessonCsv');
                $imported = $csvService->importResults($file_names);
            }
            
            if ($imported) {
                $this->_flashMessenger->addMessage(sprintf(_('Импортированно %d запись(ей)'), $imported));
            } else {
                $this->_flashMessenger->addMessage(_('Не было импортированно ни одной записи!'));
            }
            $this->_redirector->gotoSimple('csv', 'import', 'lesson');
        } 
        
        
        
        
        $this->view->setHeader(_('Импортировать результаты занятия из CSV'));
        $this->view->form = $form;
    }

}

?>