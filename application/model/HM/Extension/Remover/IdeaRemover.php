<?php
class HM_Extension_Remover_IdeaRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'menu' => [
                'module' => [
                    'idea',
                ],
            ],
        ]);
    }
}