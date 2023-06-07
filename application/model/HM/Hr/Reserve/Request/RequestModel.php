<?php
class HM_Hr_Reserve_Request_RequestModel extends HM_Model_Abstract
{
    const STATUS_NEW = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;

    protected $_primaryName = 'reserve_request_id';

    static public function getStatusTitles()
    {
        return array(
            self::STATUS_NEW => _('Новая'),
            self::STATUS_ACCEPTED => _('Принята'),
            self::STATUS_DECLINED => _('Отклонена'),
        );
    }

    static public function getStatusTitle($status)
    {
        $statuses = self::getStatusTitles();
        return $statuses[$status] ? : '';
    }
}
