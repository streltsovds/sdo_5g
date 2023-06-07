<?php

class Subject_ExtraController extends HM_Controller_Action_Subject
{
    public function init()
    {
        parent::init();

        $this->view->replaceSidebar('subject', 'subject-extras', [
            'model' => $this->_subject,
            'order' => 100, // после Subject
        ]);
    }

    public function createAction()
    {
        $this->view->setSubSubHeader(_('Создание дополнительного материала'));

        $form = new HM_Form_ExtraMaterial();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $convertToPdf = $request->getParam('file_convertToPdf');

            if ($form->isValid($request->getParams())) {
                $title = $form->getValue('title') ? : _('[Без названия]');
                $createType = $this->_getParam('create_type');
                $materials = [];

                switch ($createType) {
                    case HM_Form_LessonMaterial::CREATE_TYPE_AUTODETECT:
                        if ($insertValue = $form->getValue('code')) {
                            // пока отключено
                            $materials[] = $this->getService('Material')->importResource($insertValue, null, $title, $this->_subjectId, null, $convertToPdf);
                        } else {

                            $fileElement = $form->getElement('file');
                            $fileElement->receive();

                            $filename = $fileElement->getFileName();
                            $pathinfo = pathinfo($filename);

                            $title = $form->getValue('title') ?: $pathinfo['filename'];

                            if ($fileElement->isReceived()) {
                                $insertValue = realpath($filename);
                                $materials[] = $this->getService('Material')->importResource($insertValue, $pathinfo['basename'], $title, $this->_subjectId, null, $convertToPdf);
                            }
                        }
                        break;
                    case HM_Form_LessonMaterial::CREATE_TYPE_MATERIAL:
                        // создать в конструкторе
                        $materials[] = $this->getService('Material')->createDefault(
                            HM_Event_EventModel::TYPE_RESOURCE,
                            $title,
                            $this->_subjectId
                        );
                        break;
                    default:
                        // вкладки 2 и 3
                        $subjectMaterialIdTypes = $this->_getParam('subject_material_id_type');
                        $kbMaterialIdTypes = $this->_getParam('kb_material_id_type');
                        $materialIdTypes = $subjectMaterialIdTypes ?: $kbMaterialIdTypes;

                        // в kbase мы можем выбирать чекбоксом несколько материалов сразу
                        if(!is_array($materialIdTypes)) {
                            $materialIdTypes = [$materialIdTypes];
                        }

                        foreach ($materialIdTypes as $materialIdType) {

                            $materialId = strtok($materialIdType, '-');
                            $materialKbaseType = strtok('-');
                            $materialEventType = HM_Kbase_KbaseModel::getKbaseAndEventTypesMap($materialKbaseType);

                            /** @var HM_Material_MaterialService $materialService */
                            $materialService = $this->getService('Material');
                            $materials[] = $materialService->findMaterial($materialId, $materialEventType);
                        }
                }

                if ($materials) {

                    foreach ($materials as $material) {
                        $materialId = $this->getService('Material')->getMaterialId($material);
                        $this->getService('SubjectResource')->link(
                            $materialId,
                            $this->_subjectId,
                            'subject'
                        );
                    }

                    $this->_flashMessenger->addMessage(
                        count($materials) == 1
                            ? _('Материал успешно создан')
                            : _('Материалы успешно созданы')
                    );
                } else {
                    $this->_flashMessenger->addMessage([
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Произошла ошибка при создании материла')
                    ]);
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    // todo: lesson?
                    if (!strpos($redirectUrl, 'lesson_id')) $redirectUrl = sprintf('%s/lesson_id/%d', trim($redirectUrl, "/"), $lesson->SHEID);
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                } elseif (($createType == HM_Form_LessonMaterial::CREATE_TYPE_MATERIAL)) {
                    // к конструктору материалов
                    $this->_redirector->gotoUrl($this->view->url([
                        'module' => 'kbase',
                        'controller' => 'resource',
                        'action' => 'edit',
                        'resource_id' => $materialId,
                    ]));
                } else {
                    $this->_redirectToIndex();
                }
            }
        }

        $this->view->form = $form;
    }

    // DEPRECATED ?
    public function deleteAction()
    {
        $subjectId = $this->_getParam('subject_id');
        $resourceId = $this->_getParam('resource_id');

        if($subjectId and $resourceId) {
            /** @var HM_Subject_Resource_ResourceService $subjectResourceService */
            $subjectResourceService = $this->getService('SubjectResource');
            $subjectResourceService->unlink($resourceId, $subjectId);

            $this->_flashMessenger->addMessage(_('Дополнительный материал успешно удалён'));
        }

        $this->_redirectToIndex();
    }

    public function unlinkAction()
    {
        $resourceId = $this->_request->getParam('resource_id');
        $subjectId = $this->_request->getParam('subject_id');

        $deleted = $this->getService('SubjectResource')->unlink($resourceId, $subjectId);

        if ($deleted) {
            $this->_flashMessenger->addMessage(_('Дополнительный материал успешно удален'));
        }

        $this->_redirectToIndex();
    }

    protected function _redirectToIndex()
    {
        $isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
        $this->_redirector->gotoUrl($this->view->url([
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => $isEnduser ? 'index' : 'edit',
            'subject_id' => $this->_subjectId,
        ], null, true));
    }
}
