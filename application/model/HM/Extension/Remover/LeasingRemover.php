<?php
class HM_Extension_Remover_LeasingRemover extends HM_Extension_Remover_Abstract
{
    public function init(){
        $this->setItemsToHide([
            'infoblocks' => [
                'leasingBlock',
            ]
        ]);
    }
}