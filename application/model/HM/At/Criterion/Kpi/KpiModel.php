<?php
class HM_At_Criterion_Kpi_KpiModel extends HM_Model_Abstract
{
    const TYPE_KPI = 6; // на всякий случай не пересекается с HM_At_Criterion_CriterionModel

    static public function getKpiTypes()
    {
        return array(
            self::TYPE_KPI => _('Способ достижения KPI'),
        );
    }        
}