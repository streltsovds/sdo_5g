<?php
class HM_Quest_QuestService extends HM_Service_Abstract
{
    protected $_pollSortingVariantsPoints = [];
    protected $_pollSortingVariantsPointsSum = [];
    protected $_pollSortingVariantsPopularity = [];
    protected $_pollSortingRespondentsCount = [];

    public function insert($data, $unsetNull = true)
    {
        list($dataQuest, $dataSettings) = HM_Quest_Settings_SettingsModel::split($data);

        $quest = parent::insert($dataQuest, $unsetNull);

        $dataSettings = $dataSettings + array(
            'quest_id' => $quest->quest_id,
            'scope_type' => $quest->subject_id ? HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT : HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL,
            'scope_id' => $quest->subject_id ? $quest->subject_id : 0,
        );

        $this->getService('QuestSettings')->insert($dataSettings);

        return $quest;
    }

    public function update($data, $scopeType = HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL, $scopeId = 0)
    {
        list($dataQuest, $dataSettings) = HM_Quest_Settings_SettingsModel::split($data);

        $quest = parent::update($dataQuest);
        $quest->setScope($scopeType, $scopeId);

        $dataSettings = array_merge($dataSettings, array(
            'quest_id' => $quest->quest_id,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ));

        $res = $this->getService('QuestSettings')->update($dataSettings);
        if(!isset($res->quest_id)) {//не нашли, надо создать!
            $res = $this->getService('QuestSettings')->insert($dataSettings);
        }

        return $quest;
    }

    public function delete($questId)
    {
        if (!$questId) return true;

        $this->getService('QuestQuestionQuest')->deleteBy(array('quest_id = ?' => $questId));

        // вопросы не удаляем ,только открепляемс от теста
//         if (count($questions = $this->getService('QuestQuestionQuest')->fetchAll(array('quest_id = ?' => $questId)))) {
//             $questionIds = $questions->getList('question_id');
//             $this->getService('QuestQuestion')->deleteBy(array('question_id IN (?)' => $questionIds));
//             $this->getService('QuestQuestionResult')->deleteBy(array('question_id IN (?)' => $questionIds));
//             $this->getService('QuestQuestionVariant')->deleteBy(array('question_id IN (?)' => $questionIds));
//         }

        if (count($attempts = $this->getService('QuestAttempt')->fetchAll(array('quest_id = ?' => $questId)))) {
            $attemptIds = $attempts->getList('attempt_id');
            $this->getService('QuestCategoryResult')->deleteBy(array('attempt_id IN (?)' => $attemptIds));
        }

        $this->getService('QuestCategory')->deleteBy(array('quest_id = ?' => $questId));
        $this->getService('QuestSettings')->deleteBy(array('quest_id = ?' => $questId));
        $this->getService('QuestCluster')->deleteBy(array('quest_id = ?' => $questId));
        $this->getService('QuestQuestionQuest')->deleteBy(array('quest_id = ?' => $questId));
        $this->getService('QuestAttempt')->deleteBy(array('quest_id = ?' => $questId));

        return parent::delete($questId);
    }

    public function createLesson($subjectId, $questId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_TEST, '%module_id='.$questId.';%', $subjectId)
            )
        );
