<?php
class HM_Extension_Remover_AtCompetencePairRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            [
                'methods' => [
                    HM_At_Evaluation_EvaluationModel::TYPE_RATING,
                ],
                'elements' => [
                    'ratingComment',
                    'ratingReportComment',
                ],
            ]
        );
    }
}