<?php

class Subject_ExtrasController extends HM_Controller_Action_Subject
{
    public function init()
    {
        parent::init();

        $this->view->replaceSidebar('subject', 'subject-extras', [
            'model' => $this->_subject,
            'order' => 100, // после Subject
        ]);
    }

    public function createByMaterialAction()
    {
        $subjectId = $this->getParam('subject_id');
        $materials = $this->_getParam('postMassIds_grid');
        $materials = explode(',', $materials);

        foreach ($materials as $material) {
            $materialId = strtok($material, '-');
            $materialType = (int) strtok('-');

            if(HM_Event_EventModel::TYPE_RESOURCE == $materialType) {
                /** @var HM_Subject_Resource_ResourceService $subjectResourceService */
                $subjectResourceService = $this->getService('SubjectResource');
                $subjectResourceService->link($materialId, $subjectId);
            }
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Дополнительные материалы успешно созданы'),
        ));

        $this->_redirector->gotoUrl($this->view->url([
            'module'     => 'subject',
            'controller' => 'lessons',
            'action'     => 'edit',
            'subject_id' => $subjectId,
        ], null, true));
    }
}
