<?php
class Quest_ReportController extends HM_Controller_Action_Quest
{
    use HM_Controller_Action_Trait_Grid;
    //    protected $service;
    //    protected $idParamName;
    //    protected $idFieldName;
       protected $sessionId;
       protected $atEventsBackUrl = '';
    static  $_variants = null;

    public function init()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $questId = $this->_getParam('quest_id', 0);
        $this->sessionId = (int) $this->_getParam('session_id', 0);

        if ($subjectId) {

            $this->service = 'Subject';
            $this->idParamName  = 'subject_id';
            $this->idFieldName = 'subid';

            if (!$this->isAjaxRequest() && !$this->_getParam('print', 0)) {
                $this->_subject = $this->getOne($this->getService($this->service)->find($subjectId));

//                $this->view->setExtended(
//                        array(
//                                'subjectName' => $this->service,
//                                'subjectId' => $this->_subject->subid,
//                                'subjectIdParamName' => $this->idParamName,
//                                'subjectIdFieldName' => $this->idFieldName,
//                                'subject' => $this->_subject
//                        )
//                );
            }
        } elseif ($this->sessionId && !$questId) {

            $this->service = 'AtSession';
            $this->idParamName  = 'session_id';
            $this->idFieldName = 'session_id';

            $this->atEventsBackUrl = $this->_getAtEventsBackUrl();

            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
                if (count($this->_session->vacancy) || count($this->_session->newcomer)) {
                    $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page2'));
                }
            }
        } elseif ($questId) {
            $this->service = 'Quest';
        }

        $result = parent::init();

        if ($questId) {
            if (!$this->_quest) {
                $this->_quest = $this->getService('Quest')->findDependence(array('Cluster', 'QuestionQuest'), $questId);
            }

            $this->getService('Quest')->addQuestionsWithVariants($this->_quest);
        }
        
        return $result;
    }


    public function indexAction()
    {
        /*
         * Так как у нас пока нет универсального просмотра и вопросов и тастов
         */
        $questId = $this->_getParam('quest_id', 0);
        if ($questId) {
            $quest  = $this->_quest;

            $params = array('quest_id' => $questId);

            $subjectId = $this->_getParam('subject_id', 0);
            if ($subjectId) {
                $params['subject_id'] = $subjectId;
            }

            $feedbackId = $this->_getParam('feedback_id', 0);
            if ($feedbackId) {
                $params['feedback_id'] = $feedbackId;
            }

            $lessonId = $this->_getParam('lesson_id', 0);
            if ($lessonId) {
                $params['lesson_id'] = $lessonId;
            }

            switch ($quest->type) {
                case HM_Quest_QuestModel::TYPE_POLL:
                case HM_Quest_QuestModel::TYPE_FORM:
                    $this->_redirector->gotoSimple('poll', null, null, $params);
                    break;
                case HM_Quest_QuestModel::TYPE_TEST:
                    $this->_redirector->gotoSimple('questions', null, null, $params);
                    return;
                    break;
            }
        }
        $this->_redirectToIndex();

    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index');
    }

    protected function _getAtEventsBackUrl()
    {
        $url = $this->view->url([
                    'module' => 'session',
                    'controller' => 'event',
                    'action' => 'list',
                    'baseUrl' => 'at',
                    'session_id' => $this->sessionId,
        ], null, true);

        return $url;
    }

    public function attemptAction()
    {
        if (($subjectId = $this->_getParam('subject_id')) && ($lessonId = $this->_getParam('lesson_id')))
            $this->view->setBackUrl($this->view->url([
                'module' => 'lesson',
                'controller' => 'result',
                'action' => 'index',
                'lesson_id' => $lessonId,
                'subject_id' => $subjectId,
        ]));
        else if ($sessionId = $this->_getParam('session_id')) {
            $this->view->setBackUrl($this->view->url([
                'baseUrl' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'my',
                'session_id' => $sessionId,
            ]));
        }
        else if ($redirect = $this->_getParam('redirect')) {
            $this->view->setBackUrl(urldecode($redirect));
        }

        $this->view->print = $this->_getParam('print', 0);
        
        $newcomerId = $this->_getParam('newcomer_id', 0);
        $questId = $this->_getParam('quest_id', 0);
        
        $questAttemptId = $this->_getParam('attempt_id');
        
        if ($newcomerId) {
            $newcomer = $this->getService('RecruitNewcomer')->find($newcomerId)->current();
            $questAttempt = $this->getService('QuestAttempt')->fetchAll(
                    array(
                        'user_id = ?' => $newcomer->user_id,
                        'quest_id = ?' => $questId
                    ))->current();
            $questAttemptId = $questAttempt->attempt_id;
        }

        if (
            $this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL])
            && mb_strlen($this->atEventsBackUrl)
        ) {
            $this->view->setBackUrl($this->_getAtEventsBackUrl());
        }
        
        if (count($collection = $this->getService('QuestAttempt')->findDependence(array('Quest', 'User', 'QuestionResult', 'SessionEvent'), $questAttemptId))) {

            $this->_helper->viewRenderer->setNoRender();
            $questAttempt = $collection->current();
            
            $this->view->totalResult = ($totalResult = round(100 * $questAttempt->score_weighted)) . '%';

            if (count($questAttempt->quest)) {
                $quest = $questAttempt->quest->current();
            }
            //$this->view->setHeader(
            //
            //);

            $this->view->subheadername = HM_Quest_QuestModel::TYPE_TEST ? _('Отчет о тестировании') : _('Отчет о прохождении опроса');

            $userName = '';
            if (count($questAttempt->user)) {
                $user = $questAttempt->user->current();
                $userName = $user->getName();
            }

            if ($questAttempt->context_type == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT && count($questAttempt->sessionEvent)) {
                $sessionEvent = $questAttempt->sessionEvent->current();
                $candidate = $this->getService('User')->findOne(array('MID' => $sessionEvent->user_id));
                $userName = $candidate->getName();
            }

            $sameUserAttempts = $this->getService('QuestAttempt')->fetchAll(array(
                'context_event_id = ?' => $questAttempt->context_event_id,       
                'context_type = ?' => $questAttempt->context_type,
                'user_id = ?' => $user->MID
            ));

            $dtFormat = HM_Locale_Format::getDateTimeFormat(Zend_Locale::findLocale());
            
            $dateBegin = new HM_Date($questAttempt->date_begin);
            $dateEnd = new HM_Date($questAttempt->date_end);
            $this->view->lists = array();
            $this->view->lists['general-test'] = array(
                _('ФИО') => $userName,
                _('Оценочная форма') => $quest->name,
                _('Дата прохождения') => $dateEnd->toString($dtFormat),
                _('Затраченное время') => HM_Date::getDurationString($dateEnd->get(Zend_Date::TIMESTAMP) - $dateBegin->get(Zend_Date::TIMESTAMP)),
                _('Затрачено попыток') => count($sameUserAttempts),
            );
            
            $this->view->lists['general-context'] = $questAttempt->getReportContext();

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

            if (count($questAttempt->questionResults)) {
                
                $questionIds = $questAttempt->questionResults->getList('question_id');
                $questions = $this->getService('QuestQuestion')->fetchAllDependence(array('Variant', 'QuestionQuest'), array('question_id IN (?)' => $questionIds), 'question_id');

                $clusterIds = $this->getService('QuestQuestionQuest')->fetchAll(array('quest_id = ?' => $quest->quest_id))->getList('cluster_id');
                $clusters = $this->getService('QuestCluster')->fetchAll(array('cluster_id IN (?)' => $clusterIds))->getList('cluster_id', 'name', _('Вопросы без темы'));
                $questions = $questions->asArrayOfObjects();
                
                $scoreMaxSum  = 0;
                $scoreMinSum  = 0;
                $scoreUserSum = 0;
                $clusterResults = array();
                $clusterMin = array();
                $clusterMax = array();
                foreach ($questAttempt->questionResults as $questionResult) {
                    
                    $question = $questions[$questionResult->question_id];
                    if ($externalVariants) {
                        $question->variants = $externalVariants;
                    }
                    $result = $question->displayUserResult($questionResult);
                    
                    
                    $scoreDifference = $questionResult->score_max - $questionResult->score_min;
                    $scoreDifference = $scoreDifference == 0 ? 1 : $scoreDifference;
                    $questionList = array(
                        _('Вопрос') => $question->question,
                        _('Ответ') => $result,
                        _('Результат') => $questionResult->score_weighted . ' (' . round(($questionResult->score_weighted - $questionResult->score_min) * 100 / $scoreDifference) . '%)',
                    );
                    if($question->type == HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER) {
                        unset($questionList[_('Вопрос')]);
                    }
                    $this->view->lists['question-' . $questionResult->question_id] = $questionList;
                    
                    if($quest->type == HM_Quest_QuestModel::TYPE_TEST){
                         $this->view->lists['question-' . $questionResult->question_id][_('Диапазон баллов')] =
                             _('От').' '.round($question->score_min, 2).' '._('до').' '.round($question->score_max, 2);
                         $scoreMaxSum  += $question->score_max;
                         $scoreMinSum  += $question->score_min;
                         $scoreUserSum += $questionResult->score_weighted;
                    }
                    
                    
                    if($question->questionQuest){
                        $questionQuest = $question->questionQuest->getList('question_id', 'cluster_id');
                        $clusterId = $questionQuest[$questionResult->question_id];
                        
                        if (isset($questionResult->score_weighted)) {
                            $clusterResults[$clusterId] +=  $questionResult->score_weighted;
                        } elseif (isset($questionResult->is_correct)) {
                            if ($questionResult->is_correct) {
                                $clusterResults[$clusterId] += $questionResult->score_max;
                            } else {
                                $clusterResults[$clusterId] += $questionResult->score_min;
                            }
                        }
                        $clusterMin[$clusterId] += $questionResult->score_min;
                        $clusterMax[$clusterId] += $questionResult->score_max;
                    }
                    
                }
                if($quest->type == HM_Quest_QuestModel::TYPE_TEST){
                    $this->view->lists['general-context'][_('Диапазон баллов')] = _('От').' '.round($scoreMinSum, 2).' '._('до').' '.round($scoreMaxSum, 2);
                    $this->view->lists['general-context'][_('Набрано баллов')] = round($scoreUserSum, 2);
                }

                $this->view->lists['clusters'] = array();
                if (count($clusterResults) > 1) {

                    foreach ($clusterResults as $clusterId => $clusterResult) {
                        $this->view->lists['clusters'][$clusters[$clusterId]] = round(100 * ($clusterResult-$clusterMin[$clusterId]) / ($clusterMax[$clusterId] - $clusterMin[$clusterId])) . '%'; 
                    }
                }

                if (is_array($this->view->lists['clusters'])) {
                    ksort($this->view->lists['clusters']);
                }

                $this->view->lists['clusters'] = $this->getService('QuestAttemptCluster')->getAttemptResults($questAttemptId);
                
                $categoryResults = array();
                if (count($categoryResults = $this->getService('QuestCategoryResult')->fetchAll(array('attempt_id = ?' => $questAttempt->attempt_id)))) {
                    $this->view->lists['categories'] = array();
                    foreach ($categoryResults as $categoryResult) {
                        if (count($categoryResult->category)) {
                            $category = $categoryResult->category->current();
                            $this->view->lists['categories'][$category->name] = sprintf('%s (%s)', $categoryResult->result, $categoryResult->score_raw);
                        }
                    }
                }
                
                $this->view->questions = $questions;
            }

            $this->applyModifiers($quest->type);
            echo $this->render("attempt/{$quest->type}");
            
        } else {
            if ($newcomer) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Результаты выполнения не найдены')));
                $this->_redirector->gotoUrl($this->view->url(array(
                    'module' => 'newcomer',
                    'controller' => 'report',
                    'action' => 'index',
                    'baseUrl' => 'recruit',
                    'newcomer_id' =>$newcomer->newcomer_id,
                )), array('prependBase' => false));
            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Результаты выполнения не найдены')));
                $this->_redirector->gotoSimple('index', 'index', 'default');
            }
        }
    }

    // @todo: рефакторить по аналогии с HM_Form_Modifier
    public function applyModifiers($type)
    {
        switch ($type){
            case HM_Quest_QuestModel::TYPE_TEST:
                break;
            case HM_Quest_QuestModel::TYPE_POLL:
                unset($this->view->lists['general-test'][_('Затрачено попыток')]);
                foreach($this->view->lists as &$list) {
                    unset($list[_('Результат')]);
                }
                break;

            case HM_Quest_QuestModel::TYPE_PSYCHO:
                unset($this->view->lists['clusters']);
                unset($this->view->lists['general-test'][_('Затрачено попыток')]);
                foreach($this->view->lists as &$list) {
                    unset($list[_('Результат')]);
                }
                break;

            case HM_Quest_QuestModel::TYPE_FORM:
                unset($this->view->lists['clusters']);
                unset($this->view->lists['general-test'][_('Затрачено попыток')]);
                unset($this->view->lists['general-test'][_('Затраченное время')]);
                foreach($this->view->lists as &$list) {
                    unset($list[_('Результат')]);
                }
                break;
        }
    }

    public function feedbackAction()
    {
        $this->view->print = $this->_getParam('print', 0);
        
        $questId   = $this->_getParam('quest_id');
        $quest     = $this->getService('Quest')->fetchAllDependence(array('Settings', 'Cluster', 'QuestionQuest'),
            array('quest_id = ?' => $questId))->current();
        $subjectId = $this->_getParam('subject_id');
        $subject   = $this->getService('Subject')->find($subjectId)->current();

        if (!$quest || !$subject || ($quest->type != HM_Quest_QuestModel::TYPE_POLL)) {
            //$this->_redirect('/');
        }

        $general = array(
            _('Курс')  => $subject->name,
            _('Опрос') => $quest->name,
        );

        $externalVariants = array();
        if ($quest->scale_id > 0) {
            $scaleValues = Zend_Registry::get('serviceContainer')->getService('ScaleValue')
                ->fetchAll('scale_id='.$quest->scale_id);
            foreach ($scaleValues as $scaleValue) {
                $externalVariants[$scaleValue->value_id] = $scaleValue->text;
            }
        }

        $select = $this->getService('Feedback')->getSelect();
        $select
            ->from(
                array('sf' => 'subjects_feedback'),
                array(
                    'r.question_id',
                    'r.variant',
                    'cnt' => new Zend_Db_Expr('COUNT(r.question_result_id)'),
                ))
            ->joinInner(
                array('a' => 'quest_attempts'),
                'sf.feedback_id=a.context_event_id and a.context_type='.HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK,
                array())
            ->joinInner(array('r' => 'quest_question_results'),
                'r.attempt_id=a.attempt_id',
                array())
            ->where('sf.subject_id=?', $subjectId)
            ->where('sf.quest_id=?',   $questId)
            ->where('sf.status=?',     HM_Subject_Feedback_FeedbackModel::STATUS_FINISHED)
            ->group(array('r.question_id', 'r.variant'));

        $feedbackData = $select->query()->fetchAll();

        $questions = Zend_Registry::get('serviceContainer')->getService('QuestQuestion')
            ->fetchAllDependence(array('Variant', 'QuestionQuest'),
            array('question_id in(?)' => $quest->questionQuest->getList('question_id')));

        $clusters = array();
        if ($quest->clusters) {
            foreach ($quest->clusters as $cluster) {
                $clusters[$cluster->id] = array('title' => $cluster->name);
            }
            $clusters[0] = array('title' => _('Блок по умолчанию'));
        } else {
            $clusters[0] = array('title' => '');
        }

        $result = array();
        foreach ($questions as $question) {
            if (!$question) {
                continue;
            }
            $clusterId = 0;
            foreach($question->questionQuest as $assign) {
                if ($assign->quest_id == $questId) {
                    $clusterId = $assign->cluster_id;
                    break;
                }
            }

            $questionGraph = array(
                'graphs' => array(
                    $question->question_id => array(
                        'legend' => _('Выбрали'),
                        'title'  => $question->shorttext,
                        'color'  => '#FFFF00'
                    )
                ),
                'data'  => array(),
                'totalValue' => 0,
                'maxValue' => 0,
            );

            if ($externalVariants) {
                foreach ($externalVariants as $varId => $varName) {
                    $questionGraph['data'][$varId] = array(
                        'title' => $varName,
                        $question->question_id => 0
                    );
                }
            } else {
                foreach ($question->variants as $variant) {
                    $questionGraph['data'][$variant->question_variant_id] = array(
                        'title' => $variant->variant,
                        $question->question_id => 0
                    );
                }
            }

            if (!is_array($clusters[$clusterId])) {
                $clusters[$clusterId] = array(
                    'title' => $clusters[$clusterId],
                    'questions' => array()
                );
            }
            $clusters[$clusterId]['questions'][] = $question->question_id;
            $result[$question->question_id] = $questionGraph;
        }

        foreach ($feedbackData as $row) {
            if (!empty($result[$row['question_id']])) {
                $multi = unserialize($row['variant']);
                if (!$multi) {
                    $multi = array($row['variant']);
                }
                foreach ($multi as $variant) {
                    if(in_array($variant, array_keys($result[$row['question_id']]['data']))){
                        $result[$row['question_id']]['data'][$variant][$row['question_id']] = $row['cnt'];
                        if ($row['cnt'] > $result[$row['question_id']]['maxValue']) {
                            $result[$row['question_id']]['maxValue'] = $row['cnt'];
                        }
                        $result[$row['question_id']]['totalValue'] += $row['cnt'];
                    }
                }
            }
        }

        $this->view->general  = $general;
        $this->view->feedback = $result;
        $this->view->clusters = $clusters;
    }

    public function pollWidgetAction()
    {
        if ($this->isAjaxRequest()) {
            $name = $this->_getParam('name');
            echo _('Вы уже прошли опрос "'.$name.'", благодарим за обратную связь.');

        }
        $userId = $this->getService('User')->getCurrentUserId();
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator($userId);
        if ($isModerator)
        {

        }
        die;
    }


    public function pollAction()
    {
        $questId = $this->_getParam('quest_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $feedbackId = $this->_getParam('feedback_id', 0);
        $fromWidget = $this->_getParam('fromwidget', false);
        $showContextFilter = true;

        if ($subjectId or $feedbackId or $fromWidget) {
            $showContextFilter = false;
        }

        $quest = $this->_quest;

        if ($quest->scale_id) {
            $select = $this->_getDetailedSelectScale();
        } else {
            $select = $this->_getDetailedSelect();
        }

        $select->where('q.quest_id = ?', $questId);

        $filters = array(
            'fio' => null,
            'shorttext' => null,
            'text' => null,
            'comment' => null,
            'date' => array(
                'render' => 'Date',
                'callback' => ['function' => [$this, 'filterQuestionsDate']],
            ),
            'cluster' => null,
        );

        // if($showContextFilter) {
        //     $filters['context'] = [
        //         'values' => $this->getContextFilterList(),
        //         'callback' => ['function' => [$this, 'filterPollContext']],
        //     ];
        // }

        $grid = $this->getGrid($select,
            array(
                'question_result_id' => array(
                    'hidden' => true
                ),
                'question_type' => array(
                    'hidden' => true
                ),
                'show_feedback' => array(
                    'hidden' => true
                ),
                'free_variant' => array(
                    'hidden' => true
                ),
                'fio' => array(
                    'title' => _('ФИО'),
                    'callback' => array(
                        'function'=> array($this, 'anonimousName'),
                        'params'=> array('{{fio}}', '{{user_id}}', '{{attempt_id}}')
                    )
                ),
                'user_id' =>  array(
                    'hidden' => true
                ),
                'attempt_id' =>  array(
                    'hidden' => true
                ),
                'shorttext' => array(
                    'title' => _('Краткий текст вопроса'),
                ),
                'variant' => array(
                    'title' => _('Ответ'),
                    'callback' => array(
                        'function'=> array($this, 'updateVariant'),
                        'params'=> array('{{variant}}', '{{free_variant}}', '{{question_id}}')
                    )
                ),
                'variant_name' => array(
                    'hidden' => true
                ),
                'date' => array(
                    'title' => _('Дата прохождения'),
                    'callback' => array(
                        'function'=> array($this, 'updateDate'),
                        'params'=> array('{{date}}')
                    )
                ),
                'comment' => array(
                    'title' => _('Комментарий'),
                ),
                'context' => [
                    'hidden' => true,
                    'title' => _('Место использования'),
                    'callback' => [
                        'function' => array($this, 'updateQuestionsContext'),
                        'params' => array('{{context}}')
                    ]
                ],
            ),
            $filters
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'report',
                'action' => 'show-review',
            ),
            array('question_result_id'),
            _('Опубликовать отзыв')
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'report',
                'action' => 'hide-review',
            ),
            array('question_result_id'),
            _('Скрыть отзыв')
        );

        $grid->setActionsCallback([
            'function' => array($this, 'updateActions'),
            'params' => array('{{question_type}}', '{{show_feedback}}'),
        ]);

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->isAjaxRequest = $this->isAjaxRequest();

        $this->view->disabledModes = array();
        if (!$quest->scale_id && $quest->type == HM_Quest_QuestModel::TYPE_POLL) {
            $this->view->disabledModes = array('mca:quest:report:questions');
        }
        if ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
            $this->view->disabledModes = array('mca:quest:report:poll');
        }
    }

    public function filterPollContext($data)
    {
        $context = $data['value'];
        $select = $data['select'];
        $questId = $this->_questId;

        switch ($context) {
            case 'all':
            case null:
                break;
            case 'widget':
                $select->where('qa.context_type = ?', HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET);
                $select->where('qa.context_event_id = ?', $questId);
                break;
            default:
                $contextParts = explode('_', $context);
                switch ($contextParts[0]) {
                    case 'subject':
                        if (isset($contextParts[1]) && $contextParts[1]) {

                            $lessonIds = $this->getService('Lesson')->fetchAll(array(
                                'CID = ?' => (int) $contextParts[1],
                                'typeID = ?' => HM_Event_EventModel::TYPE_POLL
                            ))->getList('SHEID');

                            $where = false;
                            if (count($lessonIds)) {
                                $where = $this->getService('Feedback')->quoteInto(
                                    array(
                                        ' ( qa.context_type = ? AND ',
                                        ' qa.context_event_id IN (?) ) '
                                    ),
                                    array(
                                        HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING,
                                        $lessonIds,
                                    )
                                );
                            }
                            if ($where) {
                                $select->where($where);
                            }
                            else {
                                $select->where('NULL IS NOT NULL');
                            }

                        }
                        break;
                    case 'project':
                        if (isset($contextParts[1]) && $contextParts[1]) {

                            $meetingIds = $this->getService('Meeting')->fetchAll(array(
                                'project_id = ?' => (int) $contextParts[1],
                                'typeID = ?' => HM_Event_EventModel::TYPE_POLL
                            ))->getList('meeting_id');

                            $where = false;
                            if (count($meetingIds)) {
                                $where = $this->getService('Feedback')->quoteInto(
                                    array(
                                        ' ( qa.context_type = ? AND ',
                                        ' qa.context_event_id IN (?) ) '
                                    ),
                                    array(
                                        HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT,
                                        $meetingIds,
                                    )
                                );
                            }
                            if ($where) {
                                $select->where($where);
                            }
                            else {
                                $select->where('NULL IS NOT NULL');
                            }
                        }
                        break;
                    case 'feedback':
                        if (isset($contextParts[1]) && $contextParts[1]) {

                            $feedbackIds = $this->getService('Feedback')->fetchAll(
                                array('feedback_id = ?' => (int) $contextParts[1])
                            )->getList('feedback_id');

                            $feedbackUserIds = $this->getService('FeedbackUsers')->fetchAll(
                                array('feedback_id IN (?)' => $feedbackIds)
                            )->getList('feedback_user_id');

                            if (count($feedbackUserIds)) {
                                $select->where('qa.context_type IN(?)', array(HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK));
                                $select->where('qa.context_event_id IN (?)', $feedbackUserIds);
                            } else {
                                $select->where('NULL IS NOT NULL');
                            }

                        }
                        break;
                }
                break;
        }
    }

    public function diagramAction()
    {
        $questId = $this->_getParam('quest_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $feedbackId = $this->_getParam('feedback_id', 0);

        if ($this->_getParam('no-layout', 0)) {
            $this->getService('Unmanaged')->getController()->setView('DocumentBlank');
            $this->view->withoutContextMenu = $this->_getParam('withoutContextMenu', true);
            $this->view->noLayout = 1;
        }

        $dateFrom = $this->_getParam('from', null);
        $dateTo = $this->_getParam('to', null);
        $context = $this->_getParam('context', null);

        if ($dateFrom || $dateTo || $context) {

            $sr = array();

            $sr['from'] = $dateFrom;
            $sr['to'] = $dateTo;
            $sr['context'] = $context;

            $_SESSION['report_'.$questId] = $sr;
        } else {
            $sr = $_SESSION['report_'.$questId];

            $dateFrom = $this->_getParam('from', $sr['from']);
            $dateTo = $this->_getParam('to', $sr['to']);
            $context = $this->_getParam('context', $sr['context']);
        }
        if ($subjectId) {
            $context = 'subject_'.$subjectId;
            $this->view->hideContextFilter = true;
        }

        if ($feedbackId) {
            $context = 'feedback_'.$feedbackId;
            $this->view->hideContextFilter = true;
        }


        if ($this->_getParam('fromwidget', false)) {
            $this->view->hideContextFilter = true;
            $this->view->disableExtendedFile();
        }

        $currentFilter = $context;

        $quest = $this->_quest;

        switch ($context) {
            case 'all':
            case null:
                break;
            case 'widget':
                $contextType = HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET;
                $contextId = 0;
                break;
            default:
                $contextParts = explode('_', $context);
                switch ($contextParts[0]) {
                    case 'subject':
                        if ($quest->type == HM_Quest_QuestModel::TYPE_POLL) {
                            $contextType = HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING;
                        } elseif ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
                            $contextType = HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING;
                        }
                        $contextId = $contextParts[1];
                        break;
                    case 'project':
                        if ($quest->type == HM_Quest_QuestModel::TYPE_POLL) {
                            $contextType = HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT;
                        } elseif ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
                            $contextType = HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT;
                        }
                        $contextId = $contextParts[1];
                        break;
                    case 'feedback':
                        if (isset($contextParts[1]) && $contextParts[1]) {
                            $contextType = array(HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK);
                        }

                        $contextId = $contextParts[1];
                        break;
                }
                break;
        }

        $stat = $this->getService('Quest')->getAnswerStat($questId, $contextType, $contextId, $dateFrom, $dateTo);
        $this->view->stat = $stat;

        $filterList = array();
        $filterList['all'] = 'Везде';
        if ($quest->type == HM_Quest_QuestModel::TYPE_POLL) {
            $filterList['widget'] = _('виджет "Опросы"');
        }

        $subjectQuests = $this->getService('SubjectQuest')->fetchAllDependence('Subject',
            $this->quoteInto('quest_id = ?', $questId)
        );

        foreach ($subjectQuests as $subjectQuest) {
            $subjects = $subjectQuest->subjects;
            if($subjects){
                $subject = $subjects->current();
                $filterList['subject_'.$subject->subid] = $subject->name;
            }
        }


        $feedbackQuests = $this->getService('Feedback')->fetchAll(
            $this->quoteInto('quest_id = ?', $questId)
        );

        foreach ($feedbackQuests as $feedbackQuest) {
            $filterList['feedback_'.$feedbackQuest->feedback_id] = $feedbackQuest->name;
        }
        
        $this->view->filterList = $filterList;
        $this->view->currentFilter = $currentFilter;

        $this->view->dates = array();
        if ($dateFrom !== null) {
            $this->view->dates['from'] = date('d.m.Y', strtotime($dateFrom));
        }

        if ($dateTo !== null) {
            $this->view->dates['to'] = date('d.m.Y', strtotime($dateTo));
        }

        $this->view->disabledModes = array();
        if (!$quest->scale_id && $quest->type == HM_Quest_QuestModel::TYPE_POLL) {
            $this->view->disabledModes = array('mca:quest:report:questions');
        }
        if ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
            $this->view->disabledModes = array('mca:quest:report:poll');
        }
    }


    public function questionsAction()
    {
        $questId = $this->_getParam('quest_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $feedbackId = $this->_getParam('feedback_id', 0);
        $fromWidget = $this->_getParam('fromwidget', false);
        $showContextFilter = true;

        if ($subjectId or $feedbackId or $fromWidget) {
            $showContextFilter = false;
        }

        $quest = $this->_quest;

        if ($quest->type == HM_Quest_QuestModel::TYPE_POLL && $quest->scale_id != 0) {
            $result = new Zend_Db_Expr('ROUND(AVG(sv.value), 2)');
        } else {
            $result =  new Zend_Db_Expr('CONCAT(CONCAT(ROUND((AVG(
            (CASE WHEN qqr.score_weighted IS NULL THEN 0 ELSE qqr.score_weighted END)
             / CASE WHEN qqr.score_max > 0 THEN qqr.score_max ELSE 1 END) * 100), 0), \' \'),\'%\')');
        }

        $select = $this->getService('Quest')->getSelect();
        $adapter = $select->getAdapter();
        $convertValueId = is_a($adapter, 'HM_Db_Adapter_Pdo_Mysql') ? 'CONVERT (sv.value_id, char(255))' : 'CONVERT (varchar(255),sv.value_id)';
        $convertVariant = is_a($adapter, 'HM_Db_Adapter_Pdo_Mysql') ? 'CONVERT (qqr.variant, char(255))' : 'CONVERT (varchar(255),qqr.variant)';
        $select->from(
            array(
                'qq' => 'quest_questions'
            ),
            array(
                'text' => 'qq.shorttext',
                'cluster' => 'qc.name',
                'result'    => $result,
                'quest_date' => new Zend_Db_Expr('1'),
//                'context' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT qa.context_type)'),
                'min_date' => new Zend_Db_Expr('MIN(qa.date_end)'),
                'max_date' => new Zend_Db_Expr('MAX(qa.date_end)'),
            )
        );


        $select
            ->joinInner(array('qqq' => 'quest_question_quests'), 'qqq.question_id = qq.question_id', array())
            ->joinInner(array('q' => 'questionnaires'), 'q.quest_id = qqq.quest_id', array())
            ->joinInner(array('qa' => 'quest_attempts'), 'q.quest_id = qa.quest_id', array())
            ->joinInner(array('qqr' => 'quest_question_results'), 'qqr.attempt_id = qa.attempt_id AND  qqr.question_id = qq.question_id', array())
            ->joinLeft(array('qc' => 'quest_clusters'), 'qc.cluster_id = qqq.cluster_id', array())
            ->group(array('qq.question_id', 'qq.shorttext', 'qc.name'))
        ;

        if ($quest->type == HM_Quest_QuestModel::TYPE_POLL &&
            $quest->scale_id != 0
        ) {
            $select
                ->joinLeft(array('sv' => 'scale_values'), $convertVariant . ' = ' . $convertValueId, array());
        }

        //статистика только по существующим курсам
        if ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
            $select->joinInner(
                array('sch' => 'schedule'), 
                $this->getService('Lesson')->quoteInto(
                    array('context_type = ? AND context_event_id = sch.SHEID'),
                    array(HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING)
                ),
                array()
            );
            $select->joinInner(
                array('s' => 'subjects'),
                'sch.CID = s.subid',
                array()
            );
        }

        // @todo: а как же проекты??!
        
        $select->where('q.quest_id = ?', $questId);
        $select->where('qa.status = ?', HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED);

        $filters = array(
            'text' => null,
            'quest_date' => array(
                'render' => 'Date',
                'callback' => ['function' => [$this, 'filterQuestionsDate']],
            ),
            'cluster' => null,
        );

        //if($showContextFilter) {
        //    $filters['context'] = [
        //        'values' => $this->getContextFilterList(),
        //        'callback' => ['function' => [$this, 'filterQuestionsContext']],
        //    ];
        //}

        $grid = $this->getGrid($select,
            array(
                'question_result_id' => ['hidden' => true],
                'min_date' => ['hidden' => true],
                'max_date' => ['hidden' => true],
                'text' => array(
                    'title' => _('Краткий текст вопроса'),
                ),
                'cluster' => array(
                    'title' => _('Блок вопросов'),
                ),
                'result' => array(
                    'title' => _('Результат'),
                ),
                'quest_date' => array(
                    'title' => _('Дата'),
                    'callback' => [
                        'function' => [$this, 'updateQuestionsDate'],
                        'params' => ['{{min_date}}', '{{max_date}}'],
                    ]
                ),
                'context' => [
                    'hidden' => true,
                    'title' => _('Место использования'),
                    'callback' => [
                        'function' => array($this, 'updateQuestionsContext'),
                        'params' => array('{{context}}')
                    ]
                ],
            ),
            $filters
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
        
        $this->view->disabledModes = array();
        if (!$quest->scale_id && $quest->type == HM_Quest_QuestModel::TYPE_POLL) {
            $this->view->disabledModes = array('mca:quest:report:questions');
        }
        if ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
            $this->view->disabledModes = array('mca:quest:report:poll');
        }
    }

    public function filterQuestionsDate($data)
    {
        $dates = $data['value'];
        list($startDate, $endDate) = explode(',', $dates);

        $dateFilter = new Bvb_Grid_Filters_Render_Date;

        $select = $data['select'];

        if(!empty($startDate)) {
            $startDate = $dateFilter->transform($startDate, 'from');
            $select->where('qa.date_end > ?', $startDate);
        }

        if(!empty($endDate)) {
            $endDate = $dateFilter->transform($endDate, 'to');
            $select->where('qa.date_end < ?', $endDate);
        }
    }

    public function getContextFilterList()
    {
        $quest = $this->_quest;
        $questId = $this->_questId;

        $filterList = array();
        $filterList['all'] = 'Везде';

        if ($quest->type == HM_Quest_QuestModel::TYPE_POLL) {
            $filterList['widget'] = _('виджет "Опросы"');
        }

        $subjectQuests = $this->getService('SubjectQuest')->fetchAllDependence(
            'Subject',
            $this->quoteInto('quest_id = ?', $questId)
        );

        foreach ($subjectQuests as $subjectQuest) {
            $subjects = $subjectQuest->subjects;
            if($subjects){
                $subject = $subjects->current();
                $filterList['subject_'.$subject->subid] = $subject->name;
            }
        }

        $feedbackQuests = $this->getService('Feedback')->fetchAll(
            $this->quoteInto('quest_id = ?', $questId)
        );

        foreach ($feedbackQuests as $feedbackQuest) {
            $filterList['feedback_'.$feedbackQuest->feedback_id] = $feedbackQuest->name;
        }

        return $filterList;
    }

    public function updateQuestionsDate($minDate, $maxDate)
    {
        $format = 'd.m.Y';
        $minDate = date($format, strtotime($minDate));
        $maxDate = date($format, strtotime($maxDate));

        if($minDate == $maxDate) {
            return $minDate;
        } else {
            return $minDate . ' — ' . $maxDate;
        }
    }

    public function filterQuestionsContext($data)
    {
        $select = $data['select'];
        $context = trim($data['value']);

        $questId = $this->_questId;
        $quest = $this->_quest;

        switch ($context) {
            case 'all':
            case null:
                break;
            case 'widget':
                $select->where('qa.context_type = ?', HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET);
                $select->where('qa.context_event_id = ?', $questId);
                break;
            default:
                $contextParts = explode('_', $context);
                switch ($contextParts[0]) {
                    case 'subject':
                        if (!empty($contextParts[1])) {
                            if ($quest->type == HM_Quest_QuestModel::TYPE_POLL) {
                                $lessonIds = $this->getService('Lesson')->fetchAll(array(
                                    'CID = ?' => (int) $contextParts[1],
                                    'typeID = ?' => HM_Event_EventModel::TYPE_POLL
                                ))->getList('SHEID');

                                $allContextEventIds = $lessonIds;

                                if (count($allContextEventIds)) {
                                    $select->where('qa.context_type IN(?)', array(HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING));
                                    $select->where('qa.context_event_id IN (?)', $allContextEventIds);
                                } else {
                                    $select->where('NULL IS NOT NULL');
                                }
                            }

                            if ($quest->type == HM_Quest_QuestModel::TYPE_TEST) {
                                $shIds = $this->getService('Lesson')->fetchAll(
                                    array('CID = ?' => (int) $contextParts[1])
                                )->getList('SHEID');

                                if (count($shIds)) {
                                    $select->where('qa.context_type = ?', HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING);
                                    $select->where('qa.context_event_id IN (?)', $shIds);
                                } else {
                                    $select->where('NULL IS NOT NULL');
                                }
                            }
                        }
                        break;
                    case 'feedback':
                        if (isset($contextParts[1]) && $contextParts[1]) {

                            if ($quest->type == HM_Quest_QuestModel::TYPE_POLL) {

                                $feedbackUserIds = $this->getService('FeedbackUsers')->fetchAll(
                                    array('feedback_id IN (?)' => (int) $contextParts[1])
                                )->getList('feedback_user_id');


                                $select->where('qa.context_type = ?', HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK);
                                $select->where('qa.context_event_id IN (?)', $feedbackUserIds);
                            }
                        }
                        break;
                }
                break;
        }
    }

    public function updateQuestionsContext($contexts)
    {
        $contexts = explode(',', $contexts);
        array_walk($contexts, function(&$context) {
            $context = HM_Quest_Attempt_AttemptModel::getContextTypeName($context);
        });
        return '<p>'.implode('</p><p>', $contexts).'</p>';
    }

    protected function _getDetailedSelectScale()
    {
        $select = $this->getService('Quest')->getSelect();
        $adapter = $select->getAdapter();
        $convertValue   = is_a($adapter, 'HM_Db_Adapter_Pdo_Mysql') ? 'CONVERT (s.value, char(255))' : 'CONVERT (varchar(255),s.value)';
        $convertValueId = is_a($adapter, 'HM_Db_Adapter_Pdo_Mysql') ? 'CONVERT (s.value_id, char(255))' : 'CONVERT (varchar(255),s.value_id)';
        $convertVariant = is_a($adapter, 'HM_Db_Adapter_Pdo_Mysql') ? 'CONVERT (qqr.variant, char(255))' : 'CONVERT (varchar(255),qqr.variant)';
        $select->from(
            array(
                'qqr' => 'quest_question_results'
            ),
            array(
                'question_result_id' => 'qqr.question_result_id',
                'attempt_id' => 'qa.attempt_id',
                'question_id' => 'qqr.question_id',
                'fio'          => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'user_id'    => 'p.MID',
                'shorttext'    => 'qq.shorttext',
                'variant'      => new Zend_Db_Expr("CASE WHEN (qq.type = '".HM_Quest_Question_QuestionModel::TYPE_FREE."') 
                    THEN qqr.free_variant ELSE " . $convertValue . " END"),
//                'variant'      => 's.value',
                'free_variant' => 'qqr.free_variant',
                'variant_name' => 's.text',
                'date'         => 'qa.date_end',
                'comment'      => 'qqr.comment',
                'context' => 'qa.context_type',
                'question_type' => 'qq.type',
                'show_feedback' => 'qqr.show_feedback',
            )
        );

        $select
            ->joinInner(array('qa' => 'quest_attempts'), 'qqr.attempt_id = qa.attempt_id', array())
            ->joinInner(array('q' => 'questionnaires'), 'q.quest_id = qa.quest_id', array())
            ->joinInner(array('qq' => 'quest_questions'), 'qqr.question_id = qq.question_id', array())
            ->joinLeft(array('s' => 'scale_values'), 'q.scale_id = s.scale_id 
                AND ' . $convertVariant . ' = ' . $convertValueId . ' ', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = qa.user_id', array())

            ->where('qa.status = ?', HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED)
//            ->group('qqr.question_result_id','fio','shorttext')
        ;
        return $select;
    }

    protected function _getDetailedSelect()
    {

        $select = $this->getService('Quest')->getSelect();
        $select->from(
            array(
                'qqr' => 'quest_question_results'
            ),
            array(
                'question_result_id' => 'qqr.question_result_id',
                'attempt_id' => 'qa.attempt_id',
                'question_id' => 'qqr.question_id',
                'fio'          => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'user_id'    => 'p.MID',
                'shorttext'    => 'qq.shorttext',
                'variant'      => 'qqr.variant',
                'free_variant' => 'qqr.free_variant',
                'date'         => 'qa.date_end',
                'comment'      => 'qqr.comment',
                'context' => 'qa.context_type',
                'question_type' => 'qq.type',
                'show_feedback' => 'qqr.show_feedback',
            )
        );

        $select
            ->joinInner(array('qa' => 'quest_attempts'), 'qqr.attempt_id = qa.attempt_id', array())
            ->joinInner(array('q' => 'questionnaires'), 'q.quest_id = qa.quest_id', array())
            ->joinInner(array('qq' => 'quest_questions'), 'qqr.question_id = qq.question_id', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = qa.user_id', array())

            ->where('qa.status = ?', HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED)
//            ->group('qqr.question_result_id','fio','shorttext')
        ;
        return $select;
    }

    public function anonimousName($fio, $user_id, $attempt_id) {
        if (!$user_id) {
            return _('Пользователь #').$attempt_id;
        }
        return $fio;

    }

    public function updateVariant($variant, $freeVariant, $questionId)
    {
        $quest = $this->_quest;

        if ($quest->scale_id) {
            return $variant;
        }

        $questions = clone $quest->questionQuest->questions;

        $question = null;
        foreach ($questions as $q) {
            if ((int) $questionId === (int) $q->question_id) {
                $question = $q;
                break;
            }
        }

        if (!is_null($question)) {
            $questionResult = new stdClass();
            $questionResult->variant = $variant;

            if (!empty($freeVariant)) {
                $questionResult->free_variant = $freeVariant;
            }

            return $question->displayUserResult($questionResult, '; ', HM_Quest_Question_QuestionModel::RESULT_CONTEXT_DETAILED);
        }

        return '';
    }

    /** 
     *  @deprecated?
     */
    public static function getQuestVariants($questId)
    {
        if (self::$_variants == null) {
            $service = Zend_Registry::get('serviceContainer')->getService('QuestQuestionVariant');

            $select = $service->getSelect();
            $select->from(
                array(
                    'qqv' => 'quest_question_variants'
                ),
                array(
                    'question_variant_id' => 'qqv.question_variant_id',
                    'variant'    => 'qqv.variant',
                )
            );
            $select
                ->joinInner(array('qqq' => 'quest_question_quests'), 'qqq.question_id = qqv.question_id', array());
            $select->where('qqq.quest_id = ?', $questId);

            $variants = array();

            foreach ($select->query()->fetchAll() as $row) {
                $variants[$row['question_variant_id']] = $row['variant'];
            }
            self::$_variants = $variants;
        }
        return self::$_variants;

    }

    public function updateDate($date)
    {
        return date('d.m.Y H:i',strtotime($date));
    }

    public function wordAttemptAction()
    {
        $data = $options = $filesMapping = array();

        $questAttemptId = $this->_getParam('attempt_id');

        if (count($collection = $this->getService('QuestAttempt')->findDependence(array('Quest', 'User', 'QuestionResult', 'Lesson'), $questAttemptId))) {

            $this->_helper->viewRenderer->setNoRender();
            $questAttempt = $collection->current();

            if (count($questAttempt->quest)) {
                $quest = $questAttempt->quest->current();
            }

            $userName = '';
            if (count($questAttempt->user)) {
                $user = $questAttempt->user->current();
                $data['FIO'] = $userName = $user->getName();

                $position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->fetchAllDependence('Parent', array('mid = ?' => $user->MID)));
                if ($position) {
                    $department = count($position->parent) ? $position->parent->current() : false;
                }
                $data['DEPARTMENT'] = $department ? $department->name : '';
            }

            $data['DATE'] = date('d.m.Y');

            $rightAnswers = $wrongAnswers = 0;
            if (count($questAttempt->questionResults)) {
                foreach ($questAttempt->questionResults as $questionResult) {
                    if ($questionResult->score_weighted == 1) $rightAnswers++;
                }
                $wrongAnswers = count($questAttempt->questionResults) - $rightAnswers;
            }
            $data['NUM_RIGHT_ANSW'] = $rightAnswers;
            $data['NUM_WRONG_ANSW'] = $wrongAnswers;


            $mark = '';
            if (($questAttempt->context_type == HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING) && $questAttempt->is_resultative) {
                if (count($questAttempt->lesson)) {
                    $lesson = $questAttempt->lesson->current();
                    $scaleId = $lesson->getScale();

                    $lessonAssign = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array(
                        'MID = ?' => $user->MID,
                        'SHEID = ?' => $questAttempt->context_event_id,
                    )));
                    if ($lessonAssign) {
                        $mark = HM_Scale_Value_ValueModel::getTextStatus($scaleId, $lessonAssign->V_STATUS);
                    }

                }
            }
            $data['MARK'] = $mark;

            $data['table_1'] = array(array('','','','',''));
            if (count($questAttempt->questionResults)) {
                $data['table_1'] = array();

                $questions = array();
  	        $pattern = '/[^ А-Яа-я0-9;,\?\.\-]/u';
                $questionIds = $questAttempt->questionResults->getList('question_id');
                $collection = $this->getService('QuestQuestion')->fetchAllDependence('Variant', array('question_id IN (?)' => $questionIds));
                foreach ($collection as $question) {
                    $questions[$question->question_id]['text'] = strip_tags($question->question);
                    if (count($question->variants)) {
                        $questions[$question->question_id]['variants'] = $question->variants->asArrayOfArrays();
                        foreach ($question->variants as $variant) {
                            if (!isset($questions[$question->question_id]['variants_text'])) $questions[$question->question_id]['variants_text'] = '';
                            $questions[$question->question_id]['variants_text'] .= preg_replace($pattern, '', $variant->variant) . '<w:p w:rsidR="00220D1B" w:rsidRDefault="00220D1B"/>'; // перевод строки
                            if ($variant->is_correct) $questions[$question->question_id]['correct'] = strip_tags($variant->variant);
                        }
                    }
                }

                foreach ($questAttempt->questionResults as $questionResult) {
                    $row = array(
                        'question'  => preg_replace($pattern, '', $questions[$questionResult->question_id]['text']),
                        'answers'   => $questions[$questionResult->question_id]['variants_text'],
                        'chosen'    => preg_replace($pattern, '', $questions[$questionResult->question_id]['variants'][$questionResult->variant]['variant']),
                        'correct'   => preg_replace($pattern, '', $questions[$questionResult->question_id]['correct']),
                    );
                    $data['table_1'][] = $row;
                }
            }
//print_r($data); exit();
            //строим отчет!
            $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, HM_PrintForm::FORM_QUEST_PROTOCOL, $data, "Отчёт о тестировании {$userName}", $options, true, $filesMapping);

        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Результаты выполнения не найдены')));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
    }

    public function updateActions($questionType, $showFeedback, $actions)
    {
        if (HM_Quest_Question_QuestionModel::TYPE_FREE !== $questionType or !$showFeedback) {
            $this->unsetAction($actions, array('module' => 'quest', 'controller' => 'report', 'action' => 'hide-review'));
        }

        if(HM_Quest_Question_QuestionModel::TYPE_FREE !== $questionType or $showFeedback) {
            $this->unsetAction($actions, array('module' => 'quest', 'controller' => 'report', 'action' => 'show-review'));
        }
        return $actions;
    }

    public function showReviewAction()
    {
        $questionResultId = $this->_request->getParam('question_result_id');
        $questId = $this->_request->getParam('quest_id');
        $subjectId = $this->_request->getParam('subject_id');
        $feedbackId = $this->_request->getParam('feedback_id');

        $this->getService('QuestQuestionResult')->update([
            'question_result_id' => $questionResultId,
            'show_feedback' => 1,
        ]);

        $this->_flashMessenger->addMessage(_('Отзыв успешно добавлен'));
        $this->_redirector->gotoSimple('poll', 'report', 'quest', ['quest_id' => $questId, 'subject_id' => $subjectId, 'feedback_id' => $feedbackId]);
    }

    public function hideReviewAction()
    {
        $questionResultId = $this->_request->getParam('question_result_id');
        $questId = $this->_request->getParam('quest_id');
        $subjectId = $this->_request->getParam('subject_id');
        $feedbackId = $this->_request->getParam('feedback_id');

        $this->getService('QuestQuestionResult')->update([
            'question_result_id' => $questionResultId,
            'show_feedback' => 0,
        ]);

        $this->_flashMessenger->addMessage(_('Отзыв успешно скрыт'));
        $this->_redirector->gotoSimple('poll', 'report', 'quest', ['quest_id' => $questId, 'subject_id' => $subjectId, 'feedback_id' => $feedbackId]);
    }
}