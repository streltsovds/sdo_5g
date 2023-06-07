<?php
class HM_Extension_Remover_AdaptingRemover extends HM_Extension_Remover_Abstract
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
                'application' => 'recruit',
                'module' => [
                    'newcomer',
                ],
                'id' => [
                    'mca:assign:recruiter:index',
                ],
            ],
            'columns' => [
                'evaluation_adapting',
            ],
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_ADAPTATION,
            ],
            'infoblocks' => [
                'adaptationBlock',
            ],
        ]);
    }
}