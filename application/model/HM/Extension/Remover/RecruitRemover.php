<?php
class HM_Extension_Remover_RecruitRemover extends HM_Extension_Remover_Abstract
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
                    'application',
                    'candidate',
                    'costs',
                    'forms',
                    'option',
                    'provider',
                    'recruiter',
                    'report',
                    'reservist',
                    'vacancy',
                ],
                'id' => [
                    'mca:assign:recruiter:index',
                ],
            ],
            'domains' => [
                'ExternalStaffReserv',
                'Recruiting',
            ],
            'elements' => [
                'custom_employer_name',
            ],
            'columns' => [
                'evaluation_recruit',
            ],
            'massActions' => array(
                [
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'assign-programm'
                ],
            ),
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_RECRUITING,
            ],
            'infoblocks' => [
                'myEventsBlock',
            ],
        ]);
    }
}