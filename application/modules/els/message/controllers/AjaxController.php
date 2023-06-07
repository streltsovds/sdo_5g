<?php

class Message_AjaxController extends HM_Controller_Action
{

    public function init() {
        parent::init();

        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('get-personal-messages', 'json')
            ->initContext('json');
    }

    public function getPersonalMessagesAction()
    {
        $date = date('Y-m-d H:i:s');

        $view = $this->view;

        $lastLoad = $this->_getParam('lastLoad', false);
        $clientTime = $this->_getParam('clientTime', false);

        $deltaTime = 0;
        if ($clientTime) {
            $deltaTime = strtotime($date) - strtotime($clientTime);
        }

        $userService = $this->getService('User');
        $currentUserId = intval($userService->getCurrentUserId());

        $select = $this->getService('Message')->getSelect();

        $select->from(
            array('m' => 'messages'),
            array(
                'm.from',
                'm.to',
                'm.created',
//[ES!!!] //                'e.description',
//[ES!!!] //                'e.event_id',
//[ES!!!] //                'eu.views',
            )
        );
/*
        $select->joinInner(
            array('e' =>'es_events'),
            $userService->quoteInto(
                'e.event_trigger_id = m.message_id AND e.event_type_id = ?',
                Es_Entity_AbstractEvent::EVENT_TYPE_PERSONAL_MESSAGE_SEND
            ),
            array()
        );

        $select->joinLeft(
            array('eu' =>'es_event_users'),
            'e.event_id = eu.event_id',
            array()
        );
*/
        if ($lastLoad) {
            $select->where('m.created >= ?', date('Y-m-d H:i:s', strtotime($lastLoad)));
        }

        $select->where('(m.from = ?', $currentUserId);
        $select->orWhere('m.to = ?)', $currentUserId);

        $select->order('m.created ASC');

        $data = array();
        $result = $select->query()->fetchAll();

        $userIds = array();
        foreach($result as $row) {
            $userIds[] = $row['from'];
            $userIds[] = $row['to'];
        }

        $userIds = array_unique($userIds);
        $users = $userService->find($userIds)->asArrayOfObjects();

        foreach($result as $row) {
            $authorId = $row['from'];
            $recipientId = $row['to'];

            $authorExists = array_key_exists($authorId, $users);
            $recipientExists = array_key_exists($recipientId, $users);
            //если автор или получатель не найдены - убираем
            if (!$authorExists || !$recipientExists) {
                continue;
            }

            /** @var HM_User_UserModel $recipient */
            $recipient = $users[$recipientId];
            /** @var HM_User_UserModel $author */
            $author = $users[$authorId];

            $message = json_decode($row['description']);

            // тут похоже баг, в description что-то не то с датами, берём из таблицы messages
            $message->created = date('Y-m-d H:i:s', strtotime($row['created']) - $deltaTime);

            $isMy = ($currentUserId == $authorId);
            $message->is_my = $isMy;
            $message->viewed = !$isMy && !!$row['views'];

            $message->recipient_id = $recipientId;
            $message->recipient_name = $recipient->getName();
            $message->recipient_avatar = $recipient->getPhoto();

            $message->author_id = $authorId;
            $message->author_name = $author->getName();
            $message->author_avatar = $author->getPhoto();

            $message->event_id = $row['event_id'];

            $data[] = $message;
        }

        $view->assign(array(
            'lastLoad' => $date,
            'data' => $data
        ));
    }

    public function pmAction() {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $message = $this->_getParam('message', false);
            $userId = $this->_getParam('userId', false);
            $currentUserId = $this->getService('User')->getCurrentUserId();

            if ($message && $userId) {
                /** @var HM_Messenger $messenger */
                $messenger = $this->getService('Messenger');
                $messenger->setTemplate(HM_Messenger::TEMPLATE_PRIVATE);
                $messenger->send(
                    $currentUserId,
                    $userId,
                    array(
                        'TEXT' => $message,
                    )
                );
            }

        } else {
            $form = new HM_Form_AjaxMessage();
            $this->view->form = $form;
        }
    }

    public function lessonCallbackAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        //$this->getHelper('viewRenderer')->setNoRender();
        $lessonId = $this->_getParam('lesson_id', 0);
        $form = new HM_Form_AjaxMessage();
        $form->getElement('message')->setAttribs(
            array('cols' => '55','rows' => '16')
        );
        $this->view->success = false;
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {
                $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
                if ($lesson) {
                    $message = $form->getValue('message');
                    $messenger = $this->getService('Messenger');
                    $messenger->setTemplate(HM_Messenger::TEMPLATE_PRIVATE);
                    $messenger->send($this->getService('User')->getCurrentUserId(),
                        $lesson->createID,
                        array('TEXT' => _('Обратная связь по занятию '.$lesson->title.': ').$message,
                        )
                    );
                }
                $this->view->success = true;
            }
        }
        $view = $this->view;
        $view->form = $form;
    }


    public function messagesReadedAction() {
        $userId = $this->getService('User')->getCurrentUserId();
        $request_data = $this->getJsonParams();

        $read = $request_data['messages'];
        $this->getService('Message')->updateWhere(
            array('readed' => 1),
            array('to = ?' => $userId)
        );
        $result = true;
        $this->sendAsJsonViaAjax($result);
    }


    public function getUserEventsAction() {

        $limit = $this->getService('Option')->getOption('maxUserEvents');
        $select = $this->getSelect();
        $rowset = $select->limit($limit ?: 15)->query()->fetchAll();

        $result['events'] = array_values($rowset);
        foreach($result['events'] as &$event) {
            $event['create_time'] = date('d.m.Y H:i', strtotime($event['create_time']));
            if(!$event['from']) {
                $event['from'] = _('Системное сообщение');
                $event['senderPhotoUrl'] = '/images/content-modules/icon_logo.png';
            } else {
                $event['senderPhotoUrl'] = $this->getService('User')->getPhoto($event['MID']);
            }
            unset($event['MID']);
        }

        $this->sendAsJsonViaAjax($result);
    }

    protected function getSelect() {

        $select = $this->getService('User')->getSelect();
        $select->from(
            array(
                'm' => 'messages'
            ),
            array(
                'message_id' => 'm.message_id',
                'subject' => 'm.theme',
                'description' => 'm.message',
                'create_time' => 'm.created',
                'readed' => 'm.readed',
                'from' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'MID' => 'p.MID',
            ))
            ->joinLeft(array('p' => 'People'), 'p.MID = m.from', array())

            ->where('m.to = ?', $this->getService('User')->getCurrentUserId())
//            ->where('m.readed IS NULL OR m.readed = ?', 0)
            ->order('create_time DESC')
        ;

        return $select;
    }

}