<?php
class HM_Agreement_AgreementModel extends HM_Model_Abstract
{
    protected $_primaryName = 'agreement_id';

    const ITEM_TYPE_CLAIMANT = 1;
    
    const AGREEMENT_TYPE_CUSTOM = 0;
    const AGREEMENT_TYPE_DEAN = 1;
    const AGREEMENT_TYPE_SUPERVISOR = 2;
    const AGREEMENT_TYPE_SUPERSUPERVISOR = 3;
    
    static public function getAgreementTitles() 
    {
        return array(
            self::AGREEMENT_TYPE_DEAN => _('Менеджер по обучению'),
            self::AGREEMENT_TYPE_SUPERVISOR => _('Непосредственный руководитель'),
            self::AGREEMENT_TYPE_SUPERSUPERVISOR => _('Вышестоящий руководитель'),
        );
    }
    
    static public function getAgreementTitle($agreementType)
    {
        if (($agreementType == self::AGREEMENT_TYPE_CUSTOM) || self::isStatic($agreementType)) {
            return _('Произвольная должность');
        } else {
            $titles = self::getAgreementTitles();
            return $titles[$agreementType];
        }
    }
    
    static public function isStatic($agreementType)
    {
        return ($agreementType >= 10);
    }
}
