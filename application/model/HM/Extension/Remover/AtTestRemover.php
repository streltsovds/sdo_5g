<?php
class HM_Extension_Remover_AtTestRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'methods' => [
                HM_At_Evaluation_EvaluationModel::TYPE_TEST,
            ],
            'columns' => [
                'test_count',
            ],
            'menu' => [
                'id' => [
                    'mca:criterion:test:index',
                ],
                'contextMenu' => [
                    'id' => [
                        'mca:profile:criterion:professional',
                    ],
                ],
            ],
        ]);
    }
}