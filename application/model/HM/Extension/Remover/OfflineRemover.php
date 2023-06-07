<?php
class HM_Extension_Remover_OfflineRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'actions' => [
                [
                    'module' => 'offline',
                    'controller' => 'list',
                    'action' => 'new',
                ],
                [
                    'module' => 'lesson',
                    'controller' => 'import',
                    'action' => 'csv',
                ],
            ],
        ]);
    }
}