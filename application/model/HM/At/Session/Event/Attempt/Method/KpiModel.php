<?php
/**
 * Для единообразия сделано на манер HM_At_Session_Event_Attempt_Method_CompetenceModel (PersistentModel),
 * хотя оценка KPI сейчас не заполняется не в режиме Quest'а
 *
 */
class HM_At_Session_Event_Attempt_Method_KpiModel extends HM_Model_Abstract
{
    protected $_model = array();
    protected $_results = array();    
    protected $_memoResults = array();  
        
    protected $_event;
    protected $_scale;
    protected $_kpis;
    protected $_criteria;
    protected $_memos;
    protected $_options;

    public function getModel()
    {
        // чтобы не тащить за собой много переменных во view
        return array(
            'event' => $this->_event,
            'evaluation' => $this->_evaluation,
            'scale' => $this->_scale,
            'kpis' => $this->_kpis,
            'criteria' => $this->_criteria,
            'memos' => $this->_memos,
            'options' => $this->_options,
            'attempt' => $this->getData(),
        );
    }

    public function setupModel($event)
    {
        $event = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->findDependence(array('Session', 'SessionUser', 'SessionEventUser', 'Evaluation', 'EvaluationResult', 'EvaluationMemoResult'), $this->session_event_id)->current();
        $session = $event->session->current();

        $this->_event = $event;
        $this->_evaluation = $evaluation = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->findManyToMany('CriterionKpi', 'EvaluationCriterion', $event->evaluation_id)->current();
        $this->_options = $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $session->getOptionsModifier());
        
//        $kpis = array();
//        if (count($userKpis = Zend_Registry::get('serviceContainer')->getService('AtKpiUser')->fetchAll(array('user_id = ?' => $this->_event->user_id, 'cycle_id = ?' => $session->cycle_id)))) {
//            $kpis = Zend_Registry::get('serviceContainer')->getService('AtKpi')->fetchAllDependence(array('KpiCluster', 'KpiUnit', 'UserKpi'), array('kpi_id IN (?)' => $userKpis->getList('kpi_id')), 'name');
//        }
//
//        $this->_kpis = array();
//        foreach ($kpis as $kpi) {
//            $cluster = ($options['kpiUseClusters'] && count($kpi->cluster) && ($cluster = $kpi->cluster->current())) ? $cluster->name : HM_At_Kpi_Cluster_ClusterModel::NONCLUSTERED;
//            if (!isset($this->_kpis[$cluster])) {
//                $this->_kpis[$cluster] = array();
//            }
//            $userKpi = $kpi->user_kpi->exists('user_id', $this->_event->user_id);
//            $this->_kpis[$cluster][] = $kpi->getValues() + $userKpi->getValues() + (count($kpi->unit) ? array('unit' => $kpi->unit->current()->name) : array());
//        }

        $this->_kpis = Zend_Registry::get('serviceContainer')->getService('AtKpiUser')->getUserKpis($this->_event->user_id, $session->cycle_id, $this->_evaluation->relation_type);
        
        // @todo: отсортировать по 'ScaleValue.value'; в MSSQL не работает
        // $scaleId = $evaluation->scale_id; //очень ненадёжный вариант кэшировать scale_id в evaluation
        $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('kpiScaleId', $session->getOptionsModifier());
        $this->_scale = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', Zend_Registry::get('serviceContainer')->getService('Scale')->quoteInto('self.scale_id = ?', $scaleId))->current();
        $this->_memos = Zend_Registry::get('serviceContainer')->getService('AtEvaluationMemo')->fetchAll(array('evaluation_type_id = ?' => $event->evaluation_id));        
        $this->_criteria = $evaluation->criteriaKpi;

        $this->_results = (count($this->_event->evaluationResults)) ? $this->_event->evaluationResults->getList('criterion_id', 'value_id') : array();

        $memoResults = (count($this->_event->evaluationMemoResults)) ? $this->_event->evaluationMemoResults->getList('evaluation_memo_id', 'value') : array();
        foreach ($memoResults as $evaluationMemoId => $value) {
            $this->_memoResults[$evaluationMemoId] = $value;
        }

        return $this;
    }

    public function getResults()
    {
        return $this->_results;
    }
    
    public function getMemoResults()
    {
        return $this->_memoResults;
    }    
}