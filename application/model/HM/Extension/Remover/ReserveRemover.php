<?php
class HM_Extension_Remover_ReserveRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            // ВНИМАНИЕ!
            // эта роль используется одновременно для нескольких модулей:
            // подбор, адаптация, КР, ротация;
            'roles' => [
                HM_Role_Abstract_RoleModel::ROLE_HR,
                HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            ],
            'menu' => [
                'application' => 'hr',
                'module' => [
                    'reserve',
                    'reserve-request',
                ],
                'id' => [
                    'mca:assign:recruiter:index',
                ],
                'contextMenu' => [
                    'id' => [
                        'mca:profile:index:programm-reserve',
                    ],
                ]
            ],
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_RESERVE,
            ],
            'infoblocks' => [
                'reserveBlock',
                'reserveRespondentBlock',
                'positionsSliderBlock',
            ],
        ]);
    }
}