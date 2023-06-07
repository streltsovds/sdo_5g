<?php
class HM_Extension_Remover_LessonsPlanActionsRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'actions' => [
                [
                    'module' => 'subject',
                    'controller' => 'lesson',
                    'action' => 'create',
                    'hide' => [
                        [
                            'module' => 'subject',
                            'controller' => 'materials',
                            'action' => 'index',
                        ]
                    ]
                ],
                [
                    'module' => 'subject',
                    'controller' => 'lessons',
                    'action' => 'import',
                    'hide' => [
                        [
                            'module' => 'subject',
                            'controller' => 'materials',
                            'action' => 'index',
                        ]
                    ]
                ],
            ],
        ]);
    }
}