<?php
class Forum_MessagesController extends HM_Controller_Action_Activity
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
        $this->_form = new HM_Form_Message();

        $forumId = (int) $this->_getParam('forum_id', HM_Forum_ForumModel::DEFAULT_FORUM);
        $sectionId = (int) $this->_getParam('section_id');

        try {

            $this->forum = $this->forumService->getForum($forumId, $sectionId);

        } catch (Exception $e) {
            $this->_flashMessenger->addMessage($e->getMessage());
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        // Для занятий типа Форум скрытность устанавливается при создании занятия
        // Пока решили вообще убрать галку Скрытое сообщение
        //if ($this->forum->section->lesson_id > 0) {
            $this->_form->removeElement('is_hidden');
        //}

        parent::init();
        
    }
    
    public function indexAction()
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();

        $this->view->forum = $this->forum;

        if ($this->forum->section->lesson_id > 0) { // Возврат в план занятий
            $isEndUser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
            $backUrl = [
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => $isEndUser ? 'index' : 'edit',
                'subject_id' => $this->forum->subject_id,
            ];
        } else {
            $backUrl = [
                'module' => 'forum',
                'controller' => 'sections',
                'action' => 'index',
                'forum_id' => $this->forum->forum_id,
                'subject_id' => $this->forum->subject_id > 0 ? $this->forum->subject_id : null,
            ];
        }

        $this->view->setBackUrl($this->view->url($backUrl, null, true));
        $this->view->setHeader($this->forum->section->title);

        $form = $this->_getForm();
        $form->setAction($this->view->url(['module' => 'forum', 'controller' => 'messages', 'action' => 'new']));

        $this->view->form = $form;

        $elFinderDir = $this->getService('Storage')->createUserDirIfNotExists($currentUserId);
        $this->view->tiny_mce_target_hash = $elFinderDir->hash;
    }

    /**
     * @deprecated - c ajax используется createAction(), этот пока оставлен для обр. совместимости
     */
    public function newAction()
    {
        $form = $this->_getForm();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($form->isValid($request->getPost())) {

            $message = [
                'text' => $form->getValue('text'),
                'is_hidden' => $form->getValue('is_hidden', 0),
                'answer_to' => $this->_getParam('answer_to', 0),
            ];

            $message = $this->forumService->addMessage($message, $this->forum, $this->forum->section);

            // todo: а не лучше ли это держать в сервисе?
            // отправка уведомлений
            if ($message) {

                $parentMsg = $this->getService('ForumMessage')->getOne($this->getService('ForumMessage')->fetchAll(array('message_id=?' => $message->answer_to)));
                $section = $this->getService('ForumSection')->getOne($this->getService('ForumSection')->fetchAll(array('section_id=?' => $message->section_id)));
                $forum     = $this->getService('ForumForum')->getOne($this->getService('ForumForum')->fetchAll(array('forum_id=?' => $message->forum_id)));

                if ($forum && $section) {

                    $messageParam = array(
                        'MESSAGE_USER_NAME' => $message->user_name,
                        'SECTION_NAME'      => ($section && $section->title)? $section->title : '',
                        'FORUM_NAME'        => ($forum && $forum->title)? $forum->title : '',
                        'MESSAGE_URL'       => $this->view->serverUrl($section->url($forum->subject_id && $section->lesson_id ? array(HM_Controller_Action_Activity::PARAM_CONTEXT_ID => $forum->subject_id) : array()))
                    );

                    $messenger = $this->getService('Messenger');

                    // получение списка пользователей подписанных на уведомления
                    // по данному заданию подписка может осуществляться только на занятия, с глобального форума и СВ уведомления не слать
                    if ($section->lesson_id) {
                        $subscribeUsers = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAllDependence('Subscription',array('lesson_id=?'=>$section->lesson_id)));
                        if ($subscribeUsers && count($subscribeUsers->subscriptions)){
                            $subscribeUsers = $subscribeUsers->subscriptions->getList('user_id');
                        } else {
                            $subscribeUsers  = array();
                        }
                    }

                    if ($message->is_hidden) {
                        // уведомление о скрытом сообщение
                        $messenger->setOptions( HM_Messenger::TEMPLATE_FORUM_NEW_HIDDEN_ANSWER, $messageParam);
                        if ($message->answer_to && $parentMsg ) {
                            // шлется только автору родительского сообщения
                            if (in_array($parentMsg->user_id, $subscribeUsers)) {
                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $parentMsg->user_id);
                            }
                        } else {
                            // если нет родительского - сообщение шлется автору темы
                            if (in_array($section->user_id, $subscribeUsers)) {
                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $section->user_id);
                            }
                        }
                    } else {
                        // уведомления об обычном сообщении
                        $messenger->setOptions( HM_Messenger::TEMPLATE_FORUM_NEW_ANSWER, $messageParam);

                        if ($section->lesson_id) {
                            //шлются всем студентам на занятии
                            $data = $this->getService('LessonAssign')->fetchAll(array('SHEID=?' => $section->lesson_id));
                        } elseif ($forum->subject_id) {
                            // или всем слушателям на курсе
                            $data = $this->getService('Student')->fetchAll(array('CID=?' => $forum->subject_id));
                        }

                        $students = (count($data))? array_unique($data->getList('MID')) : array();
                        foreach($students as $studentID) {
                            if (!$studentID || !in_array($studentID, $subscribeUsers)) continue;
                            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentID);
                        }

                        // а так же автору темы
                        if (in_array($section->user_id, $subscribeUsers) && !in_array($section->user_id, $students)) {
                            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $section->user_id);
                        }
                    }
                }
            }
        }
        else {
            // todo: Валидацию на непустое сообщения на фронте
            $this->_flashMessenger->addMessage(
                [
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Текст сообщения не может быть пустым')
                ]);
        }

        $this->_redirectToSection();
    }

    public function createAction()
    {
        if (!$this->isAjaxRequest()) return;

        // Создание сообщений возможно только в темах
        if (!isset($this->forum->section) || !$this->forum->section->flags->theme) return;

        // Нельзя создавать сообщения в закрытых темах
        if ($this->forum->section->flags->closed) return;

        $form = $this->_getForm();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($form->isValid($request->getPost())) {

            $messageData = [
                'text' => $form->getValue('text'),
                'is_hidden' => $form->getValue('is_hidden', 0),
                'answer_to' => $this->_getParam('answer_to', 0),
            ];

            // HM_Forum_Message_MessageModel $message
            $message = $this->forumService->addMessage($messageData, $this->forum, $this->forum->section);

            if (!empty($message)) {
                $message = $this->forumService->getMessage($message->message_id); // addMessage() не все свойства объекта возвращает
                $plainMessage = self::messagePlainify($message, true);
                $this->_responseToAjax([
                    'success' => 1,
                    'message' => $plainMessage,
                    'error_message' => ''
                ]);
            } else {
                $this->_responseToAjax([
                    'success' => 0,
                    'message' => new stdClass(),
                    'error_message' => _('При создании сообщения произошла ошибка')
                ]);
            }
        } else {
            $this->_responseToAjax([
                'success' => 0,
                'message' => new stdClass(),
                'error_message' => _('Текст сообщения не может быть пустым')
            ]);
        }
    }

    public function editAction()
    {
        if (!$this->isAjaxRequest()) return;

        // Нельзя редактировать сообщения в закрытых темах
        if ($this->forum->section->flags->closed) return;

        $messageId = (int) $this->_getParam('message_id');

        if (!$messageId) return;
        // HM_Forum_Message_MessageModel $sourceMessage
        $sourceMessage = $this->forumService->getMessage($messageId);

        // Править сообщения могут модераторы и авторы
        if (!$this->forum->moderator
            && $this->getService('User')->getCurrentUserId() != $sourceMessage->user_id) {
            $this->_responseToAjax([
                'success' => 0,
                'message' => new stdClass(),
                'error_message' => _('Недостаточно прав для редактирования сообщения')
            ]);
        }

        $form = $this->_getForm();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($form->isValid($request->getPost())) {

            $messageData = [
                'text' => $form->getValue('text'),
                'is_hidden' => $form->getValue('is_hidden', 0),
            ];

            // HM_Forum_Message_MessageModel $message
            $message = $this->forumService->editMessage($messageId, $messageData);

            if (!empty($message)) {
                $plainMessage = self::messagePlainify($message);
                $this->_responseToAjax([
                    'success' => 1,
                    'message' => $plainMessage,
                    'error_message' => ''
                ]);
            } else {
                $this->_responseToAjax([
                    'success' => 0,
                    'message' => new stdClass(),
                    'error_message' => _('При обработке сообщения произошла ошибка')
                ]);
            }
        } else {
            $this->_responseToAjax([
                'success' => 0,
                'message' => new stdClass(),
                'error_message' => _('Текст сообщения не может быть пустым')
            ]);
        }
    }

    public function deleteAction()
    {
        if (!$this->isAjaxRequest()) return;

        // Нельзя удалять сообщения в закрытых темах
        if ($this->forum->section->flags->closed) return;

        $messageId = (int) $this->_getParam('message_id');
        $sectionId = (int) $this->_getParam('section_id');

        if (!$messageId || !$sectionId) return;

        $sourceMessage = $this->forumService->getMessage($messageId);

        // Удалять сообщения могут модераторы и авторы
        if (!$this->forum->moderator
            && $this->getService('User')->getCurrentUserId() != $sourceMessage->user_id) {
            $this->_responseToAjax([
                'success' => 0,
                'message' => new stdClass(),
                'error_message' => _('Недостаточно прав для удаления сообщения')
            ]);
        }

        // Получить сообщения с ответами можно только из темы...
        $allSectionMessages =  $this->forumService->getMessagesList($sectionId);
        // Массив сообщений-ответов на данное
        $answers = $this->_findAnswers($allSectionMessages, $messageId);
        $messagesIds = array_merge([$messageId], array_keys($answers));

        $this->forumService->deleteMessageFromDb($messagesIds, $sectionId); // Удаляем из БД и уменьшаем счетчик сообщений в теме

        $this->_responseToAjax([
            'success' => 1,
            'message' => new stdClass(),
            'error_message' => ''
        ]);
    }

    private function _findAnswers($messages, $searchInd) {
        $result = [];
        foreach ($messages as $key => $message) {
            $answers = $message->getAnswers(true);
            if ($key == $searchInd) {
                $result = $answers;
            }
            $result = $result + $this->_findAnswers($answers, $searchInd);
        }
        return $result;
    }

    private function _responseToAjax (array $data)
    {
        $this->_helper->layout->setLayout('ajax');
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->json($data);
        exit;
    }

    static public function messagePlainify(HM_Forum_Message_MessageModel $message, $isNew = false)
    {
        $plainData = $message->getData();
        $date = new HM_Date($message->created);
        $plainData['created'] = $date->toString('dd.MM.yyy HH:mm');
        $plainData['answers'] = (object) []; //(object) $message->getAnswersTree(Секция);
        $plainData['is_hidden'] = (int) $message->is_hidden;
        $plainData['level'] = (int) $message->level;
        $plainData['new'] = $isNew; // новое только при создании
        $plainData['user_photo'] = Zend_Registry::get('serviceContainer')->getService('User')->getPhoto((int) $message->user_id);
        return $plainData;
    }

    static public function indexPlainify($data = array())
    {
        $forumSectionService = Zend_Registry::get('serviceContainer')->getService('ForumSection');
        $plainData = [
            'section' => $data['forum']->section->getData(),
            'form' => $data['form']->render()
        ];

        $date = new HM_Date($plainData['section']['created']);
        $plainData['section']['created'] = $date->toString('dd.MM.yyy HH:mm');

        unset($plainData['section']['flags']);

        $plainData['section']['forum'] = $data['forum']->getData();
        unset($plainData['section']['forum']['config']);
        unset($plainData['section']['forum']['section']);

        $plainData['section']['parent'] = $data['forum']->section->parent ? $data['forum']->section->parent->getData() : null;
        $plainData['section']['messages'] = [];

        /** @var HM_Forum_Message_MessageModel $message */
        foreach ($data['forum']->section->messages as $messageId => $message) {
            if ($forumSectionService->isVisibleMessage($data['forum']->section, $message)) {
                $plainData['section']['messages'][$messageId] = $message->getData();
                $date = new HM_Date($message->created);
                $plainData['section']['messages'][$messageId]['created'] = $date->toString('dd.MM.yyy HH:mm');
                $plainData['section']['messages'][$messageId]['answers'] = (object) $message->getAnswersTree($data['forum']->section);
            }
        }

        if (empty($plainData['section']['messages'])) $plainData['section']['messages'] = (object) [];

        return $plainData;
    }

    private function _redirectToSection()
    {
        $this->_redirector->gotoUrl(
            $this->view->url(['action' => 'index', 'forum_id' => $this->forum->forum_id, 'section_id' => $this->forum->section->section_id])
        );
    }

}
