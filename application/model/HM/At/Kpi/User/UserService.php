<?php
class HM_At_Kpi_User_UserService extends HM_Service_Abstract
{
    public function getUserKpis($userId, $cycleId = null, $relationType = HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF)
    {
        $return = $cycleIds = $userKpis = $units = array();
        if (!$cycleId) {
            $cycles = $this->getService('Cycle')->getCurrent(true);
            if (!$cycles) {
                throw new HM_Exception(_('Не определён текущий оценочный период'));
            } elseif (is_a($cycles, 'HM_Collection')) {
                $cycleIds = $cycles->getList('cycle_id');
            } else {
                $cycleIds = array($cycles->cycle_id);
            }
        } else {
            $cycleIds = array($cycleId);
        }
        
        if ($useClusters = $this->getService('Option')->getOption('kpiUseClusters')) {
            $clusters = $this->getService('AtKpiCluster')->fetchAll()->getList('kpi_cluster_id', 'name');
        }                
        
        $units = $this->getService('AtKpiUnit')->fetchAll()->getList('kpi_unit_id', 'name');

        if ($userId && count($cycleIds)) {
            $userKpis = $this->fetchAllDependence(array('Kpi', 'Result'), array(
                'user_id = ?' => $userId,
                'cycle_id IN (?)' => $cycleIds,
            ));
        }

        foreach ($userKpis as $userKpi) {

            $cluster = HM_At_Kpi_Cluster_ClusterModel::NONCLUSTERED;
            if (count($userKpi->kpi)) {
                $kpi = $userKpi->kpi->current();
                if ($useClusters && $kpi->kpi_cluster_id && isset($clusters[$kpi->kpi_cluster_id])) {
                    $cluster = $clusters[$kpi->kpi_cluster_id];
                }
                if (!isset($return[$cluster])) {
                    $return[$cluster] = array();
                }

                $resultData = array();
                if (count($userKpi->results)) {
                    foreach($userKpi->results as $result) {
                        if ($result->relation_type == $relationType) {
                            $resultData['value_fact'] = $result->value_fact;
                            $resultData['comments'] = $result->comments;
                        }
                    }
                }
                
                $data = array_merge($userKpi->getData(), $kpi->getData(), $resultData, array('unit' => $units[$kpi->kpi_unit_id]));
                $return[$cluster][$kpi->kpi_id] = $data;
            }
        }
        ksort($return);
        foreach ($return as &$cluster) {
            uasort($cluster, array('HM_At_Kpi_User_UserService', '_sortByName'));
        }
        return $return;
    }
    
    public function getUserProgress($userId, $cycleId = null)
    {
        $achieved = 0;
        $cycleIds = array();
        if (!$cycleId) {
            if ($collection = $this->getService('Cycle')->getCurrent(true)) {
                $cycleIds = $collection->getList('cycle_id');
            }
        } else {
            $cycleIds = array($cycleId);
        }
        if (count($cycleIds)) {
            $userKpis = $this->fetchAll(array(
                'user_id = ?' => $userId,
                'cycle_id IN (?)' => $cycleIds,
            ));
            foreach ($userKpis as $userKpi) {
                if (
                    (!empty($userKpi->value_plan) || (empty($userKpi->value_plan) && $userKpi->value_type == 2)) && 
                    !empty($userKpi->value_fact) &&
                    ($userKpi->value_fact != -1) &&
                    $userKpi->value_plan <= $userKpi->value_fact) {
                    $achieved++;
                }
            }
            return count($userKpis) ? round(100 * $achieved / count($userKpis)) : 0;
        }
        return 0;
    }

    // DEPRECATED!
    // используйте HM_At_Kpi_User_Result_ResultService::setResults
    public function setFact($userKpiId, $valueFact)
    {
        $this->updateWhere(array(
            'value_fact' => $valueFact,        
        ), array(
            'user_kpi_id = ?' => $userKpiId,        
        ));
    }

    public function _sortByName($kpi1, $kpi2)
    {
        return ($kpi1['name'] < $kpi2['name']) ? -1 : 1;
    }
}