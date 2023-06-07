<?php
class HM_Tc_Subject_ActualCosts_ActualCostsModel extends HM_Recruit_AbstractCostsModel
{
    const PAYMENT_TYPE_DOCUMENT = 'document';
    const PAYMENT_TYPE_ACTUAL   = 'actual';
    
    
    public static function getPaymentTypes() {
        return array(
            self::PAYMENT_TYPE_DOCUMENT => _('По акту'),
            self::PAYMENT_TYPE_ACTUAL   => _('По факту оплаты'),
        );
    }
    
    public static function getPaymentType($type) {
        $types = self::getPaymentTypes();
        return $types[$type];
    }
    
}