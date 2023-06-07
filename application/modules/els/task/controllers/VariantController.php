<?php

class Task_VariantController extends HM_Controller_Action_Task
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $this->_setForm(new HM_Form_Variant());
        parent::init();
    }

    public function listAction()
    {
        $select = $this->getService('TaskVariant')->getSelect();
        $select
            ->from(['t' => 'tasks_variants'], [
                'variant_id' => 't.variant_id',
                'name' => 't.name'
            ])
            ->where("t.task_id = ?", $this->_taskId);

        $grid = $this->getGrid($select, [
            'variant_id' => ['hidden' => true],
            'name' => ['title' => _('Название')]
        ], [
            'name' => null
        ]);

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        $isDean = $acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN]);
        $isTeacher = $acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_TEACHER]);

        if ($isDean || ($isTeacher && $acl->isSubjectContext())) {
            $grid->addAction(['module' => 'task',
                'controller' => 'variant',
                'action' => 'edit'],
                ['variant_id'],
                $this->view->svgIcon('edit', 'Редактировать'));

            $grid->addAction(['module' => 'task',
                'controller' => 'variant',
                'action' => 'delete'],
                ['variant_id'],
                $this->view->svgIcon('delete', 'Удалить'));

            $grid->addMassAction(['module' => 'task',
                'controller' => 'variant',
                'action' => 'delete-by'],
                _('Удалить'),
                _('Вы уверены, что хотите удалить варианты задания? Если данные варианты были ранее назначены слушателям, соответствующие назначения будут отменены.'));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Вариант успешно создан'),
            self::ACTION_UPDATE => _('Вариант успешно обновлён'),
            self::ACTION_DELETE => _('Вариант успешно удалён'),
            self::ACTION_DELETE_BY => _('Варианты успешно удалены')
        );
    }

    public function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('list', 'variant', 'task', [
            'task_id' => $this->_getParam('task_id', 0),
            'lesson_id' => $this->_getParam('lesson_id', 0),
            'subject_id' => $this->_task->subject_id ? : null
        ]);
    }

    public function setDefaults($form)
    {
        $variantId = $this->_getParam('variant_id', '');
        $variant = $this->getOne($this->getService('TaskVariant')->find($variantId));

        if ($variant) {
            $form->setDefaults($variant->getValues());

            /** @var HM_Form_Element_Vue_File $filesElement */
            $filesElement = $form->getElement('files');
            $variantFiles = $this->getService('Files')->fetchAll([
                'item_type = ?' => HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT,
                'item_id = ?' => $variant->variant_id,
                'name <> ?' => '',
            ]);

            $uploadedItems = [];

            /** @var HM_Files_FilesModel $variantFile */
            foreach ($variantFiles as $variantFile) {
                $uploadedItems[] = $variantFile->getDataForVueFile();
            }

            $filesElement->setUploadedFileInfo($uploadedItems);
        }
    }

    private function _saveFiles($variant, Zend_Form $form)
    {
/*
//        $populatedFiles = $this->getService('TaskVariant')->getPopulatedFiles($variant->variant_id);
        $deletedFiles = array();//$form->files->updatePopulated($populatedFiles);
        if(count($deletedFiles))
        {
            $this->getService('Files')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
            $this->getService('TaskVariantFile')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
        }
//
*/      // !!!!! УДАЛЕНИЕ ФАЙЛОВ НЕ СДЕЛАНО

        $files = $form->getElement('files');
        if ($files->isUploaded()) {

            /** @var HM_Files_FilesService $filesService */
            $filesService = $this->getService('Files');

            if ($files instanceof HM_Form_Element_ServerFile) {

                if ($files->receive()) {
                    $files = $files->getFileName();

                    if (!is_array($files)) {
                        $files = array($files);
                    }

                    foreach ($files as $file) {
                        $fileInfo = pathinfo($file);
                        $filesService->addFile($file, $fileInfo['basename'], HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT, $variant->variant_id);
                    }
                }

            } elseif($files instanceof HM_Form_Element_Vue_File) {
                $filesInfo = (array) $files->getFileInfo();
                $fileNames = array_column($filesInfo, 'name');

                $destination = $files->getDestination();

                foreach ($fileNames as $fileKey => $fileName) {
                    $fileSrc = rtrim($destination,'/') . '/' . $fileName;
                    $filesService->addFile($fileSrc, $fileName, HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT, $variant->variant_id);
                    unlink($fileSrc);
                }
            }
        }
    }

    public function create($form)
    {
        $variant = $this->getService('TaskVariant')->insert(
            [
                'task_id' => $this->getRequest()->getParam('task_id'),
                'name' => $form->getValue('name', ''),
                'description' => $form->getValue('description', ''),
            ]
        );

        if ($variant) {
            $this->_saveFiles($variant, $form);
        }
    }

    public function update($form)
    {
        $variant = $this->getService('TaskVariant')->update(
            array(
                'variant_id' => $form->getValue('variant_id', ''),
                'name' => $form->getValue('name', ''),
                'description' => $form->getValue('description', ''),
            )
        );

        if ($variant) {
            $this->_saveFiles($variant, $form);
        }
    }
    
    public function deleteAction()
    {
        $id = $this->_getParam('variant_id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }
    

    public function delete($id)
    {
        $this->getService('TaskVariant')->delete($id);
    }
}