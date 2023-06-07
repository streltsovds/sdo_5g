<?php
class Session_ReportMethodsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Report;

    protected $_sessionUser;
    
    protected $_criteriaCache = array();
    protected $_criteriaTestCache = array();
    protected $_criteriaPersonalCache = array();
    protected $_indicatorsCache = array();
    protected $_questsCache = array();
    
    protected $_evaluation;
    protected $_profile;
    protected $_position;
    protected $_cycle;

    public function init()
    {
        parent::init();
        $this->_sessionUser = $this->_getParam('sessionUser');
        $this->_profile = $this->_getParam('profile');
        $this->_evaluation = $this->_getParam('evaluation');
        $this->_session = $this->view->session = $this->_sessionUser->session->current();

        $this->view->comment = $this->_getParam('comment');
        $this->view->name = $this->_getParam('name');
    }
    
    public function competenceAction()
    {
        $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $this->_session->getOptionsModifier());        
        
        $relationTypes = $this->_getParam('relationTypes', array());
        $criteria = $this->getService('AtCriterion')->fetchAllDependence('CriterionIndicator', null, array('cluster_id', 'name'));
        $this->_criteriaCache = $criteria->getList('criterion_id', 'name');
        foreach ($criteria as $criterion) {
            if (count($criterion->indicators)) {
                $attr = $options['competenceUseIndicatorsDescriptions'] ? 'description_positive' : 'name';
                $this->_indicatorsCache[$criterion->criterion_id] = $criterion->indicators->getList('indicator_id', $attr);
            }
        }
        
        $results = $this->getService('AtEvaluation')->profileResultsByRelationType($this->_sessionUser, $options);
        $results = $results['results'];

        $data = $graphs = array();
        $colors = HM_At_Evaluation_Method_CompetenceModel::getRelationTypeColors();
        $titles = HM_At_Evaluation_Method_CompetenceModel::getRelationTypesShort();
        foreach ($relationTypes as $relationType) {
            $graphs[$relationType] = array(
                'legend' => ucfirst($titles[$relationType]),
                'color' => $colors[$relationType],
            );
        }

        if (count($results)) {
            foreach ($results as $criterionId => $result) {
                
                $criterionData = array(
                    'title' => $this->_criteriaCache[$criterionId],
                );
                foreach ($relationTypes as $relationType) {
                    $criterionData[$relationType] = $result['criterion'][$relationType];
                }
                $data[] = $criterionData;
            }

            if (in_array(HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN, $relationTypes) && $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN] === null) {
                $graphs[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN]['legend'] .= '<sup>*</sup>';
                $this->view->footnotes['competences'] = _('Здесь и далее: результаты подчиненных включены в результаты коллег');
            }
        }
        
        $this->view->charts['competences'] = array(
            'graphs' => $graphs,
            'data' => $data,
        );
        
        $this->view->competenceCriteria = $this->_criteriaCache;
        
        foreach ($this->_criteriaCache as $criterionId => $criterionName) {

            $data = $table = array();
            if (!isset($results[$criterionId])) continue;

            $chartId = 'competence_criterion_' . $criterionId;

            $table = array(
                'head' => array(
                    'title' => _('Индикатор'),
            ));
            foreach ($relationTypes as $relationType) {
                $table['head'][$relationType] = ucfirst($titles[$relationType]);
            }

            if ($options['competenceUseIndicators']) {
                foreach ($results[$criterionId]['indicators'] as $indicatorId => $result) {
                    $row = array(
                        'title' => $this->_indicatorsCache[$criterionId][$indicatorId],
                    );
                    foreach ($relationTypes as $relationType) {
                        $row[$relationType] = $result[$relationType];
                    }
                    $table[] = $row;                   
                }
            }
            $row = array(
                'title' => array('class' => 'total', 'value' => $this->_criteriaCache[$criterionId]),
            );
            foreach ($relationTypes as $relationType) {
                $row[$relationType] = array('class' => 'total', 'value' => $results[$criterionId]['criterion'][$relationType]);
                $data[] = array(
                    'value' => $results[$criterionId]['criterion'][$relationType],
                    'title' => $titles[$relationType],
                    'color' => $colors[$relationType]
                );
            }            
            $table[] = $row;
            $this->view->tables[$chartId]= $table;
            
            $this->view->charts[$chartId] = $data;
        }

        $top = $bottom = $topHidden = $bottomHidden = array();
        if (count($this->_profile->criteriaValues)) {
            $this->_profileCriterionValues = $this->_profile->criteriaValues->getList('criterion_id', 'value_id');
        }

        // здесь именно шкала оценки компетенций
        //$scaleId = $options['competenceScaleId'];
        $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId');
        foreach ($results as $criterionId => $result) {

            $plan = HM_Scale_Converter::getInstance()->id2value($this->_profileCriterionValues[$criterionId], $scaleId);
            if (($plan != HM_Scale_Value_ValueModel::VALUE_NA) && $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] > $plan) {
                $top[$this->_criteriaCache[$criterionId]] = sprintf('%s/%s', $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS], $plan);
            }
            
            $plan = $plan = HM_Scale_Converter::getInstance()->id2value($this->_profileCriterionValues[$criterionId], $scaleId);
            if (($plan != HM_Scale_Value_ValueModel::VALUE_NA) && $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] < $plan) {
                $bottom[$this->_criteriaCache[$criterionId]] = sprintf('%s/%s', $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS], $plan);
            }
            if (($delta = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] - $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF]) >= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_HIDDEN_DELTA) {
                $topHidden[$this->_criteriaCache[$criterionId]] = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL];
            }
            if (($delta = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF] - $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS]) >= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_HIDDEN_DELTA) {
                $bottomHidden[$this->_criteriaCache[$criterionId]] = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL];
            }
        }

        if (!count($top)) $top[_('нет')] = '';
        if (!count($bottom)) $bottom[_('нет')] = '';
        if (!count($topHidden)) $topHidden[_('нет')] = '';
        if (!count($bottomHidden)) $bottomHidden[_('нет')] = '';

        $this->view->lists['competence_top'] = $top;
        $this->view->lists['competence_bottom'] = $bottom;
        if ($this->_session->getType() == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
            $this->view->lists['competence_top_hidden'] = $topHidden;
            $this->view->lists['competence_bottom_hidden'] = $bottomHidden;
        }
    
        // сравнение с профилем успешности
        $this->view->analyticsChartData = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getAnalyticsChartDataJs($this->_sessionUser->session_user_id, array(
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => 1,
        ));        
    }
    
    public function kpiAction()
    {
        if ($this->_session->cycle_id) {

            $events = $this->getService('AtSessionEvent')->fetchAllDependence('Evaluation', array(
                'session_id = ?' => $this->_session->session_id,
                'session_user_id = ?' => $this->_sessionUser->session_user_id,
                'method = ?' => HM_At_Evaluation_EvaluationModel::TYPE_KPI
            ));

            $relationTypes = array();
            if (count($events)) {
                foreach ($events as $event) {

                    if (count($event->evaluation)) {
                        $evaluationType = $event->evaluation->current();
                        $relationTypes[$evaluationType->relation_type] = HM_At_Evaluation_EvaluationModel::getRelationTypeTitle($evaluationType->relation_type, true);
                    }

                    $kpiCriteriaResults = $this->getService('AtEvaluationResults')->fetchAllDependence(array('ScaleValue', 'CriterionKpi'), array('session_event_id = ?' => $event->session_event_id));

                    if (count($kpiCriteriaResults)) {

                        if (!isset($this->view->tables['kpiCriteria'])) {
                            $this->view->tables['kpiCriteria'] = array();
                            $this->view->tables['kpiCriteria']['head'] = array(
                                'title' => _('Способ достижения'),
                                'value' => _('Оценка руководителя'),
                            );
                        }

                        foreach ($kpiCriteriaResults as $result) {
                            if (!count($result->criterionKpi)) continue; // impossible
                            $criterionKpi = $result->criterionKpi->current();
                            $scaleValue = $result->scale_value->current();
                            $this->view->tables['kpiCriteria'][] = array(
                                'criterion' => $criterionKpi->name,
                                'value' => $scaleValue->text,
                            );
                        }
                        // break; // если раскомментировать - первая попавшаяся заполненная анкета
                    }
                }

                $kpisByRelationType = $criteriaValues = array();
                $valueTypes = HM_At_Kpi_User_UserModel::getQualitiveValues();
                $relationTypes = array_unique($relationTypes);
                foreach ($relationTypes as $relationType => $relationTypeTitle) {
                    $kpisByRelationType[$relationType] = $this->getService('AtKpiUser')->getUserKpis($this->_sessionUser->user_id, $this->_session->cycle_id, $relationType);
                }

                $kpis = current($kpisByRelationType);
                if (count($kpis)) {

                    $this->view->tables['kpis']['head'] = array(
                        'i' => '#',
                        'title' => _('Задача'),
                        'value_plan' => _('Плановое значение'),
                    );

                    foreach ($relationTypes as $relationType => $relationTypeTitle) {
                        $this->view->tables['kpis']['head'][] = sprintf(_('Результат (%s)'), $relationTypeTitle);
                        $this->view->tables['kpis']['head'][] = sprintf(_('Комментарий (%s)'), $relationTypeTitle);
                    }

                    foreach ($kpis as $clusterId => $userKpis) {
                        // @todo: названия кластеров
                        foreach ($userKpis as $userKpi) {
                            $row = array(
                                ++$i,
                                $userKpi['name'],
                                sprintf('%s %s', $userKpi['value_plan'], $userKpi['unit']),
                            );

                            foreach ($relationTypes as $relationType => $relationTypeTitle) {

                                $result = $kpisByRelationType[$relationType][$clusterId][$userKpi['kpi_id']];
                                if($userKpi['value_type'] == HM_At_Kpi_User_UserModel::TYPE_QUANTITATIVE){
                                    $valueFact = $result['value_fact'];
                                } else {
                                    $valueFact = $valueTypes[$result['value_fact']];
                                }

                                $row[] = sprintf('%s %s', $valueFact, $userKpi['unit']);
                                $row[] = $result['comments'];
                            }

                            $this->view->tables['kpis'][] = $row;
                        }
                    }
                }


                $this->view->kpiTotal = ceil($this->_sessionUser->total_kpi) . '%';
                $this->view->programm_type = $this->_session->programm_type;
            }
        }
    }
    
    public function testAction()
    {
        if (count($collection = $criteriaTest = $this->getService('AtCriterionTest')->fetchAllDependence('Quest', null, array('name')))) {
            foreach ($collection as $item) {
                $this->_criteriaTestCache[$item->criterion_id] = $item->name;
                $this->_questsCache[$item->criterion_id] = count($item->quest) ? $item->quest->current()->name : '';
            }
        }
        
        if (count($this->_profile->criteriaValues)) {
            
            $results = array();
            $collection = $this->getService('AtSessionUserCriterionValue')->fetchAll(array(
                'session_user_id = ?' => $this->_sessionUser->session_user_id,
                'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
            ));
            if (count($collection)) $results = $collection->getList('criterion_id', 'value');
            
            $colors = HM_At_Evaluation_EvaluationModel::getPlanFactColors();
            $graphs = array(
                HM_At_Evaluation_EvaluationModel::CRITERION_VALUE_PROFILE => array('legend' => _('План'), 'color' => $colors[HM_At_Evaluation_EvaluationModel::CRITERION_VALUE_PROFILE]),
                HM_At_Evaluation_EvaluationModel::CRITERION_VALUE_SESSION_USER => array('legend' => _('Факт'), 'color' => $colors[HM_At_Evaluation_EvaluationModel::CRITERION_VALUE_SESSION_USER]),
            );
            
            $data = array();
            foreach ($this->_profile->criteriaValues as $criterionValue) {
                if ($criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL) continue;
                if (!isset($this->_criteriaTestCache[$criterionValue->criterion_id])) continue;
                $data[] = array(
                    'title' => sprintf("%s\r\n(%s)", $this->_criteriaTestCache[$criterionValue->criterion_id], $this->_questsCache[$criterionValue->criterion_id]),
                    HM_At_Evaluation_EvaluationModel::CRITERION_VALUE_PROFILE => $criterionValue->value,
                    HM_At_Evaluation_EvaluationModel::CRITERION_VALUE_SESSION_USER => $results[$criterionValue->criterion_id],
                );
            }
            
            $this->view->charts['criteria_test'] = array(
                'data' => $data, 
                'graphs' => $graphs
            );
        }
    }
    
    public function psychoAction()
    {
        if (count($collection = $this->getService('AtCriterionPersonal')->fetchAll())) {
            $criteriaCache = $collection->getList('criterion_id', 'name');
        }

        /** @var HM_At_Session_Event_EventService $eventService */
        $eventService = $this->getService('AtSessionEvent');

        /** @var HM_Quest_Category_Result_ResultService $categoryResultService */
        $categoryResultService = $this->getService('QuestCategoryResult');

        if (count($events = $eventService->fetchAllDependenceJoinInner('QuestAttempt',
            $eventService->quoteInto([
                'self.method = ? AND ',
                'self.session_user_id = ? AND ',
                'QuestAttempt.is_resultative = ?',
            ], [
                HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO,
                $this->_sessionUser->session_user_id,
                1
            ])))) {
            
            $criteriaPersonal = [];
            foreach ($events as $event) {
                $criteriaPersonal[$event->criterion_id] = $criteriaCache[$event->criterion_id];
                if (count($event->questAttempts)) {
                    $questAttempt = $event->questAttempts->current();

                    // Вывод по категориям
                    if (count($categoryResults = $categoryResultService->fetchAllDependence('Category', ['attempt_id = ?' => $questAttempt->attempt_id]))) {
                        $this->view->lists['categories'][$event->criterion_id] = [];
                        foreach ($categoryResults as $categoryResult) {
                            if (count($categoryResult->category)) {
                                $category = $categoryResult->category->current();
                                $this->view->lists['categories'][$event->criterion_id][$category->name] = sprintf('%s (%s)', $categoryResult->result, $categoryResult->score_raw);
                            }
                        }
                    }

                    // Вывод только по категориям
                    if (count($categoryResults = $categoryResultService->fetchAllDependence('Category', ['attempt_id = ?' => $questAttempt->attempt_id]))) {
                        $this->view->lists['categories'][$event->criterion_id] = [];
                        foreach ($categoryResults as $categoryResult) {
                            if (count($categoryResult->category)) {
                                $category = $categoryResult->category->current();
                                $this->view->lists['categories'][$event->criterion_id][$category->name] = sprintf('%s (%s)', $categoryResult->result, $categoryResult->score_raw);
                            }
                        }

                        // Оставлю это только для Кеттела и прочих перерасчётов, потому что раньше не сортировали и вопросов не было
                        if (count($conversionResults))
                            ksort($this->view->lists['categories'][$event->criterion_id]);
                    }

                }
            }

            $this->view->criteriaPersonal = $criteriaPersonal;
        }
    }
    
    public function formAction()
    {
        if (count($collection = $this->getService('AtSessionEvent')->fetchAll(array(
            'evaluation_id = ?' => $this->_evaluation->evaluation_type_id,
            'session_user_id = ?' => $this->_sessionUser->session_user_id,
        )))) {
            $eventIds = $collection->getList('session_event_id');
            $questAttempts = $this->getService('QuestAttempt')->fetchAllDependence(array('Quest', 'QuestionResult'), array(
                'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT,
                'context_event_id IN (?)' => $eventIds,
                'is_resultative = ?' => 1,
            ));
            
            $quests = $clusters = $questQuestions = $questionsByClusters = array();
            foreach ($questAttempts as $questAttempt) {
                if (count($questAttempt->quest)) {
                    $quests[] = $questAttempt->quest->current();
                }
                if (count($questAttempt->questionResults)) {
                
                    $questionIds = $questAttempt->questionResults->getList('question_id');
                    $questions = $this->getService('QuestQuestion')->fetchAllDependence(array('Variant', 'QuestionQuest'), array('question_id IN (?)' => $questionIds));
                    $questions = $questions->asArrayOfObjects();
                    $questionsByClusters[$questAttempt->quest_id] = array();
                    
                    foreach ($questAttempt->questionResults as $questionResult) {
                        
                        $variants = array();
                        $question = $questions[$questionResult->question_id];

                        if ($question) {
                            $result = $question->displayUserResult($questionResult);
                            $this->view->lists['question-' . $questionResult->question_id] = array(
                                $question->question => $result,
                            );
                        }
                    }
                    $questQuestions[$questAttempt->quest_id] = $questions;
                    
                    // надо вывести по кластерам..
                    if (count($questionIds)) {
                        if (count($collection = $this->getService('QuestQuestionQuest')->fetchAllDependence('Cluster', array(
                                'question_id IN (?)' => $questionIds,
                                'quest_id = ?' => $questAttempt->quest_id,
                        )))) {
                            foreach ($collection as $questQuestionQuest) {
                                if (count($questQuestionQuest->cluster)) {
                                    $cluster = $questQuestionQuest->cluster->current()->name;
                                } else {
                                    $cluster = HM_Quest_Cluster_ClusterModel::NONCLUSTERED;
                                }
                                if (!isset($questionsByClusters[$cluster])) $questionsByClusters[$cluster] = array();
                                $questionsByClusters[$questAttempt->quest_id][$cluster][] = $questions[$questQuestionQuest->question_id];
                            }
                        }
                    }                    
                }
            }
            $this->view->quests = $quests;
            $this->view->questions = $questQuestions;
            $this->view->questionsByClusters = $questionsByClusters;
        }
    }
    
    public function ratingAction()
    {
        
    }
}