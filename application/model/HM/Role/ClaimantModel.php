<?php

class HM_Role_ClaimantModel extends HM_Role_Abstract_RoleModel
{
    const TYPE_LMS = 0;
    const TYPE_SAP = 1;

    const STATUS_NEW = 0;
    const STATUS_REJECTED = 1;
    const STATUS_ACCEPTED = 2;

    protected $_primaryName = 'SID';

    public function getServiceName()
    {
        return 'Claimant';
    }

    static function getTypes()
    {
        return array(
            self::TYPE_LMS => _('СДО'),
            self::TYPE_SAP => _('SAP')
        );
    }

    static function getType($value)
    {
        $types = self::getTypes();
        if ( !array_key_exists($value, $types)) {
            return '<Тип не определен>';
        }
        return $types[$value];
    }

    static function getStatuses()
    {
        return array(
            self::STATUS_NEW => _('Активная'),
            self::STATUS_REJECTED => _('Отклонена'),
            self::STATUS_ACCEPTED => _('Принята')
        );
    }

    static function getStatus($status)
    {
        $statuses = self::getStatuses();
        if ( !array_key_exists($status, $statuses)) {
            return '<Статус не определен>';
        }
        return $statuses[$status];
    }
}