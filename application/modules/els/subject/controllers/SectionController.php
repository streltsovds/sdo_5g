<?php

class Subject_SectionController extends HM_Controller_Action_Subject
{
    public function init()
    {
        parent::init();

        $this->_returnUrl = $this->view->url(array(
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => $this->_isEnduser ? 'index' : 'edit',
            'subject_id' => $this->_subjectId,
        ), null, true);
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoUrl($this->_returnUrl);
    }

    public function createAction()
    {
        /** @var HM_Section_SectionService $sectionService */
        $sectionService = $this->getService('Section');

        $lastSection = $sectionService->fetchRow(['subject_id = ?' => $this->_subjectId], 'order desc');
        $order = $lastSection ? $lastSection->order + 1 : 1;

        $section = $sectionService->createSection($this->_subjectId, HM_Section_SectionModel::ITEM_TYPE_SUBJECT, HM_Section_SectionModel::getDefaultName(), $order);

        if ($section) {
            $this->_flashMessenger->addMessage(_('Раздел успешно создан'));
        } else {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Произошла ошибка при создании раздела')
            ]);
        }

        $this->_redirectToIndex();
    }

    public function deleteAction()
    {
        $sectionService = $this->getService('Section');
        $sectionId = $this->_getParam('section_id', 0);

        $section = $sectionService->getOne($sectionService->find($sectionId));

        if ($section) {
            $sectionService->deleteSection($sectionId);

            $this->_flashMessenger->addMessage(_('Раздел успешно удален'));
        } else {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Данного раздела не существует')
            ]);
        }

        $this->_redirectToIndex();
    }
}
