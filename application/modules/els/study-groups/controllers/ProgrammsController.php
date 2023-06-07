<?php

class StudyGroups_ProgrammsController extends HM_Controller_Action
{

    public function assignProgrammAction()
    {
        /** @var HM_Programm_ProgrammService $programmService */
        $programmService = $this->getService('Programm');

        /** @var HM_StudyGroup_Programm_ProgrammService $studyGroupProgrammService */
        $studyGroupProgrammService = $this->getService('StudyGroupProgramm');

        /** @var HM_StudyGroup_StudyGroupService $studyGroupService */
        $studyGroupService = $this->getService('StudyGroup');

        $groupIds = [];
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $groupIds = explode(',', $postMassIds);
        }
        $programmIds = $this->_getParam('programmId', 0);

        if (!count($groupIds)) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ]);
            $this->_redirectToIndex();
        }

        if ($programmIds === 0) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите один или несколько курсов')
            ]);
            $this->_redirectToIndex();
        }

        foreach ($programmIds as $programmId) {

            /* Проверяем существует ли программа */
            $programm = $programmService->getById($programmId);
            if (!$programm) {
                continue;
            }

            /* Перебираем группы */
            foreach ($groupIds as $groupId) {
                $group = $studyGroupService->getById($groupId);
                if ($group) {
                    /* Записываем группу на курс */
                    $studyGroupProgrammService->addProgrammOnGroup($programm->programm_id, $group->group_id);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Программы обучения успешно назначены'));
        $this->_redirectToIndex();
    }

    public function unassignProgrammAction()
    {
        /** @var HM_Programm_ProgrammService $programmService */
        $programmService = $this->getService('Programm');

        /** @var HM_StudyGroup_Programm_ProgrammService $studyGroupProgrammService */
        $studyGroupProgrammService = $this->getService('StudyGroupProgramm');

        /** @var HM_StudyGroup_StudyGroupService $studyGroupService */
        $studyGroupService = $this->getService('StudyGroup');

        $groupIds = [];
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $groupIds = explode(',', $postMassIds);
        }
        $programmIds = $this->_getParam('programmId', 0);

        if (!count($groupIds)) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ]);
            $this->_redirectToIndex();
        }

        if ($programmIds === 0) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите один или несколько курсов')
            ]);
            $this->_redirectToIndex();
        }

        foreach ($programmIds as $programmId) {
            /* Проверяем или существует курс */
            $programm = $programmService->getById($programmId);
            if (!$programm) {
                continue;
            }

            /* Перебираем группы */
            foreach ($groupIds as $groupId) {
                $group = $studyGroupService->getById($groupId);

                if ($group) {
                    /* Отписываем группу с курса */
                    $studyGroupProgrammService->removeGroupFromProgramm($group->group_id, $programm->programm_id);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Программы обучения успешно отменены'));
        $this->_redirectToIndex();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoRoute([
            'module' => 'study-groups',
            'controller' => 'list',
            'action' => 'index'
        ], null, true);
    }
}