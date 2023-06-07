<?php
class HM_Extension_Remover_MobileRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'domains' => [
                'mobile',
            ],
            'menu' => [
                'application' => 'mobile'
            ],
            'columns' => [
                'push_token'
            ]
        ]);
    }
}