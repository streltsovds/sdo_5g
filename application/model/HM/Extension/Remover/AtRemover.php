<?php
class HM_Extension_Remover_AtRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            [
                'roles' => [
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                ],
                'infoblocks' => [
                    'kpiBlock',
                ],
                'menu' => [
                    'module' => [
//                        'criterion',
                        'kpi',
                        'session',
                    ],
                    'id' => [
                        'mca:assign:atmanager:index',
                    ],
                    'contextMenu' => [
                        'id' => [
                            'mca:user:index:sessions',
                        ],
                    ]
                ],
                'domains' => [
                    'Assessment',
                ],
                'noticeClusters' => [
                    HM_Notice_NoticeModel::CLUSTER_ASSESSMENT,
                ],
            ]
        );
    }
}