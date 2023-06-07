<?php
class HM_At_Kpi_Cluster_ClusterService extends HM_Service_Abstract
{
    public function getClustersKpis()
    {
        // @todo: сортировать по 'self.name'; не работает в MSSQL
        $clusters = $this->fetchAllDependenceJoinInner('Kpi');
        return $clusters;
    }
}