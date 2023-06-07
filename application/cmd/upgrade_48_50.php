<?php
/** @var Zend_Db_Select $select */

include "cmdBootstraping.php";
$services = Zend_Registry::get('serviceContainer');

/** @var HM_Files_FilesService $filesService */
$filesService = $services->getService('Files');

/** @var HM_Task_Conversation_ConversationService $TCService */
$TCService = $services->getService('TaskConversation');

/** @var HM_Task_Variant_VariantService $TVService */
$TVService = $services->getService('TaskVariant');


$variantsMap = $conversationsFilesMap = $variantsFilesMap = [];

// Соберём старые задания
$select = $services->getService('Task')->getSelect();
$select
    ->from('tasks')
    ->where('data IS NOT NULL');

$tasksRaw = $select->query()->fetchAll();

// Соберём старые файлы заданий
$select = $services->getService('Task')->getSelect();
$select->from('list_files');

$listFilesRaw = $select->query()->fetchAll();
foreach ($listFilesRaw as $listFile) {
    $variantsFilesMap[$listFile['kod']][] = $listFile['file_id'];
}

$allQuestions = [];
foreach ($tasksRaw as &$task) {

    // вычленим варианты (вопросы)
    $taskQuestions = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $task['data']);
    $task['questions_parsed'] = $taskQuestions;

    $allQuestions = array_merge($allQuestions, $taskQuestions);
}
unset($task);

$allQuestions = array_unique($allQuestions);

// Соберём все list, чтобы без циклозапросов
$select = $services->getService('Question')->getSelect();
$select
    ->from('list')
    ->where('kod in (?)', $allQuestions);

$questionsRaw = $select->query()->fetchAll();
$questions = [];

foreach ($questionsRaw as $q) {
    $questions[$q['kod']] = $q;
}

// Сам процесс
foreach ($tasksRaw as $task) {

    foreach ($task['questions_parsed'] as $q) {
        $question = $questions[$q];

        try {
            $newVariant = $TVService->insert([
                'task_id' => $task['task_id'],
                'name' => $question['qtema'],
                'description' => $question['qdata'],
            ]);

            $variantsMap[$q] = $newVariant->variant_id;

            // Файлы, привязанные к заданиям
            if ($variantsFilesMap[$question['kod']]) {

                $filesService->updateWhere([
                    'item_type' => HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT,
                    'item_id' => $newVariant->variant_id
                ], [
                    'file_id IN (?)' => $variantsFilesMap[$question['kod']]
                ]);
            }
        } catch (Exception $e) {
        }
    }
}


// Соберём переписки
$select = $TCService->getSelect();
$select->from('interview');
$interviewsRaw = $select->query()->fetchAll();

// Соберём файлы
$select = $TCService->getSelect();
$select->from('interview_files');
$interviewsFilesRaw = $select->query()->fetchAll();

foreach ($interviewsFilesRaw as $file) {
    $conversationsFilesMap[$file['interview_id']][] = $file['file_id'];
}

foreach ($interviewsRaw as $interview) {

    // Это очевидные параметры
    $params = [
        'lesson_id' => $interview['lesson_id'],
        'variant_id' => $variantsMap[$interview['question_id']],
        'date' => $interview['date'],
        'message' => $interview['message'],
    ];

    // А это нет
    switch ($interview['type']) {
        case 0: // Создание занятия
            $params['type'] = HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK;
            $params['user_id'] = $interview['to_whom'];

            // Мы не знаем кто конкретно создавал задание
            // А не сломается ли переписка?..
            // UPD: вроде работает
            $params['teacher_id'] = 0;
            break;

        case 1: // Вопрос от пользователя
            $params['type'] = HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_QUESTION;
            $params['user_id'] = $interview['user_id'];
            $params['teacher_id'] = 0;
            break;

        case 2: // Решение на проверку
            $params['type'] = HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TO_PROVE;
            $params['user_id'] = $interview['user_id'];
            $params['teacher_id'] = 0;
            break;

        case 3: // Препод отвечает
            $params['type'] = HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ANSWER;
            $params['user_id'] = $interview['to_whom'];
            $params['teacher_id'] = $interview['user_id'];
            break;

        case 4: // Требования на доработку
            $params['type'] = HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_REQUIREMENTS;
            $params['user_id'] = $interview['to_whom'];
            $params['teacher_id'] = $interview['user_id'];
            break;

        case 5: // Выставлена оценка
            $params['type'] = HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ASSESSMENT;
            $params['user_id'] = $interview['to_whom'];
            $params['teacher_id'] = $interview['user_id'];
            break;
    }
    try {
        $newConversation = $TCService->insert($params);
    } catch (Exception $e) {
    }

    if ($conversationsFilesMap[$interview['interview_id']]) {
        try {
            $filesService->updateWhere([
                'item_type' => HM_Files_FilesModel::ITEM_TYPE_TASK_CONVERSATION,
                'item_id' => $newConversation->conversation_id
            ], [
                'file_id IN (?)' => $conversationsFilesMap[$interview['interview_id']]
            ]);
        } catch (Exception $e) {
        }
    }
}
