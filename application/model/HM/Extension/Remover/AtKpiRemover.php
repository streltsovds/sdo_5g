<?php
class HM_Extension_Remover_AtKpiRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            [
                'methods' => [
                    HM_At_Evaluation_EvaluationModel::TYPE_KPI,
                ],
                'columns' => [
                    'kpis',
                ],
                'elements' => [
                    'kpiUseCriteria',
                    'kpiScaleId',
                    'kpiUseClusters',
                    'kpiComment',
                    'kpiReportComment',
                ],
                'infoblocks' => [
                    'kpiBlock',
                ],
                'menu' => [
                    'module' => [
                        'kpi',
                    ],
                    'id' => [
                        'mca:criterion:kpi:index',
                    ],
                ],
            ]
        );
    }
}