//        if (!count($lessons)) {
            $quest = $this->getOne($this->getService('Quest')->find($questId));
            if ($quest && in_array($quest->type, [HM_Quest_QuestModel::TYPE_TEST, HM_Quest_QuestModel::TYPE_POLL])) {
                $values = array(
                    'title' => $quest->name,
                    'descript' => $quest->description,
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => $this->getService('User')->getCurrentUserId(),
                    'createDate' => date('Y-m-d H:i:s'),
                    'typeID' => $quest->type == HM_Quest_QuestModel::TYPE_TEST ? HM_Event_EventModel::TYPE_TEST : HM_Event_EventModel::TYPE_POLL,
                    'vedomost' => 1,
                    'CID' => $subjectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'teacher' => 0,
                    'params' => 'module_id='.(int) $quest->quest_id.';',
                    // 5G
                    // продублируем в отдельное человеческое поле,
                    // чтобы в будущем отказаться от "params"
                    'material_id' => $quest->quest_id,
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

                $questSettings = $this->getService('QuestSettings')->fetchAll(array(
                    'quest_id = ?' => $quest->getValue('quest_id'),
                    'scope_id = ?' => 0,
                    'scope_type = ?' => HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL
                ))->current();

                if (!$questSettings && $subjectId) {
                    $questSettings = $this->getService('QuestSettings')->fetchAll(array(
                        'quest_id = ?' => $quest->getValue('quest_id'),
                        'scope_id = ?' => $subjectId,
                        'scope_type = ?' => HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT
                    ))->current();
                }

                if ($questSettings) {
                    $values = $questSettings->getValues();
                    $values['scope_id'] = $lesson->getValue('SHEID');
                    $values['scope_type'] = HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON;

                    $this->getService('QuestSettings')->insert($values);
                }

                $students = $lesson->getService()->getAvailableStudents($subjectId);
                if (is_array($students) && count($students)) {
                    $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                }
//[ES!!!] array('lesson' => $lesson))
            }
//        } else {
//            $lesson = $lessons->current();
//        }
        return $lesson;
    }

    public function getDefaults()
    {
        return array(
            'type' => HM_Quest_QuestModel::TYPE_TEST,
            'status' => 1,
        );
    }

    public function pluralTestCount($count, $type = HM_Quest_QuestModel::TYPE_TEST)
    {
        if (!$count) {
            return _('Нет');
        }

        $labels = array(
            HM_Quest_QuestModel::TYPE_TEST => _('тест'),
            HM_Quest_QuestModel::TYPE_POLL => _('опрос'),
            HM_Quest_QuestModel::TYPE_PSYCHO => _('опрос'),
            HM_Quest_QuestModel::TYPE_FORM => _('форма'),
        );

        return sprintf(_n($labels[$type].' plural', '%s '.$labels[$type], $count), $count);
    }

    public function pluralQuestionCount($count)
    {
        return sprintf(_n('вопрос plural', '%s вопрос', $count), $count);
    }

    /**
     * @param $questId
     * @return bool|HM_Quest_QuestModel
     */
    public function getQuestWithSettings($questId) {
        $collection = $this->findDependence(
            'Settings',
            $questId
        );

        return $this->getOne($collection);
    }

    public function getSettingsReport(HM_Quest_QuestModel $quest, $model)
    {
        $settings = $quest->getSettings();

        $attemptModel = $model->getModel();
        $contextModel = $model->getContextModel();
        $contextType  = $contextModel ? $contextModel->getQuestContext() : false;

        $globalSettings = array();

        $scopeType = $settings ? $settings->scope_type : null;

        switch ($scopeType) {
            case HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL:
                break;
            case HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON:
                $lesson = $this->getService('Lesson')->find($settings->scope_id)->current();
                $subject = $this->getService('Subject')->find($lesson->CID)->current();

                $globalSettings[_('Учебный курс')] = $subject->name;
                $globalSettings[_('Занятие')]      = $lesson->title;
                break;
            case HM_Quest_QuestModel::SETTINGS_SCOPE_MEETING:
                $meeting = $this->getService('Meeting')->find($settings->scope_id)->current();
                $project = $this->getService('Project')->find($meeting->project_id)->current();

                $globalSettings[_('Проект')] = $project->name;
                $globalSettings[_('Мероприятие')]      = $meeting->title;
                break;
            case HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT:
                $subject = $this->getService('Subject')->find($settings->scope_id)->current();
                $globalSettings[_('Учебный курс')] = $subject->name;
                break;
            case HM_Quest_QuestModel::SETTINGS_SCOPE_SESSION:
                //TODO
                break;
        }

        $clusters = array();
        if ($attemptModel['quest']->clusters && $attemptModel['quest']->questionQuest) {
            $usedClustersIds = $attemptModel['quest']->questionQuest->getList('question_id', 'cluster_id');
            $clusterNames    = $attemptModel['quest']->clusters->getList('cluster_id', 'name');
            $clusterNames[0] = _('Вопросы без темы');
            foreach ($attemptModel['numbers'] as $questionId => $clusterId) {
                if (isset($clusterNames[$usedClustersIds[$questionId]])) {
                    $clusterName = $clusterNames[$usedClustersIds[$questionId]];
                    if (!isset($clusters[$clusterName])) {
                        $clusters[$clusterName] = 0;
                    }
                    $clusters[$clusterName] ++;
                } else {
                    $clusters[_('Вопросы без темы')] ++;
                }

            }
//            ksort($clusters);
            foreach ($clusters as $clusterId => $count) {
                $clusters[$clusterId] = $this->pluralQuestionCount($count);
            }
        }

        $globalTitle = '';
        switch ($attemptModel['quest']->type) {
            case HM_Quest_QuestModel::TYPE_POLL:
            case HM_Quest_QuestModel::TYPE_PSYCHO:
                $globalTitle = _('Информация об опросе');
                break;
            case HM_Quest_QuestModel::TYPE_FORM:
                $globalTitle = _('Информация об оценочной форме');
                break;
            case HM_Quest_QuestModel::TYPE_TEST:
                $globalTitle = _('Информация о тестировании');

                $globalSettings[_('Ограничение по времени прохождения')] =
                    ($settings && $settings->limit_time) ? $quest->limit_time._(' мин') : _('Без ограничения');

                if ($settings && $settings->limit_attempts) {
                    $usedAttempts = '';
                    if ($contextType && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_STUDENT))) {
                        $usedAttempts = $this->getService('QuestAttempt')->countAll($this->quoteInto(
                            array('context_event_id=?', ' AND context_type=?', ' AND user_id=?', ' AND status=?'),
                            array($contextType['context_event_id'], $contextType['context_type'], $this->getService('User')->getCurrentUserId(), HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED)
                        ));

                        if($attemptModel['quest']->limit_clean) {
                            $usedAttempts %= $quest->limit_attempts;
                        }

                        $usedAttempts = $usedAttempts.'/';
                    }
                    $globalSettings[_('Ограничение по количеству попыток')] = $usedAttempts .  $quest->limit_attempts;
                } else {
                    $globalSettings[_('Ограничение по количеству попыток')] = _('Без ограничения');
                }

                break;
        }

        $globalSettings[_('Количество вопросов')] = count($attemptModel['questions']);

        $showClusters = (count($clusters) > 1) || ((count($clusters) == 1) && !array_key_exists(_('Вопросы без темы'), $clusters));

        return array(
            'globalTitle'   => $globalTitle,
            'clustersTitle' => _('Будут заданы вопросы по темам'),
            'global'        => $globalSettings,
            'clusters'      => $clusters,
            'showClusters'  => $showClusters
        );
    }

    public function countScaleMark($lesson, $result = null)
    {
        $score = false;
        switch ($lesson->getScale()) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                if (isset($result)) {
                    if (empty($lesson->threshold) || ($result >= $lesson->threshold)) {
                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                }
                break;
            case HM_Scale_ScaleModel::TYPE_TERNARY:
                if (isset($result)) {
                    if (empty($lesson->threshold) || ($result >= $lesson->threshold)) {
                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                    } else {
                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                    }
                }
                break;
            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                if (isset($result)) {
                    if ($formulaId = $lesson->getFormulaId()) {
                        $formula = $this->getService('Formula')->getById($formulaId);
                        if ($formula) {
                            $score = $formula->getResultValue($result);
                        }
                    } else {
                        $score = $result;
                    }
                }
                break;
        }

        return $score;
    }

    public function exportVariants($itemIds, $variantsCount, $itemType = 'lesson')
    {
        if ($itemType == 'meeting') {
            $exportManager = new HM_Quest_Export_MeetingManager($itemIds);
        } else {
            $exportManager = new HM_Quest_Export_Manager($itemIds);
        }
        return $exportManager->getExportPdf($variantsCount);
    }

    public function exportTestAnswers($lessonIds) {
        $exportManager = new HM_Quest_Export_Manager($lessonIds);
        return $exportManager->getExportPdf(1, true);
    }


    // АЛЯРМ! реализовано ТОЛЬКО для quest_poll_model
    // todo отуниверсалить!!!!!!!!!!
    public function getAnswerStatByQuestions($questId, $contextModel = null)
    {
        $result = array();
        $quest     = $this->getService('Quest')->findDependence(array('QuestionQuest'),
            $questId)->current();
        if($quest) {
            $result['quest_title'] = $quest->name;
            $result['questions'] = array();
        }
        // вопросы с вариантами
        $questionIds = $quest->questionQuest->getList('question_id');
        $questions = $this->getService('QuestQuestion')->fetchAllDependence(array('Variant'), array('question_id IN (?)' => $questionIds));
        $questions = $questions->asArrayOfObjects();

        $externalVariants =array();
        if ($quest->scale_id) {
            $scaleValues = Zend_Registry::get('serviceContainer')->getService('ScaleValue')
                ->fetchAll('scale_id='.$quest->scale_id);

            foreach ($scaleValues as $scaleValue) {
                $variant = new stdClass();
                $variant->variant = $scaleValue->text;
                $variant->question_variant_id = $scaleValue->value_id;
                $externalVariants[$scaleValue->value_id] = $variant;
            }
        }

        if ($contextModel == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET) {
            $questContext = array(
                'context_type' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET,
                'context_event_id' => $questId,
            );
        } else {
            $questContext = $quest->getQuestContext();
        }

        $userAttempts = $this->getService('QuestAttempt')->fetchAllDependence(
            array('QuestionResult'),
            array(
            'context_event_id = ?' => $questContext['context_event_id'],
            'context_type = ?' => $questContext['context_type'],
            'quest_id = ?' => $quest->quest_id,
            'type = ?' => $quest->type,
            'status = ?' => HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED
        ));

        $attemptsCount = count($userAttempts);
        if (count($userAttempts)) {
            foreach ($userAttempts as $attempt) {
                foreach($attempt->questionResults as $questionResult) {
                    $question = &$questions[$questionResult->question_id];
                    if ($externalVariants) {
                        $question->variants = $externalVariants;
                    }

                    $resultVariants = $question->displayUserResult($questionResult);

                    $resultVariants = explode(',<br>', $resultVariants);
                    if(!isset($result['questions'][$question->question_id])) {
                        $result['questions'][$question->question_id] = array(
                            'title' => $question->question,
                            'variants' => array(),
                            'totalValue' => $attemptsCount
                        );
                        foreach ($question->variants as $variant) {
                            $result['questions'][$question->question_id]['variants'][$variant->variant] = array(
                                'title' => $variant->variant,
                                'count' => 0
                            );
                        }
                    }
                    foreach ($resultVariants as $resultItem) {
                        if(isset($result['questions'][$question->question_id]['variants'][$resultItem])) {
                            $result['questions'][$question->question_id]['variants'][$resultItem]['count']++;
                        }
                    }
                }
            }
        }

        /*if(count($result['questions'])) {
            foreach($result['questions'] as &$question) {
                $question['report_list'] = array_map(function($item) {
                    return $item['count'];
                } ,$question['variants']
                );
            }
        }*/

        return $result;
    }

    public function getAnswerStat($questId, $contextTypes, $contextId, $from = null, $to = null)
    {
        $result = array();
        if (!is_array($contextTypes)) $contextTypes = array($contextTypes);

        $quest = $this->getService('Quest')->findDependence(array('QuestionQuest'), $questId)->current();
        
        if($quest) {
            $result['quest_title'] = $quest->name;
            $result['questions'] = array();
        }

        // вопросы с вариантами
        $questions = array();
        if ($quest->questionQuest) {
            $questionIds = $quest->questionQuest->getList('question_id');
            $questions = $this->getService('QuestQuestion')->fetchAllDependence(array('Variant'), array('question_id IN (?)' => $questionIds), 'question_id ASC');
            $questions = $questions->asArrayOfObjects();
        }

        $externalVariants =array();
        if ($quest->scale_id) {
            $scaleValues = Zend_Registry::get('serviceContainer')->getService('ScaleValue')
                ->fetchAll('scale_id='.$quest->scale_id, 'value_id ASC');

            foreach ($scaleValues as $scaleValue) {
                $variant = new stdClass();
                $variant->variant = $scaleValue->text;
                $variant->question_variant_id = $scaleValue->value_id;
                $externalVariants[$scaleValue->value_id] = $variant;
            }
        }


        $where = array(
            'quest_id = ?' => $quest->quest_id,
            'type = ?' => $quest->type,
            'status = ?' => HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED
        );

        if ($from != null) {
            $where['date_end >= ?'] = date('Y-m-d 00:00:00', strtotime($from));
        }

        if ($to != null) {
            $where['date_end <= ?'] = date('Y-m-d 23:59:59', strtotime($to));
        }

        $userAttempts = array();
        foreach ($contextTypes as $contextType) {
            if ($contextType == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET) {
                $userAttempts = array_merge($userAttempts, $this->getService('QuestAttempt')->fetchAllDependence(
                    array('QuestionResult'),
                    array_merge(array(
                        'context_event_id = ?' => $quest->quest_id,
                        'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET,
                    ), $where)
                )->asArrayOfObjects());
            } elseif ($contextType == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK && $contextId) {

                $feedbackUserIds = $this->getService('FeedbackUsers')->fetchAll(
                    array('feedback_id IN (?)' => $contextId)
                )->getList('feedback_user_id');

                if (count($feedbackUserIds)) {
                    $userAttempts = array_merge($userAttempts, $this->getService('QuestAttempt')->fetchAllDependence(
                        array('QuestionResult'),
                        array_merge(array(
                            'context_event_id IN (?)' => $feedbackUserIds,
                            'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK,
                        ), $where)
                    )->asArrayOfObjects());
                }
            } elseif ($contextType == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING && $contextId) {

                $shIds = $this->getService('Lesson')->fetchAll(
                    array('CID = ?' => $contextId)
                )->getList('SHEID');

                if ($shIds) {
                    $userAttempts = array_merge($userAttempts, $this->getService('QuestAttempt')->fetchAllDependence(
                        array('QuestionResult'),
                        array_merge(array(
                            'context_event_id IN (?)' => $shIds,
                            'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING,
                        ), $where)
                    )->asArrayOfObjects());
                } else {
                    $userAttempts = array();
                }

            } elseif ($contextType == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT && $contextId) {

                $meetingIds = $this->getService('Meeting')->fetchAll(
                    array('project_id = ?' => $contextId)
                )->getList('meeting_id');

                if ($meetingIds) {
                    $userAttempts = array_merge($userAttempts, $this->getService('QuestAttempt')->fetchAllDependence(
                        array('QuestionResult'),
                        array_merge(array(
                            'context_event_id IN (?)' => $meetingIds,
                            'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT,
                        ), $where)
                    )->asArrayOfObjects());
                } else {
                    $userAttempts = array();
                }

            } else {
                $userAttempts = array_merge($userAttempts, $this->getService('QuestAttempt')->fetchAllDependence(
                    array('QuestionResult'),
                    $where
                )->asArrayOfObjects());
            }
        }

        $colorCorrectCounter = array();
        $colorCounter = array();

        $attemptsCount = count($userAttempts);
        if ($attemptsCount) {
            foreach ($userAttempts as $attempt) {

                foreach($attempt->questionResults as $questionResult) {
                    $question = &$questions[$questionResult->question_id];

                    switch ($question->type) {
                        case 'single':
                        case 'multiple':
                        case 'imagemap':
                            if ($externalVariants) {
                                $question->variants = $externalVariants;
                            }

                            $resultVariants = $question->displayUserResult(
                                $questionResult,
                                HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER,
                                HM_Quest_Question_QuestionModel::RESULT_CONTEXT_DIAGRAM
                            );

                            $resultVariants = explode(HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $resultVariants);

                            if(!isset($result['questions'][$question->question_id])) {
                                $result['questions'][$question->question_id] = array(
                                    'title' => $question->shorttext,
                                    'variants' => array(),
                                    'totalValue' => 0,
                                    'type' => $question->type,
                                );

                                $colorCorrectCounter[$question->question_id] = 0;
                                $colorCounter[$question->question_id] = 0;

                                if ($question->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT) {
                                    $colors = HM_Quest_Question_QuestionModel::getIncorrectColors();;
                                } elseif ($question->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT) {
                                    $colors = HM_Quest_Question_QuestionModel::getUnknownColors();
                                } else {
                                    $colors = HM_Quest_Question_QuestionModel::getColors();
                                }

                                foreach ($question->variants as $variant) {
                                    if ($variant->is_correct) {
                                        $correctColors = HM_Quest_Question_QuestionModel::getCorrectColors();
                                        $curColor = $correctColors[$colorCorrectCounter[$question->question_id]++];
                                    } else {
                                        $curColor = $colors[$colorCounter[$question->question_id]++];
                                    }

                                    $result['questions'][$question->question_id]['variants'][$variant->variant] = array(
                                        'title' => $variant->variant . ' ' . ($variant->is_correct ? " (Верно)" : ""),
                                        'count' => 0,
                                    );

                                    $result['questions'][$question->question_id]['colors'][$variant->variant] = $curColor;
                                }
                            }

                            foreach ($resultVariants as $resultItem) {
                                if(isset($result['questions'][$question->question_id]['variants'][$resultItem])) {
                                    $result['questions'][$question->question_id]['variants'][$resultItem]['count']++;
                                    $result['questions'][$question->question_id]['totalValue']++;
                                }
                            }
                            break;

                        case 'text':
                        case 'mapping':
                        case 'classification':
                        case 'placeholder':
                            $this->_twoStateStat($result, $question, $questionResult);
                            break;

                        case 'sorting':
                            switch ($quest->type) {
                                case HM_Quest_QuestModel::TYPE_POLL:
                                    $this->_pollSortingPointsStat($question, $questionResult);
                                    break;
                                
                                case HM_Quest_QuestModel::TYPE_TEST:
                                    $this->_twoStateStat($result, $question, $questionResult);
                                    break;
                            }
                            break;
                    }

                }
            }
            $this->_pollSortingPercentsStat($result);

        }
        ksort($result['questions']);

        return $result;
    }

    public function isDeletable($questId)
    {
        if (in_array($questId, HM_Quest_QuestModel::getHardcodeDeleteIds())) {
            return false;
        }
        return true;
    }

    public function isEditable($questId)
    {
        if (in_array($questId, HM_Quest_QuestModel::getHardcodeEditIds())) {
            return false;
        }
        return true;
    }

    public function isDenyByCreatorRole($creatorRole)
    {
        $currentUserRole = $this->getService('User')->getCurrentUserRole();

        if(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER == $creatorRole &&
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER !== $currentUserRole
        ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param HM_Quest_QuestModel $quest
     * @param HM_Lesson_LessonModel $lesson
     * @param HM_Form_Lesson $form
     */
    public function _postProcessQuest($quest, $subjectId, $lessonId, $form) {
        // Будем копировать настройки из области видимости курса
        $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $subjectId);

        /** @var HM_Quest_Settings_SettingsService $questSettingsService */
        $questSettingsService = $this->getService('QuestSettings');

        // Устанавливаем свою область видимости для занятий
        /** @var HM_Quest_Settings_SettingsModel $settings */
        $settings = $questSettingsService->copyToScope($quest, HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON, $lessonId);

        /**
         * Индивидульная настройка теста для занятия.
         * Раскомментировать при необходимости.
         */
        list($dataQuest, $dataSettings) = HM_Quest_Settings_SettingsModel::split($form->getValues());

        $dataSettings['quest_id']   = $settings->quest_id;
        $dataSettings['scope_type'] = $settings->scope_type;
        $dataSettings['scope_id']   = $settings->scope_id;


        if ($dataSettings['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $dataSettings['mode_selection_questions'] = $dataSettings['mode_selection_questions_cluster'];
        }
        unset($dataSettings['mode_selection_questions_cluster']);

        if ('0' !== $dataSettings['mode_display'] and
            !$dataSettings['mode_display']
        ) {
            $dataSettings['mode_display'] = $settings->mode_display;
        }

        if ($dataSettings['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS) {
            $dataSettings['mode_display_clusters'] = new Zend_Db_Expr('NULL');
        }

        if ($dataSettings['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS) {
            $dataSettings['mode_display_questions'] = new Zend_Db_Expr('NULL');
        } elseif($settings->mode_display_questions && !$dataSettings['mode_display_questions']) {
            $dataSettings['mode_display_questions'] = $settings->mode_display_questions;
        }

        if (!$dataSettings['cluster_limits']) {
            $dataSettings['cluster_limits'] = $settings->cluster_limits;
        }
        if (!$dataSettings['limit_time']) {
            $dataSettings['limit_time'] = new Zend_Db_Expr('NULL');
        }
        if (!$dataSettings['limit_attempts']) {
            $dataSettings['limit_attempts'] = new Zend_Db_Expr('NULL');
        }
        if (!$dataSettings['limit_clean']) {
            $dataSettings['limit_clean'] = new Zend_Db_Expr('NULL');
        }
        $questSettingsService->update($dataSettings);
    }

    /**
     * @param $title
     * @param $subjectId
     * @return HM_Model_Abstract
     * @throws HM_Exception
     */
    public function createDefault($title, $subjectId, $type)
    {
        if(!strlen($title) or empty($subjectId)) {
            throw new HM_Exception(_('Ошибка при создании теста'));
        }

        $defaults = $this->getDefaults();
        $defaults['name'] = $title;
        $defaults['subject_id'] = $subjectId;
        $defaults['type'] = $type;
        $result = $this->insert($defaults);

        return $result;
    }

    public function getQuestIdsWithQuestions($type = null)
    {
        $select = $this->getSelect()
            ->from(
                ['q' => 'questionnaires'],
                [
                    'quest_id' => 'q.quest_id',
                    'question' => 'qq.question',
                ]
            )
            ->joinInner(['qqq' => 'quest_question_quests'], 'qqq.quest_id=q.quest_id', [])
            ->joinInner(['qq' => 'quest_questions'], 'qq.question_id=qqq.question_id', []);

        if($type) {
            $select->where('q.type=?', $type);
        }

        $queryItems = $select->query()->fetchAll();
        $result = [];

        foreach ($queryItems as $queryItem) {
            $questId = $queryItem['quest_id'];
            $result[$questId][] = $queryItem['question'];
        }

        return $result;
    }

    public function getQuestIdsWithoutQuestions($type = null, $subjectId = 0)
    {
        $result = [];
        $select = $this->getSelect()
            ->from(
                ['q' => $this->getTableName()],
                ['quest_id' => 'q.quest_id',]
            )
            ->joinLeft(['qqq' => 'quest_question_quests'], 'qqq.quest_id = q.quest_id', [])
            ->where('qqq.question_id IS NULL');

        if($type) {
            $select->where('q.type = ?', $type);
        }

        if($subjectId){
            $select->where('q.subject_id = ?', $subjectId);
        }

        $resultRaw = $select->query()->fetchAll();
        $result = count($resultRaw) ? array_column($resultRaw, 'quest_id') : $result;

        return $result;
    }

    public function getQuestIdsWithVariants($type = null)
    {
        $select = $this->getSelect()
            ->from(
                ['q' => 'questionnaires'],
                [
                    'quest_id' => 'q.quest_id',
                    'variant' => 'qv.variant',
                ]
            )
            ->joinInner(['qqq' => 'quest_question_quests'], 'qqq.quest_id=q.quest_id', [])
            ->joinInner(['qv' => 'quest_question_variants'], 'qv.question_id=qqq.question_id', []);

        if($type) {
            $select->where('q.type = ?', $type);
        }

        $queryItems = $select->query()->fetchAll();
        $result = [];

        foreach ($queryItems as $queryItem) {
            $questId = $queryItem['quest_id'];
            $result[$questId][] = $queryItem['variant'];
        }

        return $result;
    }

    public function clearLesson($subject, $questId, $eventType)
    {
        if(!in_array($eventType, [HM_Event_EventModel::TYPE_POLL, HM_Event_EventModel::TYPE_TEST])) {
            throw new Exception('This event type is not allowed.');
        }

        if ($subject == null) {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    ['(typeID = ?', ' AND params LIKE ?)'],
                    [
                        $eventType,
                        '%module_id='.$questId.';%',
                    ]
                )
            );
        } else {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    ['(typeID = ?', ' AND params LIKE ?)', ' AND CID = ?'],
                    [
                        $eventType,
                        '%module_id='.$questId.';%',
                        $subject->subid,
                    ]
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

    public function getClassifierLinkType($type)
    {
        $linkType = HM_Classifier_Link_LinkModel::TYPE_TEST;
        switch ($type) {
            case HM_Quest_QuestModel::TYPE_POLL:
                $linkType = HM_Classifier_Link_LinkModel::TYPE_POLL;
                break;
            case HM_Quest_QuestModel::TYPE_PSYCHO:
                $linkType = HM_Classifier_Link_LinkModel::TYPE_PSYCHO;
                break;
            case HM_Quest_QuestModel::TYPE_FORM:
                $linkType = HM_Classifier_Link_LinkModel::TYPE_FORM;
                break;
        }

        return $linkType;
    }


    public function copy($test, $subjectId = null)
    {
        if ($test) {
            if (null !== $subjectId) {
                $test->subject_id = $subjectId;
            }

            $questions = $test->getQuestionsIds();

            if (count($questions)) {

                $test->data = '';
                $newQuestions = array();

                foreach($questions as $questionId) {
                    $newQuestion = $this->getService('Question')->copy($questionId);
                    if ($newQuestion) {
                        $newQuestions[] = $newQuestion->kod;
                    }
                }

                $test->addQuestionsIds($newQuestions);
            }

            $newTest = $this->insert($test->getValues(null, array('test_id', 'task_id', 'quiz_id')));

            return $newTest;
        }

        return false;
    }

    public function addQuestionsWithVariants(&$originQuest)
    {
        if (!$originQuest->questionQuest)
            return false;

        $questionQuest = clone $originQuest->questionQuest;

        foreach ($questionQuest as $v) {
            $questionIds[] = $v->question_id;
        }

        // Добавляется так потому что связь с Question идёт через HM_Quest_Question_Quest_QuestTable
        $originQuest->questionQuest->questions = $this->getService('QuestQuestion')->fetchAllDependence(array('Variant'), array('question_id IN (?)' => $questionIds));
    }

    private function _twoStateStat(&$result, $question, &$questionResult)
    {
        if (!isset($result['questions'][$question->question_id])) {
            $result['questions'][$question->question_id] = array(
                'title' => $question->shorttext,
                'variants' => array(),
                'totalValue' => 100,
                'type' => $question->type,
            );
            $result['questions'][$question->question_id]['count'] = 0;
            $result['questions'][$question->question_id]['variants'][_('Верно')] = array(
                'title' => _('Верно'),
                'count' => 0,
            );

            $result['questions'][$question->question_id]['variants'][_('Неверно')] = array(
                'title' => _('Неверно'),
                'count' => 0,
            );
            $result['questions'][$question->question_id]['count'] = 0;
        }

        $questionResult->score_max = $questionResult->score_max > 0 ? $questionResult->score_max : 1; // На ноль делить нельзя!

        $result['questions'][$question->question_id]['variants'][_('Верно')]['count'] =
        ($result['questions'][$question->question_id]['variants'][_('Верно')]['count']
            * $result['questions'][$question->question_id]['count']
            + ($questionResult->score_weighted / $questionResult->score_max) * 100
        )
            / ++$result['questions'][$question->question_id]['count'];

        $result['questions'][$question->question_id]['variants'][_('Неверно')]['count'] =
        100 - $result['questions'][$question->question_id]['variants'][_('Верно')]['count'];
    }

    /**
     * @method Подсчет распределения популярности ответов в баллах
     * Тип задания: Опрос
     * Тип вопроса: Упорядочиване.
     */
    private function _pollSortingPointsStat($question, &$questionResult)
    {
        // Создаём упорядоченную таблицу ответов пользователя
        $qResultVariant = unserialize($questionResult->variant);

        if (count($qResultVariant)) {
            $qResultVariant = array_flip($qResultVariant);
            ksort($qResultVariant);

            $variantsCount = $question->variants->count();

            if ($variantsCount > 0) {
                $this->_pollSortingRespondentsCount[$question->question_id]++;

                if (!isset($this->_pollSortingVariantsPopularity[$question->question_id])) {
                    $this->_pollSortingVariantsPopularity[$question->question_id] = [
                        'title' => $question->shorttext,
                        'variants' => [],
                        'totalValue' => 100,
                        'type' => $question->type,
                    ];

                    // Создаём таблицу вариантов ответов
                    foreach ($question->variants as $variant) {
                        $this->_pollSortingVariantsPopularity[$question->question_id]['variants'][$variant->question_variant_id] = [
                            'title' => $variant->variant,
                            'count' => 0,
                        ];
                    }
                }

                // Создаём таблицу распределения баллов
                if (!isset($this->_pollSortingVariantsPoints[$question->question_id])) {
                    for ($i = 0; $i < $variantsCount; $i++) {
                        $this->_pollSortingVariantsPoints[$question->question_id][$i + 1] = $variantsCount - $i;
                        $this->_pollSortingVariantsPointsSum[$question->question_id] += $variantsCount - $i;
                    }
                }

                // Распределяем баллы по вариантам ответов
                foreach ($qResultVariant as $position => $variantId) {
                    $this->_pollSortingVariantsPopularity[$question->question_id]['variants'][$variantId]['count'] += $this->_pollSortingVariantsPoints[$question->question_id][$position];
                }
            }
        }
    }

    /**
     * @method Подсчет распределения популярности ответов в процента.
     * Производится, исходя из результатов метода _pollSortingPointsStat()
     * Тип задания: Опрос
     * Тип вопроса: Упорядочиване.
     */
    private function _pollSortingPercentsStat(&$result)
    {
        if (count($this->_pollSortingVariantsPopularity)) {
            foreach ($this->_pollSortingVariantsPopularity as $questionId => &$value) {

                if (count($value['variants'])) {
                    foreach ($value['variants'] as &$v) {

                        $v['count'] = ($v['count']
                        / ($this->_pollSortingRespondentsCount[$questionId] * $this->_pollSortingVariantsPointsSum[$questionId])
                        ) * 100;
                    }
                    unset($v);

                    usort($value['variants'], function (&$a, &$b) {
                        return $b['count'] - $a['count'];
                    });
                }
            }

            $result['questions'] = array_merge($result['questions'], $this->_pollSortingVariantsPopularity);
        }
    }
}
