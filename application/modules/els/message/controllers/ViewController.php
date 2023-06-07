<?php

class Message_ViewController extends HM_Controller_Action_Activity
{

    private $_users = [];

    public function systemAction()
    {
        $this->view->setSubHeader(_('Все сообщения'));

        $subject = $this->_getParam('subject', false);
        $subjectId = (int)$this->_getParam('subject_id', 0);

        /** @var HM_Forum_Message_MessageService $messageService */
        $messageService = $this->getService('Message');

        $select = $messageService->getSelect();
        $currentUserId = $this->getService('User')->getCurrentUserId();

        $select->from(['t1' => 'messages'], ['tempss' => 'from', 'from', 'toWhom' => 'to', 'message_id', 'created', 'message'])
            ->where('(t1.from = ' . $currentUserId . ' OR t1.to = ' . $currentUserId . ')')
            ->order('created DESC');

        // в глобальном сервисе (через гл.меню) показываем вообще все мои сообщения
        if ($subject && $subjectId) {
            $select
                ->where('subject = ?', $subject)
                ->where('subject_id = ?', $subjectId);
        }

        // Не знаю почему, но если использовать
        // "to" , то вместо От отображается "-", пришлось to заменить на toWhom
        $grid = $this->getGrid(
            $select, [
            'message_id' => ['hidden' => true],
            'tempss' => ['hidden' => true],
            'from' => ['title' => _('Тип')],
            'toWhom' => ['title' => _('От/Кому')],
            'message' => ['title' => _('Сообщение'), 'escape' => false],
            'created' => ['title' => _('Дата'), 'format' => 'date']
        ], [
                'message_id' => null,
                'from' => [
                    'values' => [
                        $currentUserId => _('Исходящие'),
                        '!=' . $currentUserId => _('Входящие')
                    ]
                ],
                'message' => null,
                'toWhom' => null,
                'created' => ['render' => 'DateSmart']
            ]
        );

        $grid->updateColumn('message', [
                'callback' => [
                    'function' => [$this, 'getSubString'],
                    'params' => ['{{message}}', '{{message_id}}']
                ]
            ]
        );

        $grid->updateColumn('toWhom', [
                'callback' => [
                    'function' => [$this, 'getUser'],
                    'params' => ['{{tempss}}', '{{toWhom}}']
                ]
            ]
        );

        $grid->updateColumn('from', [
                'callback' => [
                    'function' => [$this, 'getDirection'],
                    'params' => ['{{from}}']
                ]
            ]
        );

        $this->getService('Activity')->initializeActivityCabinet('', 'subject', $subjectId);
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
            $currentUserId
        );

        $this->view->disableMessages = !$isModerator && $this->getService('Option')->getOption('disable_messages');
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->setBackUrl($_SERVER['HTTP_REFERER']);
    }

    public function indexAction()
    {
        $view = $this->view;
        /** @var HM_View_Helper_HM $HM */
        $HM = $view->HM();

        $getNew = $view->url([
            'module' => 'message',
            'controller' => 'ajax',
            'action' => 'get-personal-messages'
        ], null, true);

        $markViewed = $view->url([
            'module' => 'es',
            'controller' => 'events',
            'action' => 'markasviewed'
        ], null, true);

        $HM->create('hm.module.messenger.ui.Messenger', [
            'renderTo' => '#messenger',
            'currentUserId' => $this->getService('User')->getCurrentUserId(),
            'urls' => [
                'getNew' => $getNew,
                'markViewed' => $markViewed,
            ],
        ]);

        $form = new HM_Form_Messenger();

        $view->assign([
            'form' => $form
        ]);
    }

    public function oneAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $messageId = (int)$this->_getParam('message_id', 0);

        /** @var HM_Message_MessageModel $message */
        $message = $this->getService('Message')->find($messageId)->current();

        $this->view->title = $message->theme;

        return $this->view->fields = $this->view->card($message, $message->getCardFields(), [], true);
    }

    public function getUser($from, $to)
    {

        $userId = $to;
        if ($from != $this->getService('User')->getCurrentUserId()) {
            $userId = $from;
        }

        if (!isset($this->_users[$userId])) {
            $user = $this->getService('User')->getOne(
                $this->getService('User')->find($userId)
            );
            if ($user) {
                $this->_users[$user->MID] = $user;
            }
        }

        if (isset($this->_users[$userId])) {
            $tt = '<div>' . $this->view->cardLink($this->view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $userId])) . $this->_users[$userId]->getName() . '</div>';

            return $tt;
        }

        if ($userId == HM_Messenger::SYSTEM_USER_ID) {
            return $this->getService('Option')->getOption('dekanName');
        }

        return sprintf(_('Пользователь #%d удалён'), $userId);
    }

    public function getDirection($from)
    {
        if ($from == $this->getService('User')->getCurrentUserId()) {
            return _('Исходящее');
        }

        return _('Входящее');
    }

    /**
     * get real sub string, removing all formatting from a string anyway, not only when provided string
     * is too long to output as before.
     *
     * @param string $field provided html/text message
     * @param int $id message id
     * @param int $len length of a string cut before pasting read more link, default:300
     *
     * @return string
     */
    public function getSubString($field, $id, $len = 300)
    {
        $field = strip_tags($field, '<a>');
        $field = nl2br($field);
        $field = str_replace('\n', '<br>', $field);

        if (strlen($field) > $len) {
            $subtext = wordwrap($field, $len, "<br/>");
            $res = explode("<br/>", $subtext);
            $url = $this->view->url([
                'module' => 'message',
                'controller' => 'view',
                'action' => 'one',
                'message_id' => $id
            ]);

            $result = $res[0] . "... " . $this->view->cardLink($url, _('Полный текст сообщения'), 'text');
        } else {
            $result = $field;
        }
        // Пришлось завернуть в span, чтобы ячейка грида не превращала содержимое во flex
        return '<span class="grid-message-view-system__message">' . $result . '</span>';
    }

}