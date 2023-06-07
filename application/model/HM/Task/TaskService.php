<?php
class HM_Task_TaskService extends HM_Test_Abstract_AbstractService
{
    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_TASK)));
        $this->getService('TaskVariant')->deleteBy($this->quoteInto(array('task_id=?'),array($id)));

        return parent::delete($id);
    }

    protected function _updateData($test)
    {
        return $this->getService('Test')->updateWhere(
            array('data' => $test->data),
            $this->quoteInto(array('test_id = ?', ' AND type = ?'), array($test->test_id, HM_Test_TestModel::TYPE_TASK))
        );
    }

    public function publish($id)
    {
        $this->update(array(
            'task_id' => $id,
            'status' => HM_Task_TaskModel::STATUS_STUDYONLY,
        ));
    }
    
    public function unpublish($id)
    {
        $this->update(array(
            'task_id' => $id,
            'status' => HM_Task_TaskModel::STATUS_UNPUBLISHED,
        ));
    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => 0, //public
        );
    }

    public function copy($test, $subjectId = null)
    {
        $newTest = parent::copy($test, $subjectId);

        /** @var HM_Task_Variant_VariantService $taskVariantService */
        $taskVariantService = $this->getService('TaskVariant');
        if ($newTest) {

            $variants = $taskVariantService->fetchAll(array('task_id = ?' => $test->task_id));

            if ($variants) {
                foreach ($variants as $variant) {
                    $taskVariantService->copy($variant, $newTest->task_id);
                }
            }
        }

        $this->getService('TagRef')->copy(HM_Tag_Ref_RefModel::TYPE_TASK, $test->task_id, $newTest->task_id);

        return $newTest;
    }

    public function saveFile($file)
    {

    }

    public function createLesson($subjectId, $taskId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_TASK, '%module_id='.$taskId.';%', $subjectId)
            )
        );
//        if (!count($lessons)) {
            $task = $this->getOne($this->getService('Task')->find($taskId));
            if ($task) {
                $values = array(
                    'title' => $task->title,
                    'descript' => $task->description,
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => $this->getService('User')->getCurrentUserId(),
                    'createDate' => date('Y-m-d H:i:s'),
                    'typeID' => HM_Event_EventModel::TYPE_TASK,
                    'vedomost' => 1,
                    'CID' => $subjectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'teacher' => 0,
                    'params' => 'module_id='.(int) $task->task_id.';',
                    // 5G
                    // продублируем в отдельное человеческое поле,
                    // чтобы в будущем отказаться от "params"
                    'material_id' => $task->task_id,
                    'all' => 1,
                    'cond_sheid' => '',
                    'cond_mark' => '',
                    'cond_progress' => 0,
                    'cond_avgbal' => 0,
                    'cond_sumbal' => 0,
                    'cond_operation' => 0,
                    'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
                    'section_id' => 0,
                    'order' => 0,
                );
                $lesson = $this->getService('Lesson')->insert($values);
                $students = $lesson->getService()->getAvailableStudents($subjectId);
                if (is_array($students) && count($students)) {
                    $taskVariants = $this->getService('TaskVariant')->fetchAll(['task_id = ?' => $taskId]);
                    if (count($taskVariants)) {
                        $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                    } else {
                        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
                        $url = $view->url(array(
                            'module' => 'task',
                            'controller'=> 'variant',
                            'action' => 'list',
                            'subject_id' => $subjectId,
                            'lesson_id' => $lesson->SHEID,
                            'task_id'    => $taskId
                        ));
                        header("Location: $url");
                        die();
                    }
                }
//[ES!!!] array('lesson' => $lesson))
            }
