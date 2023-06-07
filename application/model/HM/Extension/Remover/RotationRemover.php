<?php
class HM_Extension_Remover_RotationRemover extends HM_Extension_Remover_Abstract
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
                    'rotation',
                ],
                'id' => [
                    'mca:assign:recruiter:index',
                ],
            ],
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_ROTATION,
            ],
            'infoblocks' => [
                'rotationBlock',
            ],
        ]);
    }
}