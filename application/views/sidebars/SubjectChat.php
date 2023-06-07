<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_SubjectChat extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'services'; // @todo
    }

    public function getTitle()
    {
        return _('Чат');
    }

    public function getContent()
    {

        $currentUserId = (int) $this->getService('User')->getCurrentUserId();
        $currentUser = $this->getService('User')->getCurrentUser();
        $wsConfig = Zend_Registry::get('config')->websocket;
        $session = $this->getService('Session')->fetchRow(array('sesskey is not null and sesskey <> \'\' and mid = ?' => $currentUserId), 'sessid DESC');

        /** @var HM_Lesson_LessonModel $lesson */
        $lesson = $this->getModel();

        $namespace = $lesson->getChatNamespace();
        /** @var HM_ChatMessage_ChatMessageService $chatMessagesService */
        $chatMessagesService = $this->getService('ChatMessage');
        $lastMessagesFetch = $chatMessagesService->getMessages($namespace)->asArrayOfArrays();

        foreach ($lastMessagesFetch as $lastMessage) {
           $author = $lastMessage['user'] ?$lastMessage['user']->current() : false;
           unset($lastMessage['user']);

           if($author) {
               $lastMessage['user'] = array(
                   'id' => $author->MID,
                   'name' => $author->LastName . ' ' . $author->FirstName . ' ' . $author->Patronymic,
                   'avatarUrl' => $author->getPhoto(),
               );
           }
           $lastMessage['items'] = $this->getService('ChatMessage')->fetchAll(array('message_id = ?' => $lastMessage['message_id']))->asArrayOfArrays();

           $lastMessages[] = $lastMessage;
        }

        $subject = $lesson->subject->current();
        $subject->icon = $subject->getDefaultIcon();
        $subject->image = $subject->getUserIcon();
        $subject->begin = $subject->getBegin();
        $subject->end = $subject->getEnd();
        $subject->isAccessible = $subject->isAccessible();

        $collections = [
            $this->getService('LessonAssign')->fetchAllDependence('User', ['SHEID = ?' => $lesson->SHEID]),
            $this->getService('Teacher')->fetchAllDependence('User', ['CID = ?' => $subject->subid])->asArrayOfObjects(),
            $this->getService('Dean')->fetchAllDependence('User')->asArrayOfObjects()
        ];

        $users = [];

        foreach ($collections as $collection) {
            foreach ($collection as $roleUser) {
                if (count($roleUser->users)) {
                    $user = $roleUser->users->current();
                    $users[$user->MID] = $user->getName();
                }
            }
        }

        $initialStoreState = [
            'messages' => $lastMessages,
            'wsConfig' => [
                'port' => $wsConfig->port,
                'host' => $wsConfig->host,
                'ssl' => $wsConfig->ssl,
                'sessionId' => $session->sesskey,
                'namespace' => $namespace,
            ],
            'user' => [
                'id' => $currentUser->MID,
                'name' => $currentUser->LastName .' '. $currentUser->FirstName .' '. $currentUser->Patronymic,
                'avatarUrl' => $currentUser->getPhoto(),
            ],
            'api' => [
              'subject_id' => $subject->subid,
              'lesson_id' => $lesson->SHEID,
            ]
        ];
        $initialStoreState = HM_Json::encodeErrorSkip($initialStoreState);

        $data = [
            'lesson' => $lesson,
            'subject' => $subject,
            'users' => $users,
        ];

        $data['editUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'list', 'action' => 'edit', 'subid' => $subject->subid]);
        $data['showEdit'] = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]);
        $data['currentUser'] = $this->getService('User')->getCurrentUser();

        $data['subjectPlanUrl'] = $this->view->url([
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => 'index',
            'subject_id' => $subject->subid
        ]);

        $data = HM_Json::encodeErrorSkip($data);

        return $this->view->partial('subject/chat.tpl', [
            'data' => $data,
            'initialStoreState' => $initialStoreState,
        ]);
    }
}