//        } else {
//            $lesson = $lessons->current();
//        }
        return $lesson;
    }

    /**
     * @param $title
     * @param $subjectId
     * @return HM_Model_Abstract
     * @throws HM_Exception
     */
    public function createDefault($title, $subjectId)
    {
        if(!strlen($title) or empty($subjectId)) {
            throw new HM_Exception(_('Ошибка при создании теста'));
        }

        $defaults = $this->getDefaults();
        $defaults['title'] = $title;
        $defaults['subject_id'] = $subjectId;
        $result = $this->insert($defaults);

        return $result;
    }


     public function migrate() { //Старые поля и таблицы не удаляем, новые поля и таблицы добавляем 
    
        $tasks = $this->fetchAll();
        $kod2variant = array();
        foreach($tasks as $task) {
            $questions = explode("~\x03~", $task->data);
            if(!count($questions)) continue;
            $variants = $this->getService('Question')->fetchAll(array('kod in (?)'=>$questions));
            foreach($variants as $variant) {
                $variantNew = $this->getService('TaskVariant')->insert(array('task_id'=>$task->task_id, 'name'=>$variant->qtema, 'description'=>$variant->qdata));
                $kod2variant[$variant->kod] = $variantNew->variant_id;
            }

            $filesIn = $this->getService('QuestionFiles')->fetchAll(array('kod in (?)'=>$questions));
            if(!count($filesIn)) continue;
            $files = $this->getService('Files')->fetchAll(array('file_id in (?)'=>$filesIn->getList('kod', 'file_id')));
            $file2kod = $filesIn->getList('file_id', 'kod');
            foreach($files as $file) {
                $data = $file->getValues();
                $data['item_type'] = HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT;
                $data['item_id'] = $kod2variant[$file2kod[$file->file_id]];
                $this->getService('Files')->update($data);
            }
        }

        $interviews = $this->getService('TaskConversation')->fetchAll();
        foreach($interviews as $interview) {
            $data = $interview->getValues();
            if($interview->question_id) {
                $data['variant_id'] = $kod2variant[$interview->question_id];
            }
            $data['user_id'] = ($interview->type==1 || $interview->type==2) ? $interview->user_id : $interview->to_whom;
            $data['teacher_id'] = !($interview->type==1 || $interview->type==2) ? $interview->user_id : $interview->to_whom;
            $interview = $this->getService('TaskConversation')->update($data);
        }

        $filesIn = $this->getService('InterviewFiles')->fetchAll();
        $files = $this->getService('Files')->fetchAll(array('file_id in (?)'=>$filesIn->getList('file_id')));
        $file2kod = $filesIn->getList('file_id', 'interview_id');
        foreach($files as $file) {
            $data = $file->getValues();
            $data['item_type'] = HM_Files_FilesModel::ITEM_TYPE_TASK_CONVERSATION;
            $data['item_id'] = $file2kod[$file->file_id];
            $this->getService('Files')->update($data);
        }

        die('fin');
    }
       
    public function getTasksIdsWithVariants()
    {
        $variants = $this->getService('TaskVariant')->fetchAll();
        $result = [];

        foreach($variants as $variant) {
            $itemId = $variant->task_id;
            $result[$itemId][] = $variant->description;
        }

        return $result;
    }

    /**
     * @param int $subjectId
     * @return array
     */
    public function getTasksIdsWithoutVariants($subjectId = 0)
    {
        $result = [];
        $variantsSelect = $this->getSelect();

        $variantsSelect
            ->from(['t' => $this->getTableName()], ['t.task_id'])
            ->joinLeft(['tv' => $this->getService('TaskVariant')->getTableName()], 'tv.task_id = t.task_id', [])
            ->where('tv.variant_id IS NULL')
            ->group('t.task_id');

        if($subjectId) {
            $variantsSelect->where('t.subject_id = ?', $subjectId);
        }

        $resultRaw = $variantsSelect->query()->fetchAll();
        $result = count($resultRaw) ? array_column($resultRaw, 'task_id') : $result;

        return $result;
    }

    public function clearLesson($subject, $taskId)
    {
        if($subject == null) {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?"),
                    array(HM_Event_EventModel::TYPE_TASK, '%module_id=' . $taskId . ';%')
                )
            );
        } else {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                    array(HM_Event_EventModel::TYPE_TASK, '%module_id=' . $taskId . ';%', $subject->subid)
                )
            );
        }

        if (count($lessons)) {
            /** @var HM_Lesson_LessonService $lessonService */
            $lessonService = $this->getService('Lesson');
            foreach($lessons as $lesson) {
                $lessonService->resetMaterialFields($lesson->SHEID);
            }
        }
    }

    public function getTask($lessonId, $userId) 
    {
        $return = array();
        $return['messages'] = $return['variant'] = array();

        $lesson = $this->getService('Lesson')->find($lessonId)->current();
//        $mark = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array("MID = ?"=>$userId, "SHEID = ?"=>$lessonId)));

        $messages = $this->getService('TaskConversation')->fetchAll(array('lesson_id=?'=>$lessonId, 'user_id=?'=>$userId/*, 'type <> ?' => HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK*/), 'date DESC');

        foreach($messages as $message) {
            $data = $this->getService('TaskConversation')->getConversationDataForResp($message);
        
            $item = array(
                'id' => $message->conversation_id,
                'title' => '??????',
                'date' => date('d.m.Y H:i', strtotime($message->date)),
                'message' => $message->message,
                'fio'=> $message->teacher ? $message->teacher['name'] : $message->user['name'],
                'Im' => $message->teacher ? false: true
            );

            $item['files'] = array();
            foreach($message->files as $file) {
                $item['files'][] = array('name'=>$file['displayName'], 'url'=>"file/get/file/file_id/{$file['id']}");
            }

            if($message->type==HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK) {
                $return['variant'] = $item;
            } else {
                $return['messages'][] = $item;
            }
        }

        $types = HM_Task_Conversation_ConversationModel::getStudentTypes();
        $return['replyTypes'] = array();
        foreach($types as $id=>$type){
            $return['replyTypes'][] = array('id'=>$id, 'name'=>$type);
        }
        $return['id'] = $lessonId;
        $return['name'] = $lesson->title;

        return $return;
    }

    public function saveTask($data) 
    {
        $lessonId = $data->task_id;
        $type = $data->reply->type;

        $userId = $this->getService('User')->getCurrentUserId();

        $conversation = $this->getService('TaskConversation')->insert(array(
            'lesson_id' => $lessonId,
            'user_id' => $userId,
            'teacher_id' => 0,
            'type' => $type,
            'message' => $data->reply->text,
            'date' => date('Y-m-d H:i:s')
        ));
        if(!$conversation) return false;

        $this->notifyAboutTask($lessonId, $userId, $type);

        if($data->file_name) {
            $base64Pos = strpos($data->file, 'base64');
            $content = $base64Pos ? base64_decode(substr($data->file, $base64Pos+7)) : $data->file;
            $file = $this->getService('Files')->addFileFromBinary($content, $data->file_name, HM_Files_FilesModel::ITEM_TYPE_TASK_CONVERSATION, $conversation->conversation_id);
        }

        return true;
    }

    public function notifyAboutTask($lessonId, $userId, $type)
    {
        $teacherIds = [];

        /** @var HM_Lesson_Task_TaskModel $lesson */
        $lesson = $this->getService('Lesson')->find($lessonId)->current();

        $teachers = $this->getService('Teacher')->fetchAll(array('CID = ?' => $lesson->CID));
        if (count($teachers)) {
            $teacherIds = $teachers->getList('MID');
        }

        /** @var HM_User_UserModel $user */
        $user = $this->getService('User')->find($userId)->current();
        $subject = $this->getService('Subject')->find($lesson->CID)->current();

        /** @var HM_Messenger $messenger */
        $messenger = $this->getService('Messenger');

        $params = ['lesson_id' => $lesson->SHEID, 'subject_id' => $subject->subid];
        switch ($type) {

            // На проверку
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TO_PROVE:
                $messenger->setTemplate(HM_Messenger::TEMPLATE_STUDENT_SOLVE_TASK);
                $params['fio'] = $user->getNameCyr();
                break;

            //Вопрос тьютору
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_QUESTION:
                $messenger->setTemplate(HM_Messenger::TEMPLATE_STUDENT_QUESTION_TASK);
                $params['fio'] = $user->getNameCyr();
                break;

            // Ответ тьютора
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ANSWER:
                $messenger->setTemplate(HM_Messenger::TEMPLATE_TEACHER_ANSWER_TASK);
                break;

            // Требования на доработку
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_REQUIREMENTS:
                $messenger->setTemplate(HM_Messenger::TEMPLATE_TEACHER_CONDITION_TASK);
                break;
        }

        $messenger->assign($params);

        switch ($type) {
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TO_PROVE:
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_QUESTION:

                foreach ($teacherIds as $teacherId)
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $teacherId);

                break;

            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ANSWER:
            case HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_REQUIREMENTS:

                $messenger->send($this->getService('User')->getCurrentUserId(), $userId);
                break;
        }
    }
}