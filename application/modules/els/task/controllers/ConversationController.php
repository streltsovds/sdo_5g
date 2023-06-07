<?php

class Task_ConversationController extends HM_Controller_Action_Subject
{

    public function init()
    {
        parent::init();
        $lessonId = $this->_getParam('lesson_id', 0);

        if($this->_subject) {
            $this->_isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
            $this->setActiveContextMenu($this->_isEnduser ? 'mca:subject:lessons:index' : 'mca:subject:lessons:edit');

            if($this->_isEnduser) {
                $this->_returnUrl = $this->view->url([
                    'module' => 'subject',
                    'controller' => 'lessons',
                    'action' => 'index',
                    'subject_id' => $this->_subjectId,
                ], null, true);
            } else {

                $this->_returnUrl = $this->view->url([
                    'module' => 'subject',
                    'controller' => 'results',
                    'action' => 'index',
                    'subject_id' => $this->_subjectId,
                    'lesson_id' => $lessonId,
                ], null, true);
            }


            $this->view->setBackUrl($this->_returnUrl);
        }
    }

    public function indexAction()
    {
        $lesson = null;
        $lessonId = $this->_getParam('lesson_id', 0);
        if ($lessonId) {
            $collection = $this->getService('Lesson')->find($lessonId);
            if (count($collection)) {
                $lesson = $collection->current();
                $this->view->setHeader($lesson->title);
            }
        }
        $isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER));
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $userId = $isEnduser ? $currentUserId : $this->_getParam('user_id', 0);
        $type = $this->_getParam('type', 0);
        /** @var HM_User_UserModel $user */
        $user = $this->getService('User')->findOne($userId);
        if ($user && !$isEnduser) {
            $this->view->setSubSubHeader(_('Результаты занятия')  . " ({$user->getName()})");
        }

        $lessonRow = $this->getService('Lesson')->fetchRow(['SHEID = ?' => $lessonId]);
        $this->view->lesson = HM_Json::encodeErrorSkip($lessonRow->getData());

        if (HM_Event_EventModel::TYPE_TASK == $lessonRow->typeID and
            !empty($lessonRow->material_id)
        ) {
            $taskRow = $this->getService('Task')->fetchRow(['task_id = ?' => $lessonRow->material_id]);

            if($taskRow->created_by)
            /** @var HM_User_UserModel $createdUser */ {

                $createdUser = $this->getService('User')->fetchRow(['MID = ?' => $taskRow->created_by]);

                if ($createdUser) {
                    $taskRow->setValue('created_by', $createdUser->getNameCyr());
                }
            }

            /** @var HM_Task_Conversation_ConversationService $conversationService */
            $conversationService = $this->getService('TaskConversation');
            $taskConversation = $conversationService->getOne($conversationService->fetchAll([
                'lesson_id=?' => $lessonId,
                'user_id=?' => $userId,
                'type=?' => HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK,
            ], 'conversation_id DESC'));

            if ($taskConversation and $taskConversation->variant_id) {
                $taskVariant = $this->getService('TaskVariant')->fetchRow(['variant_id = ?' => $taskConversation->variant_id]);

                $filesRows = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT, $taskVariant->variant_id);
                $files = [];

                foreach ($filesRows as $file) {
                     $files[] = [
                        'id' => $file->getId(),
                        'displayName' => $file->getDisplayName(),
                        'fileName' => $file->getFileName(),
                        'path' => $file->getPath(),
                        'url' => $file->getUrl(),
                        'size' => $file->getSize(),
                        'type' => HM_Files_FilesModel::getFileType($file->getDisplayName()),
                    ];
                }

                if($taskVariant) {
                    $variantData = [
                        'item' => $taskVariant->getData(),
                        'files' => $files,
                    ];
                }
            }
        }

        $this->view->assign([
            'task' => HM_Json::encodeErrorSkip($taskRow ? $taskRow->getData() : []),
            'variant' => HM_Json::encodeErrorSkip($variantData ?: []),
            'lesson_id' => $lessonId,
            'type' => $type,
            'user_id' => $isEnduser ? $this->getService('User')->getCurrentUserId() : $userId,
            'isEnduser' => (int) $isEnduser,
        ]);
    }

    public function getConversationsAction()
    {
        /** @var HM_Task_Conversation_ConversationService $conservationService */
        $conservationService = $this->getService('TaskConversation');

        $lessonId = $this->_getParam('lesson_id', 0);
        $userId = $this->_getParam('user_id', $this->getService('User')->getCurrentUserId());

        $conversations = $conservationService->fetchAll(array('lesson_id=?' => $lessonId, 'user_id=?' => $userId, 'type <> ?' => HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK), 'date');
        $result = array();

        foreach($conversations as $conversation) {
            $conversation->message = strip_tags($conversation->message);
            $result[] = $conservationService->getConversationDataForResp($conversation)->getData();
        }

        $lesson = $this->getService('Lesson')->find($lessonId)->current();
        $taskId = explode('=', $lesson->params);
        $task = $this->getService('Task')->find(intval($taskId[1]))->current();

//        $this->view->task = $task;
        $this->view->conversations = $result;
        return $this->responseJson([
            'conversations' => $result,
        ]);

    }

    public function addConversationAction()
    {
        $lessonId = $this->_getParam('lesson_id', 0);
        $type = $this->_getParam('type', HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK);
        $message = $this->_getParam('message', null);
        $isEnduser = $this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ENDUSER]);

        $teacherId =  $isEnduser ? 0 : $this->getService('User')->getCurrentUserId();
        $userId = $isEnduser ? $this->getService('User')->getCurrentUserId() : $this->_getParam('user_id', 0);

        /** @var HM_Task_Conversation_ConversationModel $conversation */
        $conversation = $this->getService('TaskConversation')->insert(array(
            'lesson_id' => $lessonId,
            'user_id' => $userId,
            'teacher_id' => $teacherId,
            'type' => $type,
            'message' => $message,
            'date' => date('d.m.Y H:i:s')
        ));

        $this->getService('Task')->notifyAboutTask($lessonId, $userId, $type);

        $adapter = new Zend_File_Transfer_Adapter_Http();
        if ($adapter->receive()) {
            $files = $adapter->getFileInfo();
            foreach($files as $name => $file) {
                $this->getService('Files')->addFile(
                    $file['tmp_name'],
                    $file['name'],
                    HM_Files_FilesModel::ITEM_TYPE_TASK_CONVERSATION,
                    $conversation->conversation_id
                );
            }
        }

        if($type == HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ASSESSMENT) {

            $lesson = $this->getService('Lesson')->getLesson($lessonId);
            $mark = $this->_getParam('score', 0);

            /** @var HM_Lesson_Assign_AssignService $lessonAssignService */
            $lessonAssignService = $this->getService('LessonAssign');
            $subjectId = $this->_getParam('subject_id', 0);

            $lessonAssignService->setUserScore(
                (int)$userId,
                (int)$lessonId,
                (int)$mark,
                $subjectId
            );
        }

        return $this->responseJson([
            'conversation' => $this->getService('TaskConversation')->getConversationDataForResp($conversation)->getData(),
        ]);
    }
}