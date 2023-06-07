<?php
class HM_At_Criterion_Cluster_ClusterService extends HM_Service_Abstract
{
    public function getClustersCriteria($categoryId = false)
    {
        $where = array();
        if ($categoryId) {
            $where = $this->quoteInto('Criterion.category_id = ?', $categoryId);
        }
        // @todo: сортировать по 'self.name'; не работает в MSSQL
        $clusters = $this->fetchAllDependenceJoinInner('Criterion', $where);
        return $clusters;
    }
}