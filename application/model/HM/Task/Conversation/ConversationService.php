<?php

class HM_Task_Conversation_ConversationService extends HM_Service_Abstract
{
    public function assignUser($lessonId, $taskId, $userId, $variantId=false) {

        $conversations = $this->getService('TaskConversation')->fetchAll(array('lesson_id = ?'=>$lessonId, 'user_id = ?'=>$userId,'type=?'=>HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK,));
        if (count($conversations)) return;

        if (!$variantId) {
            $variants = array_values($this->getService('TaskVariant')->fetchAll(array('task_id = ?'=>$taskId))->getList('variant_id'));
            if (!count($variants)) return;
            $variantId = $variants[count($variants)>1 ? rand(0, (count($variants) - 1)) : 0];
        }

        $conversation = $this->addConversationWithVariant($lessonId, $userId, $variantId);

        $files = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT, $variantId);
        foreach($files as $file) {
            $this->getService('Files')->addFile($this->getService('Files')->getPath($file->file_id), $file->name, HM_Files_FilesModel::ITEM_TYPE_TASK_CONVERSATION, $conversation->conversation_id);
        }
    }

    public function addConversationWithVariant($lessonId, $userId, $variantId)
    {
        $variant = $this->getService('TaskVariant')->find($variantId)->current();
        $data = array(
            'type'=>HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK,//MESSAGE_TYPE_QUESTION
            'variant_id'=>$variantId,
            'message'=> (!is_null($variant) && isset($variant->description)) ? $variant->description : '',
            'lesson_id'=>$lessonId,
            'user_id'=>$userId,
            'teacher_id'=>$this->getService('User')->getCurrentUserId(),
            'date'=>date('Y-m-d H:i:s')
        );

        return $this->getService('TaskConversation')->insert($data);
    }

    public function insert($data, $unsetNull = true)
    {
        return parent::insert($data, $unsetNull);
    }

    public function getConversationDataForResp($conversation)
    {
        $filesRows = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_TASK_CONVERSATION, $conversation->conversation_id);
        $files = [];

        foreach ($filesRows as $file) {
             $files[] = [
                'id' => $file->getId(),
                'displayName' => $file->getDisplayName(),
                'path' => $file->getPath(),
                'url' => $file->getUrl(),
                'size' => $file->getSize(),
                'type' => HM_Files_FilesModel::getFileType($file->getDisplayName()),
            ];
        }

        if (HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ASSESSMENT === $conversation->type) {
            /** @var HM_Lesson_Assign_AssignModel $lessonAssign */
            $lessonAssign = $this->getService('LessonAssign')->fetchRow([
                'SHEID = ?' => $conversation->lesson_id,
                'MID = ?' => $conversation->user_id,
            ]);

            if($lessonAssign) {
                $conversation->setValue('mark', $lessonAssign->V_STATUS);
            }
        }

        $conversation->setValue('files', $files);

        if($conversation->teacher_id) {
            $teacherInfo = null;
            $teacher = $this->getService('User')->fetchRow(['MID = ?' => $conversation->teacher_id]);
            if($teacher) {
                $teacherInfo = [
                    'id' => $teacher->MID,
                    'name' => $teacher->getNameCyr(),
                ];
            }

            $conversation->setValue('teacher', $teacherInfo);
        }

        if($conversation->user_id) {
            $userInfo = null;
            $user = $this->getService('User')->fetchRow(['MID = ?' => $conversation->user_id]);

            if($user) {
                $userInfo = [
                    'id' => $user->MID,
                    'name' => $user->getNameCyr(),
                ];
            }

            $conversation->setValue('user', $userInfo);
        }

        return $conversation;
    }

}
