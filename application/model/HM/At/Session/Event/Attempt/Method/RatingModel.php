<?php
/**
 * PersistentModel - набор данных, хранимый в сессии на протяжении всего Quest'а
 * Если анкета 360 заполняется не в режиме Quest'а - используется эта же модель, но только один раз
 *
 */
class HM_At_Session_Event_Attempt_Method_RatingModel extends HM_Multipage_PersistentModel_Abstract implements HM_Multipage_PersistentModel_Interface
{
    protected $_index;
    protected $_event;
    protected $_clusters;
    protected $_criteria;
    protected $_pairs;
    protected $_users;
    protected $_options;

    public function getModel()
    {
        // чтобы не тащить за собой много переменных во view
        return array(
            'index' => $this->_index,
            'event' => $this->_event,
            'clusters' => $this->_clusters,
            'criteria' => $this->_criteria,
            'users' => $this->_users,
            'pairs' => $this->_pairs,
            'options' => $this->_options,
            'attempt' => $this->getData(),
        );
    }

    public function setupModel($event = false)
    {
        $this->_options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS);
        if (!$event) {
            $event = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->fetchAllDependence(array('Evaluation', 'SessionPair', 'SessionPairResult'), Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->quoteInto('session_event_id = ?', $this->session_event_id))->current();
        }
        $this->_event = $event;
        
        if (count($event->pairs)) {
            $mids = array_unique(array_merge($event->pairs->getList('first_user_id'), $event->pairs->getList('second_user_id')));
            $users = Zend_Registry::get('serviceContainer')->getService('User')->fetchAllDependence('Position', array('MID IN (?)' => $mids), array('LastName', 'FirstName'));
            $this->_users = $users->asArrayOfObjects();
            $this->_pairs = $event->pairs->asArrayOfObjects();
        }

        $evaluation = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->findManyToMany('Criterion', 'EvaluationCriterion', $event->evaluation_id)->current();
        if (count($evaluation->criteria)) {
            $criteria = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->getClustersCriteriaIndicators($evaluation->criteria->getList('criterion_id'));

            $criteriaClusterCache = array();
            foreach ($criteria as $criterion) {

                // @todo: а что если кластеры не включены..?
                if (count($criterion->cluster) && Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceUseClusters')) {
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

                $this->_index[$clusterId][] = $criterion->criterion_id;
                $this->_criteria[$criterion->criterion_id] = $criterion;
                $criteriaClusterCache[$criterion->criterion_id] = $clusterId;
            }
        }
        
        $pairs = array();
        if (count($event->pairResults)) {
        	foreach ($event->pairResults as $result) {
        		$clusterId = $criteriaClusterCache[$result->criterion_id];
        		$this->_results[$clusterId][$result->criterion_id][$result->session_pair_id] = $result->user_id;
        	}
        }
        
        return $this;
    }   
}