<?php
class HM_Orgstructure_OrgstructureModel extends HM_Model_Abstract
{
    const TYPE_DEPARTMENT = 0;
    const TYPE_POSITION = 1;
    const TYPE_FIRED = -2;
    const TYPE_VACANCY = -3;

    const EMPLOYEE   = 0;
    const MANAGER    = 1;
    const SUPERVISOR = 1; // DEPRECATED

    const HEAD_SOID = 1; // очень нехорошая константа, не используйте её

    const DEFAULT_HEAD_STRUCTURE_ITEM_TITLE = 'Компания';

    protected $_primaryName = 'soid';
    
    public function getCardFields()
    {
        switch ($this->type) {

            case self::TYPE_VACANCY:
                return array(
                    'getTypeTitle()' => _('Тип'),
                    'getOrgPath()' => _('Входит в'),
                    'getNumberOfStaffUnits()'  => _('Количество штатных единиц')
                );
                break;
            default:
                return array(
                    'getTypeTitle()' => _('Тип'),
                    'getOrgPath()' => _('Входит в'),
                    'getName()'  => _('В должности')
                );
        }
    }

    public function getNumberOfStaffUnits()
    {
        if ($this->staffUnit) {
            $staffUnit = $this->staffUnit->current();
            return $staffUnit->quantity_text;
        }
        return '-';
    }
    
    public function getName()
    {
        return $this->name;
    }

    static public function factory($data, $default = 'HM_Orgstructure_OrgstructureModel')
    {
        switch($data['type']) {
            case self::TYPE_POSITION:
                return parent::factory($data, 'HM_Orgstructure_Position_PositionModel');
                break;
            default:
                return parent::factory($data, 'HM_Orgstructure_Unit_UnitModel');
                break;
        }
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_DEPARTMENT => _('Подразделение'),
            self::TYPE_POSITION => _('Должность'),
            self::TYPE_VACANCY => _('Вакансия')
        );
    }

    public function getUser()
    {
        if (isset($this->user) && count($this->user)) {
            return $this->user[0];
        }
        return false;
    }

    public function setUser(HM_User_UserModel $user)
    {
        return $this->user = array($user);
    }

    public function getTypeTitle()
    {
        $types = self::getTypes();
        return $types[$this->type];
    }

    public function getUserName()
    {
        $user = $this->getUser();
        if ($user) {
            return $user->getName();
        }
        return '-';
    }


    public function getProfileName()
    {
        $result = _('Не определено');
        $profileId = $this->profile_id;
        if ($profileId) {
            $service = Zend_Registry::get('serviceContainer')->getService('AtProfile');
            $profile = $service->getOne($service->find($profileId));
            if ($profile !== false ) $result = $profile->name;
        }
        return $result;
    }

    public function isPosition()
    {
        return in_array($this->type, array(
            self::TYPE_POSITION,
            self::TYPE_VACANCY,
        ));
    }
    

    public function getOrgPath($includeSelf = false)
    {
        $sign = $includeSelf ? '=' : '';
        $collection = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(array(
            "lft <{$sign} ?" => $this->lft,
            "rgt >{$sign} ?" => $this->rgt,
            "level <{$sign} ?" => $this->level,
        ), 'level');
        
        if (count($collection)) {
            $deps = $collection->getList('level', 'name');
            ksort($deps);
            return implode(' &rarr; ', $deps);
        }
        return '';
    }

    public function getParent()
    {
        return Zend_Registry::get('serviceContainer')->getService('Orgstructure')->findOne($this->owner_soid);
    }

    public function readyForImpersonalAssigns($pskName = 'gsp')
    {
        // #32400 cit: Средствами этого валидатора пропускать только подразделения 3-го и ниже уровня и только из ГСП
        $isReady = $this->getValue('level') >= 3;
        if ($soid = Zend_Registry::get('config')->$pskName->integration->sources->soid) {
            $root = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->findOne($soid);
            if ($root) $isReady = $isReady && ($this->lft > $root->lft) && ($this->rgt < $root->rgt);
        }

        return  $isReady;
    }

}