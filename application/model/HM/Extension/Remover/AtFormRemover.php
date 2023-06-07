<?php
class HM_Extension_Remover_AtFormRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            [
                'methods' => [
                    HM_At_Evaluation_EvaluationModel::TYPE_FORM,
                ],
                'menu' => [
                    'id' => [
                        'mca:quest:list:form',
                    ],
                ],
            ]
        );
    }
}