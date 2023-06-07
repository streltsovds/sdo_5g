<?php
class HM_Extension_Remover_AtPsychoRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            [
                'methods' => [
                    HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO,
                ],
                'menu' => [
                    'id' => [
                        'mca:criterion:personal:index',
                        'mca:quest:list:psycho',
                    ],
                    'contextMenu' => [
                        'id' => [
                            'mca:profile:criterion:personal',
                        ],
                    ],
                ],
            ]
        );
    }
}