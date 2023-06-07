<?php
class HM_Extension_Remover_AtCompetenceRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            [
                'methods' => [
                    HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE,
                ],
                'columns' => [
                    'kpis',
                    'indicators',
                ],
                'elements' => [
                    'competenceUseIndicators',
                    'competenceUseScaleValues',
                    'competenceUseIndicatorsDescriptions',
                    'competenceUseIndicatorsReversive',
                    'competenceUseIndicatorsScaleValues',
                    'competenceScaleId',
                    'competenceUseClusters',
                    'competenceRandom360',// 'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
                    'competenceRandom270',// 'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
                    'competenceComment',
                    'competenceReportComment',
                ],
                'menu' => [
                    'id' => [
                        'mca:criterion:corporate:index',
                    ],
                    'contextMenu' => [
                        'id' => [
                            'mca:profile:criterion:corporate',
                        ],
                    ],
                ],
            ]
        );
    }
}