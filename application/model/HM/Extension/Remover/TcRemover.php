<?php
class HM_Extension_Remover_TcRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'domains' => [
                'Tc',
            ],
            'menu' => [
                'application' => 'tc'
            ],
            'elements' => [
                'managers_notify_session_quarter_days_before',
            ],
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_PLANING,
            ],
        ]);
    }
}