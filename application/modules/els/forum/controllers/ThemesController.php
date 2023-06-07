<?php

class Forum_ThemesController extends HM_Controller_Action_Activity
{
    /**
     * @var HM_Forum_ForumService
     */
    protected $forumService;

    /**
     * @var HM_Forum_Forum_ForumModel
     */
    protected $forum;

    public function init()
    {
        $this->forumService = $this->getService('Forum');
        $forumId = (int)$this->_getParam('forum_id', HM_Forum_ForumModel::DEFAULT_FORUM);
        $sectionId = (int)$this->_getParam('section_id', 0);

        $this->_form = new HM_Form_Theme();

        try {

            $this->forum = $this->forumService->getForum($forumId, $sectionId);

        } catch (Exception $e) {
            $this->_flashMessenger->addMessage($e->getMessage());
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        parent::init();
    }

    public function newAction()
    {
        $form = $this->_getForm();

        // Для форумов имеющих разделы создание темы возможно только в разделе
        if ($this->forum->flags->subsections && empty($this->forum->section)) return;

        // Нельзя создать тему в теме
        if ($this->forum->section->flags->theme) return;

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            // Обработка отправленного запроса из формы

            if ($form->isValid($request->getPost())) {
                $section = array(
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text'),
                    'flags' => array('theme' => true),
                    'subject' => $this->_activitySubjectName,
                );

                $section = $this->forumService->createSection($section, $this->forum, $this->forum->section);

                $this->_flashMessenger->addMessage(array(
                    'message' => sprintf(_('Тема «%s» успешно создана'), "<a href=\"{$section->url()}\">" . $form->getValue('title') . "</a>"),
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'hasMarkup' => true,
                ));

                $this->_redirectToTheme($section->section_id);

            } else {
                $this->_flashMessenger->addMessage(
                    [
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Название темы не может быть пустым')
                    ]);
            }
        }

        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        $section = $this->forum->section;
        $userId = $this->getService('User')->getCurrentUserId();

        // Тему могут править модераторы (роли заданы в настройках) или автор
        if (!$this->forum->moderator && $userId != $section->user_id) {
            $this->_flashMessenger->addMessage(
                [
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Недостаточно прав для редактирования темы')
                ]);
            $this->_redirector->gotoUrlAndExit($_SERVER['HTTP_REFERER']);
        }

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $data = [
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text'),
                    'edited_by' => $userId,
                    'edited' => date("Y-m-d H:i:s"),
                ];

                $this->getService('ForumSection')->updateSection($section->section_id, $data);

                $this->_flashMessenger->addMessage(_('Тема успешно изменена'));
                $this->_redirectToTheme($section->section_id);
            } else {
                $this->_flashMessenger->addMessage(
                    [
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Название темы не может быть пустым')
                    ]);
            }
        } else {
            $form->setDefaults($section->getValues());
        }
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        if (empty($this->forum->section)) return;

        // Проверка на соответствие ID форума раздела текущему ID форума
        if ($this->forum->section->forum_id != $this->forum->forum_id) return;

        // Тема форумного занятия отдельно от занятия не удаляется
        if ($this->forum->section->lesson_id > 0) return;

        // Тему могут удалить модераторы (роли заданы в настройках) или автор
        if (!$this->forum->moderator
            && $this->getService('User')->getCurrentUserId() != $this->forum->section->user_id) {
            $this->_flashMessenger->addMessage(
                [
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Недостаточно прав для удаления темы')
                ]);
            $this->_redirector->gotoUrlAndExit($_SERVER['HTTP_REFERER']);
        }

        // Удаляем раздел/тему и все ее дочерние подразделы/сообщения из БД
        $this->forumService->deleteSection($this->forum->section);

        $this->_flashMessenger->addMessage(
            [
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Тема успешно удалена')
            ]);

        $this->_redirector->gotoUrl($this->view->url([
            'module' => 'forum',
            'controller' => 'sections',
            'action' => 'index',
            'forum_id' => $this->forum->forum_id,
            'subject_id'=>$this->_getParam('subject_id'), // для форума курса
            ], null, true));
    }

    private function _redirectToTheme($themeId = 0)
    {
        $params = ['controller' => 'messages', 'action' => 'index'];
        if ($themeId) {
            $params['section_id'] = $themeId;
        }

        $this->_redirector->gotoUrl(
            $this->view->url($params)
        );
    }
}
