<?php

class Forum_SectionsController extends HM_Controller_Action_Activity
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
        $sectionId = (int)$this->_getParam('section_id');

        $this->_form = new HM_Form_Section();

        try {

            $this->forum = $this->forumService->getForum($forumId, $sectionId);

        } catch (Exception $e) {
            $this->_flashMessenger->addMessage($e->getMessage());
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        parent::init();
    }

    public function indexAction()
    {
        if ($this->forum->subject_id > 0) {// Для форума курса
            $this->view->setHeader($this->forum->title);
            $this->view->setSubHeader('');
            if (empty($this->view->isEndUser)) {
                $backUrl = [
                    'module' => 'subject',
                    'controller' => 'list',
                    'action' => 'index',
                    'base' => 0,
                ];
            } else {
                $backUrl =  [
                    'module' => 'subject',
                    'controller' => 'my',
                    'action' => 'index',
                ];
            }
            $this->view->setBackUrl($this->view->url($backUrl, null, true));
        }
        $this->view->forum = $this->forum;
    }


    public function newAction()
    {
        // Форум должен допускать существование разделов
        if (!$this->forum->flags->subsections) return;

        $form = $this->_getForm();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($request->isPost()) // Обработка отправленного запроса из формы
        {
            if ($form->isValid($request->getPost())) {
                $section = [
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text')
                ];

                $this->forumService->createSection($section, $this->forum);

                $this->_flashMessenger->addMessage([
                    'message' => sprintf(_('Раздел «%s» успешно создан'), $form->getValue('title')),
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                ]);

                $this->_redirectToForum();

            } else {
                // todo: Валидацию на непустое сообщения на фронте
                $this->_flashMessenger->addMessage(
                    [
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Название раздела не может быть пустым')
                    ]);
            }
        }

        $this->view->form = $form;
    }

    public function editAction()
    {
        if (!$this->isAjaxRequest()) return;

        $form = $this->_getForm();
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $data = [
                    'section_id' => $form->getValue('section_id'),
                    'title' => $form->getValue('title'),
                    'edited_by' => $this->getService('User')->getCurrentUserId(),
                    'edited' => date("Y-m-d H:i:s"),
                ];

                $result = $this->getService('ForumSection')->update($data);

                if ($result) {
                    $this->responseJson([
                        'status' => 'success'
                    ]);
                } else {
                    $this->responseJson([
                        'status' => 'error'
                    ]);
                }
            } else {
                $this->responseJson([
                    'status' => 'error'
                ]);
            }
        }
    }

    public function deleteAction()
    {
        // Удаление раздела возможно только при нахождении в разделе
        if (!isset($this->forum->section)) return;

        // Проверка на соответствие ID форума раздела текущему ID форума
        if ($this->forum->section->forum_id != $this->forum->forum_id) return;

        // Невозможно удалить дефолтный раздел форума
        if ($this->forum->section->section_id == 1
            && $this->forum->section->forum_id == 1) return;

        // Удаляем раздел и все его дочерние темы/сообщения из БД
        $this->forumService->deleteSection($this->forum->section);

        $this->_flashMessenger->addMessage([
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Раздел успешно удалён')
        ]);

        $this->_redirectToForum();
    }


    static public function indexPlainify($data = array())
    {
        $aclService = Zend_Registry::get('serviceContainer')->getService('Acl');
        $userService = Zend_Registry::get('serviceContainer')->getService('User');

        $canEditCategories = (int)$aclService->inheritsRole(
            $userService->getCurrentUserRole(), [
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_HR,
        ]);

        $plainData = [
            'forum' => $data['forum']->getData(),
        ];

        unset($plainData['forum']['config']);
        $plainData['forum']['sections'] = [];
        $plainData['forum']['editable'] = $canEditCategories;

        /** @var HM_Forum_Section_SectionModel $section */
        foreach ($data['forum']->sections as $sectionId => $section) {

            $plainData['forum']['sections'][$sectionId] = $section->getData();
            $plainData['forum']['sections'][$sectionId]['editable'] = $canEditCategories;
            $plainData['forum']['sections'][$sectionId]['subsections'] = [];

            /** @var HM_Forum_Section_SectionModel $subsection */
            foreach ($section->subsections as $subsectionId => $subsection) {
                $plainData['forum']['sections'][$sectionId]['subsections'][$subsectionId] = $subsection->getData();
                $date = new HM_Date($subsection->created);
                $plainData['forum']['sections'][$sectionId]['subsections'][$subsectionId]['created'] = $date->toString('dd.MM.yyy HH:mm');

                // Дата последнего сообщения в теме
                $lastMsgDate = new HM_Date($subsection->last_msg);
                if ($lastMsgDate->isToday()) $lastMsg = _('Сегодня ') . $lastMsgDate->toString('HH:mm');
                elseif ($lastMsgDate->isYesterday()) $lastMsg = _('Вчера ') . $lastMsgDate->toString('HH:mm');
                else $lastMsg = $lastMsgDate->toString('dd.MM.yyy HH:mm');
                $plainData['forum']['sections'][$sectionId]['subsections'][$subsectionId]['last_msg'] = $lastMsg;
            }
        }

        if (empty($plainData['forum']['sections'])) $plainData['forum']['sections'] = (object) [];

        return $plainData;
    }

    private function _redirectToForum()
    {
        $this->_redirector->gotoUrl(
            $this->view->url(['module' => 'forum', 'controller' => 'sections', 'action' => 'index', 'forum_id' => $this->forum->forum_id], null, true)
        );
    }
}
