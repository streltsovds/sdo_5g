<?php

class Option_UploadTemplatesController extends HM_Controller_Action
{
    protected $templates = array(
        'study_journal',
        'study_protocol',
    );

    public function indexAction()
    {
        $isLaborSafetyLocal = $this->currentUserRole(array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL));
        $form = ($isLaborSafetyLocal) ?
            new HM_Form_UploadTemplatesReadOnly() :
            new HM_Form_UploadTemplates();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {

                $this->uploadTemplates($form);

                $this->_flashMessenger->addMessage(_('Файлы шаблонов успешно изменены.'));
                $this->_redirector->gotoSimple('index', 'upload-templates', 'option');
            } else {
                $form->populate($this->_request->getParams());
            }
        } else {
            $this->populateFilesForm($form);
        }

        $this->view->isLaborSafetyLocal = $isLaborSafetyLocal;
        $this->view->form = $form;
    }

    public function deleteTemplateAction()
    {
        $template = $this->getRequest()->getParam('template');
        $dirName  = Zend_Registry::get('config')->path->templates->print_forms;
        $fileName = 'form_'.$template.'.docx';
        $fileRealPath = $dirName . $fileName;
        if ($fileRealPath) {
            @unlink($fileRealPath);
        }
    }

    public function downloadCurrentTemplateAction()
    {
        $template = $this->getRequest()->getParam('template');
        $dirName  = Zend_Registry::get('config')->path->templates->print_forms;
        $fileName = 'form_'.$template.'.docx';
        $fileRealPath = $dirName . $fileName;

        $options = array('filename' => $fileName);

        if (file_exists($fileRealPath))
        {
            $this->_helper->SendFile(
                $fileRealPath,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $options
            );
            die();
        }
    }

    protected function uploadTemplates($form)
    {
        foreach ($this->templates as $template) {
            if ($file = $form->getElement($template)) {
                if ($file->isUploaded()){
                    $dirName  = Zend_Registry::get('config')->path->templates->print_forms;
                    $fileName = 'form_'.$template.'.docx';
                    $fileRealPath = $dirName . $fileName;
                    if ($fileRealPath) {
                        @unlink($fileRealPath);
                    }
                    $file->addFilter('Rename', $fileRealPath, $template, true);
                    $file->receive();
                }
            }
        }
    }

    protected function populateFilesForm($form)
    {
        foreach ($this->templates as $template) {
            $dirName  = Zend_Registry::get('config')->path->templates->print_forms;
            $fileRealPath = $dirName . 'form_'.$template.'.docx';

            if (file_exists($fileRealPath)) {
                $populatedFile = new HM_File_FileModel(
                    array(
                        'id' => $template,
                        'displayName' => 'form_'.$template.'.docx',
                        'path' => $fileRealPath,
                        'url' => $this->view->url(
                            array(
                                'module' => 'option',
                                'controller' => 'upload-templates',
                                'action' => 'download-current-template',
                                'template' => $template,
                                'baseUrl' => ''
                            )
                        ),
                    )
                );
                if ($file = $form->getElement($template)) {
                    $file->setValue($populatedFile);
                }
            }
        }
    }
}