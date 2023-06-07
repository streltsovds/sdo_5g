<?php
class HM_Recruit_PlannedCosts_PlannedCostsModel extends HM_Recruit_AbstractCostsModel
{
    CONST STATUS_NEW      = 'new';
    CONST STATUS_ACCEPTED = 'accepted';
    
    public static function getStatuses() {
        return array(
            self::STATUS_NEW      => _('Новый'),
            self::STATUS_ACCEPTED => _('Принят'),
        );
    }
    
    public static function getStatus($status) {
        $stauses = self::getStatuses();
        return $stauses[$status];
    }
}