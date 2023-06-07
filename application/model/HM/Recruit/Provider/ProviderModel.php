<?php
class HM_Recruit_Provider_ProviderModel extends HM_Model_Abstract
{
    const STATUS_ACTUAL     = 1;
    const STATUS_NOT_ACTUAL = 0;

    const ID_PERSONAL = 1;
    const ID_HEADHUNTER = 2;
    const ID_SUPERJOB = 3;
    const ID_ESTAFF = 4;
    const ID_EXCEL = 5;
    const ID_ELSE = 6;

    const LOCKED_NOT_LOCKED = 0;
    const LOCKED_LOCKED = 1;

    const USERFORM_NO = 0;
    const USERFORM_YES = 1;

    const COST_NO = 0;
    const COST_YES = 1;

    public static function getStatuses() {
        return array(
            self::STATUS_ACTUAL     => _('Актуальный'),
            self::STATUS_NOT_ACTUAL => _('Не актуальный'),
        );
    }
    
    public static function getStatus($status) {
        $stauses = self::getStatuses();
        return $stauses[$status];
    }
}