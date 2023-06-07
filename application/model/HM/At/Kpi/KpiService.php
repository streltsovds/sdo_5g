<?php
class HM_At_Kpi_KpiService extends HM_Service_Abstract
{
    public function getTypicalKpis()
    {
        return $this->fetchAll(array('is_typical = ?' => HM_At_Kpi_KpiModel::TYPICAL));
    }

    // в базовой версии критерии оценки KPI не влияют 
    // ранг результативности и матрицу успешности
    static public function mapKpiRatio($arr)
    {
        return 1;
    }
}