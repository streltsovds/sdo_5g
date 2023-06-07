<?php
/**
 * PersistentModel - набор данных, хранимый в сессии на протяжении всего Quest'а
 * Если анкета 360 заполняется не в режиме Quest'а - используется эта же модель, но только один раз
 *
 */
class HM_At_Session_Event_Attempt_Method_CompetenceModel extends HM_Multipage_PersistentModel_Abstract implements HM_Multipage_PersistentModel_Interface
{
    protected $_index;
    protected $_event;
    protected $_evaluationType;
    protected $_scale;
    protected $_scaleValues;
    protected $_clusters;
    protected $_criteria;
    protected $_indicators;
    protected $_memos;
    protected $_options;

    public function getModel()
    {
        // чтобы не тащить за собой много переменных во view
        return array(
            'index' => $this->_index,
            'event' => $this->_event,
            'evaluationType' => $this->_evaluationType,
            'scale' => $this->_scale,
            'scaleValues' => $this->_scaleValues,
            'clusters' => $this->_clusters,
            'criteria' => $this->_criteria,
            'indicators' => $this->_indicators,
            'memos' => $this->_memos,
            'options' => $this->_options,
            'attempt' => $this->getData(),

            // это влияет на NavPanel - кнопки перехода под оценчной формой
            // форма оценки по компетенциям работает в режиме "одной попытки"
            // т.е. при повторном входе результаты восстанавливаются и заполнение продолжается с того же места
            // единственное, что мне здесь не нравится - слово Quest в названии режима.
            'mode' => HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_SINGLE,
        );
    }

    public function setupModel($event = false)
    {
        if (!$event) {
            $event = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->findDependence(array('Session', 'SessionUser', 'SessionEventUser', 'Evaluation', 'EvaluationResult', 'EvaluationIndicator', 'EvaluationMemoResult'), $this->session_event_id)->current();
        }
        $this->_event = $event;
        $session = count($event->session) ? $event->session->current() : false; 
        $this->_options = $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $session->getOptionsModifier());

        $this->_evaluationType = $evaluation = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->findManyToMany('Criterion', 'EvaluationCriterion', $event->evaluation_id)->current();
//         $criterionResults = Zend_Registry::get('serviceContainer')->getService('AtEvaluationResults')->fetchAll(array('session_event_id = ?' => $this->session_event_id))->getList('criterion_id', 'value_id');
//         $indicatorResults = Zend_Registry::get('serviceContainer')->getService('AtEvaluationIndicator')->fetchAll(array('session_event_id = ?' => $this->session_event_id))->getList('indicator_id', 'value_id');
        $criterionResults = (count($this->_event->evaluationResults)) ? $this->_event->evaluationResults->getList('criterion_id', 'value_id') : array();
        $indicatorResults = (count($this->_event->evaluationIndicators)) ? $this->_event->evaluationIndicators->getList('indicator_id', 'value_id') : array();
        $memoResults = (count($this->_event->evaluationMemoResults)) ? $this->_event->evaluationMemoResults->getList('evaluation_memo_id', 'value') : array();

        // @todo: отсортировать по 'ScaleValue.value'; в MSSQL не работает
        // $scaleId = $evaluation->scale_id; //очень ненадёжный вариант кэшировать scale_id в evaluation
        //$scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId');
        $scaleId = $options['competenceScaleId'];
        $this->_scale = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', Zend_Registry::get('serviceContainer')->getService('Scale')->quoteInto('self.scale_id = ?', $scaleId))->current();
        
        $this->_scaleValues = ($this->_scale && count($this->_scale->scaleValues)) ? $this->_scale->scaleValues->asArrayOfObjects() : array();
        usort($this->_scaleValues, array('HM_At_Session_Event_Attempt_Method_CompetenceModel', '_sortByScaleValue'));
        
        $this->_memos = Zend_Registry::get('serviceContainer')->getService('AtEvaluationMemo')->fetchAll(array('evaluation_type_id = ?' => $event->evaluation_id));

        if (count($evaluation->criteria)) {
            $criteria = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->getClustersCriteriaIndicators($evaluation->criteria->getList('criterion_id'));

            foreach ($memoResults as $evaluationMemoId => $value) {
                $this->_memoResults[$evaluationMemoId] = $value;
            }
            foreach ($criteria as $criterion) {

                if (count($criterion->cluster) && $options['competenceUseClusters']) {
                    $cluster = $criterion->cluster->current();
                    $clusterId = $cluster->cluster_id;
                } else {
                    $clusterId = HM_At_Criterion_Cluster_ClusterModel::NONCLUSTERED;
                }

                if (!isset($this->_index[$clusterId])) {
                    if ($cluster) {
                        $this->_clusters[$clusterId] = $cluster;
                    }
                    $this->_index[$clusterId] = array();
                    $this->_items[] = $clusterId;
                }

                $this->_criteria[$criterion->criterion_id] = $criterion;
                if (!isset($this->_index[$clusterId][$criterion->criterion_id])) {
                    $this->_index[$clusterId][$criterion->criterion_id] = array();
                }

                if ($this->_options['competenceUseScaleValues'] && count($criterion->scaleValues)) {
                    $criterion->scaleValues = $criterion->scaleValues->getList('value_id', 'description');
                }

                if (!$this->_options['competenceUseIndicators']) {
                    if (isset($criterionResults[$criterion->criterion_id])) {
                        $this->_results[$clusterId][$criterion->criterion_id] = $criterionResults[$criterion->criterion_id];
                    }
                }

                if (count($criterion->indicators) && $this->_options['competenceUseIndicators']) { 
                    foreach ($criterion->indicators as $indicator) {

                        $indicatorScaleValues = Zend_Registry::get('serviceContainer')->getService('AtCriterionIndicatorScaleValue')->fetchAll(array(
                            'indicator_id = ?' => $indicator->indicator_id
                        ));

                        $indicator->scaleValuesQuestionnaire = $indicatorScaleValues->getList('value_id', 'description_questionnaire');
                        $indicator->scaleValues = $indicatorScaleValues->getList('value_id', 'description');
                        $this->_indicators[$indicator->indicator_id] = $indicator;
                        $this->_index[$clusterId][$criterion->criterion_id][] = $indicator->indicator_id;

//                         autodetect отключен, используем options
//                         if (!empty($indicator->description_positive) && !empty($indicator->description_negative)) {
//                             $this->_mode['indicators_description'] = self::MODE_INDICATORS_DESCRIPTION_ON;
//                         }
//                         if ($indicator->reverse) {
//                             // если есть хотя бы один реверсивный индикатор - не показываем значения input'ов и ячейки с названием положительное/отрицательное проявление
//                             $this->_mode['indicators_reverse'] = self::MODE_INDICATORS_REVERSE_ON;
//                         }

                        if ($this->_options['competenceUseIndicators']) {
                            if (isset($indicatorResults[$indicator->indicator_id])) {
                                $this->_results[$clusterId][$indicator->indicator_id] = $indicatorResults[$indicator->indicator_id];
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }
    
    static public function _sortByScaleValue($scaleValue1, $scaleValue2) 
    {
        if ($scaleValue1->value == HM_Scale_Value_ValueModel::VALUE_NA) {
            return 1;
        } elseif ($scaleValue2->value == HM_Scale_Value_ValueModel::VALUE_NA) {
            return -1;
        } else {
            return ($scaleValue1->value < $scaleValue2->value) ? -1 : 1;
        }   
    }
}