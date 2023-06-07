<?php
class HM_Orgstructure_Position_PositionModel extends HM_Orgstructure_OrgstructureModel
{
    const TYPE = 1;
    
    const ROLE_EMPLOYEE = 0;
    const ROLE_MANAGER = 1;    

    public function getType()
    {
        return self::TYPE;
    }

    public function getCardFields()
    {
        return array(
            'getName()' => _('Название'),
            'getProfileName()' => _('Профиль'),
            'getOrgPath()' => _('Входит в'),
            'getUserName()'  => _('В должности')
        );
    }
}